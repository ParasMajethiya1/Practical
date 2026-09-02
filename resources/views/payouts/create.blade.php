@extends("layouts.app")
@section("title", "New Payout")

@section("content")
<a href="{{ route("payouts.index") }}" class="text-sm text-slate-500 hover:text-slate-800 transition">&larr; Back to Payouts</a>

<h3 class="mt-3 mb-1 flex items-center gap-2 text-xl font-bold text-slate-800">
    <i class="bi bi-plus-circle text-brand-600"></i> New Payout
</h3>
<p class="mb-6 text-sm text-slate-500 max-w-xl">
    The moment you submit this, the payout amount is placed <span class="font-semibold text-amber-600">on hold</span> in the merchant's wallet.
    It's only released back (on failure) or debited for real (on success) once the payout is processed.
</p>

<div class="grid gap-6 lg:grid-cols-3">
    <form method="POST" action="{{ route("payouts.store") }}" class="lg:col-span-2 rounded-2xl border border-slate-100 bg-white p-6 shadow-sm space-y-5">
        @csrf

        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Merchant</label>
            <select name="merchant_id" class="w-full rounded-lg border-slate-200 bg-slate-50 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 @error("merchant_id") border-rose-400 @enderror">
                <option value="">Select a merchant&hellip;</option>
                @foreach ($merchants as $merchant)
                    <option value="{{ $merchant->id }}" @selected((string) old("merchant_id") === (string) $merchant->id)>
                        {{ $merchant->name }} &mdash; available {{ number_format($merchant->wallet->available_balance ?? 0, 2) }} {{ $merchant->wallet->currency ?? "" }}
                    </option>
                @endforeach
            </select>
            @error("merchant_id") <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Amount</label>
                <input type="number" step="0.01" min="0.01" name="amount" value="{{ old("amount") }}" class="w-full rounded-lg border-slate-200 bg-slate-50 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 @error("amount") border-rose-400 @enderror">
                @error("amount") <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Currency</label>
                <input type="text" maxlength="3" name="currency" value="{{ old("currency", "INR") }}" class="w-full rounded-lg border-slate-200 bg-slate-50 py-2 text-sm uppercase shadow-sm focus:border-brand-500 focus:ring-brand-500">
            </div>
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Payout Method</label>
            <input type="text" name="payout_method" value="{{ old("payout_method") }}" placeholder="e.g. IMPS, NEFT, UPI" class="w-full rounded-lg border-slate-200 bg-slate-50 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
        </div>

        <div class="border-t border-dashed border-slate-200 pt-5">
            <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Beneficiary details</p>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Name</label>
                    <input type="text" name="beneficiary_name" value="{{ old("beneficiary_name") }}" class="w-full rounded-lg border-slate-200 bg-slate-50 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 @error("beneficiary_name") border-rose-400 @enderror">
                    @error("beneficiary_name") <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Account Number</label>
                    <input type="text" name="beneficiary_account_number" value="{{ old("beneficiary_account_number") }}" class="w-full rounded-lg border-slate-200 bg-slate-50 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 @error("beneficiary_account_number") border-rose-400 @enderror">
                    @error("beneficiary_account_number") <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">IFSC</label>
                    <input type="text" name="beneficiary_ifsc" value="{{ old("beneficiary_ifsc") }}" class="w-full rounded-lg border-slate-200 bg-slate-50 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Bank Name</label>
                    <input type="text" name="beneficiary_bank_name" value="{{ old("beneficiary_bank_name") }}" class="w-full rounded-lg border-slate-200 bg-slate-50 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
            </div>
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Remarks</label>
            <textarea name="remarks" rows="2" class="w-full rounded-lg border-slate-200 bg-slate-50 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">{{ old("remarks") }}</textarea>
        </div>

        <div class="flex gap-3 pt-2">
            <button class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-brand-600/30 hover:bg-brand-700 transition">
                <i class="bi bi-lock"></i> Initiate & Hold Funds
            </button>
            <a href="{{ route("payouts.index") }}" class="inline-flex items-center rounded-lg border border-slate-200 px-5 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">Cancel</a>
        </div>
    </form>

    <div class="space-y-4">
        <div class="rounded-2xl border border-amber-100 bg-amber-50 p-5">
            <div class="flex items-center gap-2 text-amber-700 font-semibold text-sm">
                <i class="bi bi-lock-fill"></i> How the hold works
            </div>
            <ol class="mt-3 space-y-2 text-[13px] text-amber-800/90 list-decimal list-inside">
                <li>Amount is reserved instantly - it's subtracted from the merchant's <em>available</em> balance.</li>
                <li>If the payout later <strong>succeeds</strong>, the hold converts into a real debit.</li>
                <li>If it <strong>fails</strong>, the hold is released straight back to available balance.</li>
            </ol>
        </div>
        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Tip</p>
            <p class="text-[13px] text-slate-500">Use <span class="font-medium text-slate-700">Process Pending</span> on the Payouts list to instantly resolve this payout and see the hold released or debited.</p>
        </div>
    </div>
</div>
@endsection
