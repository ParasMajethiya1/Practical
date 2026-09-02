@extends("layouts.app")
@section("title", $wallet->merchant->name . " Wallet")

@section("content")
<a href="{{ route("wallets.index") }}" class="text-sm text-slate-500 hover:text-slate-800 transition">&larr; Back to Wallets</a>

<div class="flex flex-wrap items-center justify-between gap-3 my-4">
    <h4 class="text-lg font-bold text-slate-800">{{ $wallet->merchant->name }}</h4>
</div>

<div class="grid gap-4 sm:grid-cols-3 mb-6">
    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Ledger Balance</p>
        <p class="mt-1 text-2xl font-extrabold text-slate-800">{{ number_format($wallet->balance, 2) }} <span class="text-sm font-medium text-slate-400">{{ $wallet->currency }}</span></p>
    </div>
    <div class="rounded-2xl border border-amber-100 bg-amber-50 p-5">
        <p class="text-xs font-semibold uppercase tracking-wider text-amber-600 flex items-center gap-1"><i class="bi bi-lock-fill"></i> On Hold</p>
        <p class="mt-1 text-2xl font-extrabold text-amber-700">{{ number_format($wallet->held_amount, 2) }} <span class="text-sm font-medium text-amber-500">{{ $wallet->currency }}</span></p>
        <p class="mt-1 text-[12px] text-amber-600/80">Reserved against pending payouts</p>
    </div>
    <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5">
        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Available</p>
        <p class="mt-1 text-2xl font-extrabold text-emerald-700">{{ number_format($wallet->available_balance, 2) }} <span class="text-sm font-medium text-emerald-500">{{ $wallet->currency }}</span></p>
        <p class="mt-1 text-[12px] text-emerald-600/80">Ready to spend on new payouts</p>
    </div>
</div>

<div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm mb-6">
    <form method="GET" class="flex flex-wrap items-end gap-3">
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500">Type</label>
            <select name="type" class="rounded-lg border-slate-200 bg-slate-50 py-1.5 pl-3 pr-8 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                <option value="">All</option>
                <option value="CREDIT" @selected(request("type")==="CREDIT")>Credit</option>
                <option value="DEBIT" @selected(request("type")==="DEBIT")>Debit</option>
                <option value="HOLD" @selected(request("type")==="HOLD")>Hold</option>
                <option value="RELEASE" @selected(request("type")==="RELEASE")>Release</option>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500">From</label>
            <input type="date" name="date_from" value="{{ request("date_from") }}" class="rounded-lg border-slate-200 bg-slate-50 py-1.5 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500">To</label>
            <input type="date" name="date_to" value="{{ request("date_to") }}" class="rounded-lg border-slate-200 bg-slate-50 py-1.5 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
        </div>
        <div class="flex gap-2">
            <button class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-3.5 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-800 transition"><i class="bi bi-funnel"></i> Filter</button>
            <a href="{{ route("wallets.show", $wallet) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3.5 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">Reset</a>
        </div>
    </form>
</div>

<div class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                    <th class="px-5 py-3">Type</th>
                    <th class="px-5 py-3">Amount</th>
                    <th class="px-5 py-3">Before</th>
                    <th class="px-5 py-3">After</th>
                    <th class="px-5 py-3">Reference</th>
                    <th class="px-5 py-3">Description</th>
                    <th class="px-5 py-3">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($transactions as $tx)
                    @php
                        $badge = [
                            "CREDIT" => "bg-emerald-50 text-emerald-700 ring-emerald-200",
                            "DEBIT" => "bg-rose-50 text-rose-700 ring-rose-200",
                            "HOLD" => "bg-amber-50 text-amber-700 ring-amber-200",
                            "RELEASE" => "bg-sky-50 text-sky-700 ring-sky-200",
                        ][$tx->type] ?? "bg-slate-100 text-slate-600 ring-slate-200";
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-5 py-3">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $badge }}">{{ $tx->type }}</span>
                        </td>
                        <td class="px-5 py-3 font-semibold text-slate-800">{{ number_format($tx->amount, 2) }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ number_format($tx->balance_before, 2) }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ number_format($tx->balance_after, 2) }}</td>
                        <td class="px-5 py-3 text-xs uppercase tracking-wide text-slate-400">{{ $tx->reference_type }} #{{ $tx->reference_id }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $tx->description }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ $tx->created_at->format("d M Y H:i") }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-12 text-center text-slate-400">No wallet transactions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-5 py-3">
        {{ $transactions->links() }}
    </div>
</div>
@endsection
