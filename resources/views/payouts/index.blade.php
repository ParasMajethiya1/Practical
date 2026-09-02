@extends("layouts.app")
@section("title", "Payouts")

@section("content")
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <h3 class="flex items-center gap-2 text-xl font-bold text-slate-800">
        <i class="bi bi-arrow-up-circle text-brand-600"></i> Payouts
    </h3>
    <div class="flex gap-2">
        <form method="POST" action="{{ route("payouts.process-pending") }}">
            @csrf
            <button class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-600 shadow-sm hover:bg-slate-50 transition">
                <i class="bi bi-arrow-repeat"></i> Process Pending
            </button>
        </form>
        <a href="{{ route("payouts.create") }}" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm shadow-brand-600/30 hover:bg-brand-700 transition">
            <i class="bi bi-plus-lg"></i> New Payout
        </a>
    </div>
</div>

<div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm mb-6">
    @include("partials.filters")
</div>

<div class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                    <th class="px-5 py-3">Transaction ID</th>
                    <th class="px-5 py-3">Merchant</th>
                    <th class="px-5 py-3">Amount</th>
                    <th class="px-5 py-3">Method</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">Created</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($payouts as $payout)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-5 py-3"><code class="tx-id rounded-md bg-slate-100 px-2 py-1 text-xs text-slate-600">{{ $payout->transaction_id }}</code></td>
                        <td class="px-5 py-3 font-medium text-slate-700">{{ $payout->merchant->name }}</td>
                        <td class="px-5 py-3 font-semibold text-slate-800">{{ number_format($payout->amount, 2) }} {{ $payout->currency }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ $payout->payout_method ?? "-" }}</td>
                        <td class="px-5 py-3">@include("partials.status-badge", ["status" => $payout->status])</td>
                        <td class="px-5 py-3 text-slate-500">{{ $payout->created_at->format("d M Y H:i") }}</td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route("payouts.show", $payout) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 h-8 w-8 text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition"><i class="bi bi-eye"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-12 text-center text-slate-400">No payouts found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-5 py-3">
        {{ $payouts->links() }}
    </div>
</div>
@endsection
