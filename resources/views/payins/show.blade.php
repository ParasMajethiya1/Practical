@extends("layouts.app")
@section("title", $payin->transaction_id)

@section("content")
<a href="{{ route("payins.index") }}" class="text-sm text-slate-500 hover:text-slate-800 transition">&larr; Back to Pay-ins</a>

<div class="flex flex-wrap items-center justify-between gap-3 my-4">
    <h4 class="flex items-center gap-3 text-lg font-bold text-slate-800">
        <code class="tx-id rounded-md bg-slate-100 px-2.5 py-1 text-sm text-slate-600">{{ $payin->transaction_id }}</code>
        @include("partials.status-badge", ["status" => $payin->status])
    </h4>
    <div class="text-2xl font-extrabold text-slate-800">{{ number_format($payin->amount, 2) }} <span class="text-sm font-medium text-slate-400">{{ $payin->currency }}</span></div>
</div>

<div class="grid gap-4 md:grid-cols-2">
    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
        <h6 class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Payment Details</h6>
        <dl class="divide-y divide-slate-50 text-sm">
            <div class="flex justify-between py-2"><dt class="text-slate-500">Merchant</dt><dd class="font-medium text-slate-700">{{ $payin->merchant->name }}</dd></div>
            <div class="flex justify-between py-2"><dt class="text-slate-500">Amount</dt><dd class="font-medium text-slate-700">{{ number_format($payin->amount, 2) }} {{ $payin->currency }}</dd></div>
            <div class="flex justify-between py-2"><dt class="text-slate-500">Payment Method</dt><dd class="font-medium text-slate-700">{{ $payin->payment_method ?? "-" }}</dd></div>
            <div class="flex justify-between py-2"><dt class="text-slate-500">Remarks</dt><dd class="font-medium text-slate-700">{{ $payin->remarks ?? "-" }}</dd></div>
            <div class="flex justify-between py-2"><dt class="text-slate-500">Failure Reason</dt><dd class="font-medium text-rose-600">{{ $payin->failure_reason ?? "-" }}</dd></div>
            <div class="flex justify-between py-2"><dt class="text-slate-500">Created At</dt><dd class="font-medium text-slate-700">{{ $payin->created_at }}</dd></div>
            <div class="flex justify-between py-2"><dt class="text-slate-500">Processed At</dt><dd class="font-medium text-slate-700">{{ $payin->processed_at ?? "Not processed yet" }}</dd></div>
        </dl>
    </div>
    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
        <h6 class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Customer Details</h6>
        <dl class="divide-y divide-slate-50 text-sm">
            <div class="flex justify-between py-2"><dt class="text-slate-500">Name</dt><dd class="font-medium text-slate-700">{{ $payin->customer_details["name"] ?? "-" }}</dd></div>
            <div class="flex justify-between py-2"><dt class="text-slate-500">Email</dt><dd class="font-medium text-slate-700">{{ $payin->customer_details["email"] ?? "-" }}</dd></div>
            <div class="flex justify-between py-2"><dt class="text-slate-500">Phone</dt><dd class="font-medium text-slate-700">{{ $payin->customer_details["phone"] ?? "-" }}</dd></div>
        </dl>
    </div>
</div>
@endsection
