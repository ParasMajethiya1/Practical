@extends("layouts.app")
@section("title", $merchant->name)

@section("content")
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <h3 class="flex items-center gap-2 text-xl font-bold text-slate-800"><i class="bi bi-shop text-brand-600"></i> {{ $merchant->name }}</h3>
    <div class="flex gap-2">
        <a href="{{ route("merchants.edit", $merchant) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3.5 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition"><i class="bi bi-pencil"></i> Edit</a>
        <form action="{{ route("merchants.destroy", $merchant) }}" method="POST" onsubmit="return confirm('Delete this merchant?')">
            @csrf @method("DELETE")
            <button class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 px-3.5 py-2 text-sm font-medium text-rose-600 hover:bg-rose-50 transition"><i class="bi bi-trash"></i> Delete</button>
        </form>
    </div>
</div>

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
    <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Wallet Balance</p>
        <p class="mt-1 text-xl font-extrabold text-slate-800">{{ number_format($merchant->wallet->balance ?? 0, 2) }} {{ $merchant->wallet->currency ?? "" }}</p>
    </div>
    <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
        <p class="text-xs font-semibold uppercase tracking-wider text-amber-600 flex items-center gap-1"><i class="bi bi-lock-fill"></i> On Hold</p>
        <p class="mt-1 text-xl font-extrabold text-amber-700">{{ number_format($merchant->wallet->held_amount ?? 0, 2) }}</p>
    </div>
    <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Available</p>
        <p class="mt-1 text-xl font-extrabold text-emerald-700">{{ number_format($merchant->wallet->available_balance ?? 0, 2) }}</p>
    </div>
    <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Status</p>
        <span class="mt-1 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $merchant->status === "active" ? "bg-emerald-50 text-emerald-700 ring-emerald-200" : "bg-slate-100 text-slate-500 ring-slate-200" }}">{{ ucfirst($merchant->status) }}</span>
    </div>
</div>

<div class="grid gap-4 md:grid-cols-2">
    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
        <h6 class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Recent Pay-ins</h6>
        <table class="w-full text-sm">
            <thead><tr class="text-left text-slate-400"><th class="pb-2">Txn ID</th><th class="pb-2">Amount</th><th class="pb-2">Status</th></tr></thead>
            <tbody class="divide-y divide-slate-50">
            @forelse ($recentPayins as $p)
                <tr>
                    <td class="py-2"><a href="{{ route("payins.show", $p) }}" class="text-brand-600 hover:underline"><code class="tx-id">{{ $p->transaction_id }}</code></a></td>
                    <td class="py-2 font-medium text-slate-700">{{ number_format($p->amount, 2) }}</td>
                    <td class="py-2">@include("partials.status-badge", ["status" => $p->status])</td>
                </tr>
            @empty
                <tr><td colspan="3" class="py-6 text-center text-slate-400">No pay-ins yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
        <h6 class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Recent Payouts</h6>
        <table class="w-full text-sm">
            <thead><tr class="text-left text-slate-400"><th class="pb-2">Txn ID</th><th class="pb-2">Amount</th><th class="pb-2">Status</th></tr></thead>
            <tbody class="divide-y divide-slate-50">
            @forelse ($recentPayouts as $p)
                <tr>
                    <td class="py-2"><a href="{{ route("payouts.show", $p) }}" class="text-brand-600 hover:underline"><code class="tx-id">{{ $p->transaction_id }}</code></a></td>
                    <td class="py-2 font-medium text-slate-700">{{ number_format($p->amount, 2) }}</td>
                    <td class="py-2">@include("partials.status-badge", ["status" => $p->status])</td>
                </tr>
            @empty
                <tr><td colspan="3" class="py-6 text-center text-slate-400">No payouts yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
