# Pay-in & Payout Module (Laravel)

A backend-focused Laravel module implementing merchant pay-in/payout initiation,
a scheduled job that randomly resolves pending payments (SUCCESS / FAILED /
PENDING), wallet ledger accounting, and a Bootstrap-based admin panel with
filters.

> This repo ships **only the application-specific code** (`app/`, selected
> `database/`, `resources/views/`, `routes/`) that plugs into a fresh Laravel
> installation. This keeps the deliverable focused on the actual assignment
> (business logic, DB design, services, cron) instead of thousands of
> unmodified framework boilerplate files. See **Setup** below for exactly how
> to merge it in — it takes about 2 minutes.

---

## 1. Architecture at a Glance

```
app/
├── Console/
│   ├── Commands/ProcessPendingPayments.php   # artisan payments:process-pending
│   └── Kernel.php                            # schedules the command every minute
├── Exceptions/InsufficientBalanceException.php
├── Helpers/TransactionIdHelper.php           # guaranteed-unique transaction IDs
├── Http/
│   ├── Controllers/
│   │   ├── Api/{Merchant,Payin,Payout,Wallet}Controller.php   # JSON API
│   │   ├── Auth/AdminAuthController.php      # /login, /logout for the panel
│   │   └── {Merchant,Payin,Payout,Wallet}Controller.php       # Blade admin panel
│   ├── Middleware/AuthenticateMerchant.php   # X-API-KEY auth for the API
│   └── Requests/                             # Form Request validation
├── Models/                                   # Merchant, Wallet, WalletTransaction,
│                                              # Payin, Payout, PaymentLog, Admin
└── Services/
    ├── PayinService.php                      # initiate + list pay-ins
    ├── PayoutService.php                     # initiate + list payouts (+ balance check)
    ├── WalletService.php                     # ALL balance mutations go through here
    ├── PaymentProcessingService.php          # the cron's core logic
    └── PaymentLogger.php                     # writes to payment_logs table + laravel.log

database/migrations/                          # 7 migrations, see section 2
database/seeders/AdminSeeder.php              # 1 back-office login (see section 8)
database/seeders/MerchantSeeder.php           # 2 demo merchants + wallets

resources/views/                              # Bootstrap 5 admin panel (merchants,
                                               # payins, payouts, wallets) with filters,
                                               # + resources/views/auth/login.blade.php

routes/{web,api,console}.php
```

**Controllers stay thin.** All business logic (transaction ID generation,
status transitions, wallet debits/credits, validation of business rules like
"sufficient balance") lives in `app/Services`, `app/Helpers` and Form
Requests — never directly in controllers.

---

## 2. Database Design

| Table                | Purpose                                                                 |
|-----------------------|--------------------------------------------------------------------------|
| `merchants`           | Merchant identity + `api_key` (unique) used for API auth + status      |
| `wallets`              | One-to-one with merchant. Holds the current `balance` (source of truth)|
| `payins`               | Pay-in requests: `transaction_id` (unique), amount, `status`, customer details (JSON) |
| `payouts`              | Payout requests: `transaction_id` (unique), amount, `status`, beneficiary details (JSON) |
| `wallet_transactions`  | Immutable ledger of every balance change (CREDIT/DEBIT) with `balance_before`/`balance_after` |
| `payment_logs`         | Structured event log: INITIATED, CRON_CHECK, PROCESSED, ERROR, etc.    |
| `admins`               | Back-office staff logins (email + hashed password) for the `/login` panel — separate from `merchants` |

**Relationships**

* `Merchant hasOne Wallet`, `hasMany Payin`, `hasMany Payout`, `hasMany WalletTransaction`
* `Payin/Payout belongsTo Merchant`
* `WalletTransaction belongsTo Wallet, Merchant`

**Key constraints**

* `merchants.email`, `merchants.api_key` → unique
* `payins.transaction_id`, `payouts.transaction_id` → unique
* `wallets.merchant_id` → unique (enforces one wallet per merchant)
* **`wallet_transactions(reference_type, reference_id)` → unique** — this is
  the constraint that makes double-processing structurally impossible (see
  section 4).
* Indexes on `(merchant_id, status)` and `(status, created_at)` on both
  `payins` and `payouts` to keep cron polling and admin-panel filtering fast.

---

## 3. Payment Flow (Pay-in / Payout)

1. Merchant calls `POST /api/v1/payins` (or `/payouts`) with `X-API-KEY` header.
2. `AuthenticateMerchant` middleware resolves + validates the merchant.
3. A Form Request (`StorePayinRequest` / `StorePayoutRequest`) validates
   amount, currency, customer/beneficiary fields.
4. `PayinService::initiate()` / `PayoutService::initiate()`:
   * generates a unique transaction ID via `TransactionIdHelper` (format:
     `PIN20260902AB12CD34EF` / `POT20260902AB12CD34EF`),
   * persists the record with `status = PENDING`,
   * writes an `INITIATED` entry via `PaymentLogger` (DB + log file).
5. Response returns the `transaction_id` and `status: PENDING` immediately —
   processing is asynchronous, via the cron.

**Payouts only**: `PayoutService::initiate()` performs an up-front balance
check (`WalletService::hasSufficientBalance`) and rejects the request with
`422` if the wallet can't cover the amount — this saves creating payouts
that could never succeed.

---

## 4. The Cron / Scheduler (core of the assignment)

**Command:** `php artisan payments:process-pending`
**Scheduled:** every minute, via `app/Console/Kernel.php`
(`->everyMinute()->withoutOverlapping()->runInBackground()`).

For every row currently `PENDING` in `payins` and `payouts`:

1. It is re-fetched **inside its own DB transaction with `lockForUpdate()`**
   and its status is re-checked. If it's no longer `PENDING` (already picked
   up by a concurrent run), it is skipped — this is the first
   duplicate-processing guard.
2. A random status is drawn from `[SUCCESS, FAILED, PENDING]`.
3. **PENDING** → nothing changes; the row is picked up again on the next run
   automatically (we simply always query for `status = PENDING`, so there's
   no separate "retry queue" to maintain).
4. **SUCCESS** → status updated, `processed_at` stamped, and
   `WalletService::creditForPayin()` / `debitForPayout()` is called.
5. **FAILED** → status + `failure_reason` updated, wallet untouched.

**Why the wallet can never be double-credited/debited (second guard):**
`WalletService` writes a `WalletTransaction` row with
`reference_type = 'payin'|'payout'` and `reference_id = <payin/payout id>`.
The `wallet_transactions` table has a **unique DB constraint** on
`(reference_type, reference_id)`. Even if the service method were somehow
invoked twice for the same payment (a bug, a re-run, a race condition that
slips past the row lock), the second `INSERT` fails / is caught and short-
circuited before the balance is touched a second time. This makes "same
payment cannot be processed/deducted twice" a database-level guarantee, not
just an application-level convention.

For payouts specifically: if the wallet balance dropped below the payout
amount between initiation and processing (e.g. another payout drained it),
`WalletService::debitForPayout()` throws `InsufficientBalanceException` and
the payout is force-marked `FAILED` with that reason, rather than allowing a
negative balance.

Run it manually any time with:
```bash
php artisan payments:process-pending
# optional: process in smaller/larger batches
php artisan payments:process-pending --batch=50
```

---

## 5. Setup Instructions

### Prerequisites
PHP 8.2+, Composer, MySQL (or SQLite for a quick trial).

### Step 1 — Create a fresh Laravel app
```bash
composer create-project laravel/laravel payin-payout-app "^10.0"
cd payin-payout-app
```

### Step 2 — Merge this module in
Copy/overwrite the following folders from this repo into the new project
(they will not conflict with anything in a fresh install, aside from the two
files noted below which **replace** the stock ones):

```bash
cp -r app/*        payin-payout-app/app/
cp -r database/migrations/*  payin-payout-app/database/migrations/
cp -r database/seeders/*     payin-payout-app/database/seeders/
cp -r resources/views/*      payin-payout-app/resources/views/
cp routes/web.php     payin-payout-app/routes/web.php
cp routes/api.php     payin-payout-app/routes/api.php
```

> `app/Http/Kernel.php` and `app/Console/Kernel.php` in this repo **replace**
> the stock files — they only add the `auth.merchant` middleware alias and
> the scheduler entry on top of Laravel's defaults, nothing else was removed.
> `config/auth.php` also **replaces** the stock file — it keeps every default
> Laravel guard/provider and only adds the `admin` guard + `admins` provider
> used to protect the back-office panel (see **Admin Authentication** below).
> If you're on Laravel 11 (which restructured `bootstrap/app.php` and removed
> the two Kernel files), instead:
> * register the command's schedule in `bootstrap/app.php` under `->withSchedule()`
> * register `'auth.merchant' => \App\Http\Middleware\AuthenticateMerchant::class` under `->withMiddleware()`
> * add the `admin` guard/`admins` provider from `config/auth.php` into your own copy of that file
> * everything else (Models, Services, Controllers, Requests, migrations, views, routes) works unchanged.

### Step 3 — Environment & database
```bash
cp .env.example .env
php artisan key:generate
```
Edit `.env` with your DB credentials (see `.env.example.snippet` in this
repo for the relevant keys), then:
```bash
php artisan migrate
php artisan db:seed
```
This runs both seeders (`app/database/seeders/DatabaseSeeder.php` calls
them in order):
* `AdminSeeder` creates one back-office login and prints its email +
  a random generated password to the console — use these at `/login`.
* `MerchantSeeder` prints two demo merchants with their `api_key` and
  opening wallet balance — copy one for the API calls below.

(Run `php artisan db:seed --class=AdminSeeder` or `--class=MerchantSeeder`
if you only want one of them.)

### Step 4 — Run it
```bash
php artisan serve
```
Visit `http://localhost:8000` — you'll be redirected to `/login`. Sign in
with the credentials `AdminSeeder` printed to the console, then you'll land
on the admin panel (merchants, pay-ins, payouts, wallets, all with filters).

### Step 5 — Run the cron
For local testing, run it on demand:
```bash
php artisan payments:process-pending
```
For continuous processing (mirrors production), run Laravel's scheduler
loop in a separate terminal:
```bash
php artisan schedule:work
```
In production, add a single system cron entry instead:
```
* * * * * cd /path/to/payin-payout-app && php artisan schedule:run >> /dev/null 2>&1
```

---

## 6. API Documentation & Sample Requests

Base URL: `http://localhost:8000/api/v1`
Auth: header `X-API-KEY: <merchant api_key>` on every route except merchant
registration.

### 6.1 Register a merchant (public)
```bash
curl -X POST http://localhost:8000/api/v1/merchants \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Acme Retail Pvt Ltd",
    "email": "acme@example.com",
    "phone": "9999999999"
  }'
```
```json
{
  "success": true,
  "message": "Merchant registered successfully. Store the api_key securely - it will not be shown again.",
  "data": { "id": 3, "name": "Acme Retail Pvt Ltd", "email": "acme@example.com", "api_key": "6QowJ...redacted...aB" }
}
```

### 6.2 Initiate a Pay-in
```bash
curl -X POST http://localhost:8000/api/v1/payins \
  -H "X-API-KEY: <merchant_api_key>" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 1500.50,
    "currency": "INR",
    "payment_method": "UPI",
    "customer_name": "Rahul Sharma",
    "customer_email": "rahul@example.com",
    "customer_phone": "9876543210",
    "remarks": "Order #INV-1042"
  }'
```
```json
{
  "success": true,
  "message": "Pay-in initiated successfully.",
  "data": {
    "transaction_id": "PIN20260902AB12CD34EF",
    "status": "PENDING",
    "amount": "1500.50",
    "currency": "INR",
    "created_at": "2026-09-02T10:15:00.000000Z"
  }
}
```

### 6.3 Initiate a Payout
```bash
curl -X POST http://localhost:8000/api/v1/payouts \
  -H "X-API-KEY: <merchant_api_key>" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 2000,
    "currency": "INR",
    "payout_method": "IMPS",
    "beneficiary_name": "Priya Verma",
    "beneficiary_account_number": "123456789012",
    "beneficiary_ifsc": "HDFC0001234",
    "beneficiary_bank_name": "HDFC Bank",
    "remarks": "Vendor settlement"
  }'
```
Returns `422` with `{"success": false, "message": "..."}` if the merchant's
wallet balance is insufficient.

### 6.4 List / filter Pay-ins or Payouts
```bash
curl "http://localhost:8000/api/v1/payins?status=SUCCESS&date_from=2026-09-01&date_to=2026-09-02" \
  -H "X-API-KEY: <merchant_api_key>"
```

### 6.5 Get a single Pay-in / Payout by transaction ID
```bash
curl http://localhost:8000/api/v1/payins/PIN20260902AB12CD34EF \
  -H "X-API-KEY: <merchant_api_key>"
```

### 6.6 Get wallet balance + recent transactions
```bash
curl http://localhost:8000/api/v1/wallet -H "X-API-KEY: <merchant_api_key>"
```

---

## 7. Logging

Every important event is written to **both**:
* the `payment_logs` table (filterable, queryable, joinable to the source
  payin/payout via the `loggable_type`/`loggable_id` polymorphic columns), and
* `storage/logs/laravel.log` (via `Log::info` / `Log::error`), prefixed
  `[PAYMENT]` / `[PAYMENT ERROR]` for easy `grep`/`tail -f`.

Events logged: `INITIATED` (with the full request payload), `CRON_CHECK`
(when a row remains PENDING), `PROCESSED` (status change to SUCCESS/FAILED),
and `ERROR` (e.g. `InsufficientBalanceException` during processing).

---

## 8. Admin Authentication

The back-office panel (`routes/web.php`) is **not** public — every route in
it is wrapped in `Route::middleware('auth:admin')`, so an unauthenticated
visitor is redirected to `/login` first.

* **Separate from merchant auth.** Merchants authenticate to the API with
  an `X-API-KEY` header (`AuthenticateMerchant` middleware, `merchants`
  table). Staff authenticate to the Blade panel with an email/password
  session login against a completely different table/model/guard
  (`admins` table, `App\Models\Admin`, the `admin` guard in
  `config/auth.php`). One credential can never be used for the other
  surface.
* **How it works:**
  * `database/migrations/2024_01_03_000001_create_admins_table.php` — `id`,
    `name`, `email` (unique), `password`, `remember_token`.
  * `App\Models\Admin` — implements `Authenticatable`, hashes `password`
    via the model cast, hides `password`/`remember_token` from arrays/JSON.
  * `config/auth.php` — adds the `admin` guard (session driver) + `admins`
    provider alongside Laravel's stock `web` guard, so it doesn't disturb
    anything a fresh install already relies on.
  * `App\Http\Controllers\Auth\AdminAuthController` — `showLoginForm()`,
    `login()` (validates, `Auth::guard('admin')->attempt()`, regenerates
    the session), `logout()` (guard logout + session invalidate/regenerate
    CSRF token).
  * `resources/views/auth/login.blade.php` — plain email/password form,
    styled to match the rest of the panel.
  * `routes/web.php` — `GET/POST /login` (behind `guest:admin` so a
    logged-in admin can't see the login form again), `POST /logout`
    (behind `auth:admin`), and every existing admin route now sits inside
    an `auth:admin` group.
  * The sidebar / mobile nav (`resources/views/layouts/app.blade.php`)
    shows the signed-in admin's name and a logout button.
* **Seeding a login:** `AdminSeeder` (called from `DatabaseSeeder`) creates
  one admin — `admin@example.com` with a random 14-character password
  printed to the console on first run. In a real deployment, change that
  password immediately (or delete the seeded row and create real admins via
  `php artisan tinker`: `App\Models\Admin::create(['name' => '...', 'email' => '...', 'password' => Hash::make('...')])`).
* **CSRF & sessions.** Both come from Laravel's stock `web` middleware
  group (already in `Kernel.php`), so no extra setup is needed beyond a
  working `APP_KEY` and the default `session`/`cache` config from
  `laravel new`.

## 9. Admin Panel

Bootstrap 5 UI, no build step required (CDN assets), covering:

* **Merchants** — list/search/filter by status, create, edit, view detail
  (wallet balance + recent pay-ins/payouts), delete (blocked if wallet has
  a non-zero balance).
* **Pay-ins / Payouts** — list with filters (status, merchant, date range),
  detail view with full customer/beneficiary JSON rendered.
* **Wallets** — list of all merchant balances; detail view is a full ledger
  (`wallet_transactions`) filterable by type and date, showing
  balance-before/after for a clean audit trail.

---

## 10. Design Decisions / Notes

* **Why a separate `wallet_transactions` ledger instead of just mutating
  `wallets.balance`?** It gives a full audit trail, makes the
  double-processing guard possible via the unique constraint, and lets the
  UI show a proper statement per merchant.
* **Why check balance both at initiation and at processing time for
  payouts?** Initiation-time check gives fast feedback to the merchant;
  processing-time check (inside the locked transaction) is the one that
  actually matters for correctness, since balance can change between the
  two moments.
* **Why Form Requests instead of validating in the controller?** Keeps
  controllers thin and validation rules reusable/testable in isolation.
* **Currency** is stored per-record (`payins.currency`, `payouts.currency`,
  `wallets.currency`) rather than assumed globally, though this project
  does not implement cross-currency conversion — mixed-currency wallets
  would need that as a follow-up.
