@extends("layouts.app")
@section("title", $payout->transaction_id)

@section("content")
<a href="{{ route("payouts.index") }}" class="text-sm text-slate-500 hover:text-slate-800 transition">&larr; Back to Payouts</a>

<div class="flex flex-wrap items-center justify-between gap-3 my-4">
    <h4 class="flex items-center gap-3 text-lg font-bold text-slate-800">
        <code class="tx-id rounded-md bg-slate-100 px-2.5 py-1 text-sm text-slate-600">{{ $payout->transaction_id }}</code>
        @include("partials.status-badge", ["status" => $payout->status])
    </h4>
    <div class="text-2xl font-extrabold text-slate-800">{{ number_format($payout->amount, 2) }} <span class="text-sm font-medium text-slate-400">{{ $payout->currency }}</span></div>
</div>

@if ($payout->isPending())
    <div class="mb-6 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4">
        <i class="bi bi-lock-fill text-amber-600 mt-0.5"></i>
        <p class="text-sm text-amber-800">
            <span class="font-semibold">{{ number_format($payout->amount, 2) }} {{ $payout->currency }} is currently on hold</span>
            in {{ $payout->merchant->name }}'s wallet. It will be released back if this payout fails, or debited for real once it succeeds.
        </p>
    </div>
@endif

<div class="grid gap-4 md:grid-cols-2">
    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
        <h6 class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Payment Details</h6>
        <dl class="divide-y divide-slate-50 text-sm">
            <div class="flex justify-between py-2"><dt class="text-slate-500">Merchant</dt><dd class="font-medium text-slate-700">{{ $payout->merchant->name }}</dd></div>
            <div class="flex justify-between py-2"><dt class="text-slate-500">Amount</dt><dd class="font-medium text-slate-700">{{ number_format($payout->amount, 2) }} {{ $payout->currency }}</dd></div>
            <div class="flex justify-between py-2"><dt class="text-slate-500">Payout Method</dt><dd class="font-medium text-slate-700">{{ $payout->payout_method ?? "-" }}</dd></div>
            <div class="flex justify-between py-2"><dt class="text-slate-500">Remarks</dt><dd class="font-medium text-slate-700">{{ $payout->remarks ?? "-" }}</dd></div>
            <div class="flex justify-between py-2"><dt class="text-slate-500">Failure Reason</dt><dd class="font-medium text-rose-600">{{ $payout->failure_reason ?? "-" }}</dd></div>
            <div class="flex justify-between py-2"><dt class="text-slate-500">Created At</dt><dd class="font-medium text-slate-700">{{ $payout->created_at }}</dd></div>
            <div class="flex justify-between py-2"><dt class="text-slate-500">Processed At</dt><dd class="font-medium text-slate-700">{{ $payout->processed_at ?? "Not processed yet" }}</dd></div>
        </dl>
    </div>
    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
        <h6 class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Beneficiary Details</h6>
        <dl class="divide-y divide-slate-50 text-sm">
            <div class="flex justify-between py-2"><dt class="text-slate-500">Name</dt><dd class="font-medium text-slate-700">{{ $payout->beneficiary_details["name"] ?? "-" }}</dd></div>
            <div class="flex justify-between py-2"><dt class="text-slate-500">Account Number</dt><dd class="font-medium text-slate-700">{{ $payout->beneficiary_details["account_number"] ?? "-" }}</dd></div>
            <div class="flex justify-between py-2"><dt class="text-slate-500">IFSC</dt><dd class="font-medium text-slate-700">{{ $payout->beneficiary_details["ifsc"] ?? "-" }}</dd></div>
            <div class="flex justify-between py-2"><dt class="text-slate-500">Bank Name</dt><dd class="font-medium text-slate-700">{{ $payout->beneficiary_details["bank_name"] ?? "-" }}</dd></div>
        </dl>
    </div>
</div>

@if ($holdHistory->isNotEmpty())
    <div class="mt-6 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
        <h6 class="mb-4 text-xs font-semibold uppercase tracking-wider text-slate-400">Hold &amp; Release Timeline</h6>
        <ol class="relative border-l-2 border-slate-100 ml-2 space-y-6">
            @foreach ($holdHistory as $tx)
                @php
                    $meta = [
                        "HOLD" => ["dot" => "bg-amber-500", "label" => "Amount held", "icon" => "bi-lock-fill", "text" => "text-amber-700"],
                        "DEBIT" => ["dot" => "bg-emerald-500", "label" => "Hold converted to debit", "icon" => "bi-check-circle-fill", "text" => "text-emerald-700"],
                        "RELEASE" => ["dot" => "bg-rose-500", "label" => "Hold released", "icon" => "bi-arrow-counterclockwise", "text" => "text-rose-700"],
                    ][$tx->type] ?? ["dot" => "bg-slate-400", "label" => $tx->type, "icon" => "bi-dot", "text" => "text-slate-700"];
                @endphp
                <li class="ml-5">
                    <span class="absolute -left-[9px] flex h-4 w-4 items-center justify-center rounded-full {{ $meta["dot"] }} ring-4 ring-white"></span>
                    <div class="flex items-center gap-2 {{ $meta["text"] }} font-semibold text-sm">
                        <i class="bi {{ $meta["icon"] }}"></i> {{ $meta["label"] }}
                        <span class="ml-auto font-mono text-xs text-slate-400">{{ $tx->created_at->format("d M Y H:i:s") }}</span>
                    </div>
                    <p class="mt-1 text-[13px] text-slate-500">{{ $tx->description }}</p>
                </li>
            @endforeach
        </ol>
    </div>
@endif
@endsection
