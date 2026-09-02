@extends("layouts.app")
@section("title", $payin->transaction_id)

@section("content")
<a href="{{ route("payins.index") }}" class="text-decoration-none">&larr; Back to Pay-ins</a>

<div class="d-flex justify-content-between align-items-center my-3">
    <h4 class="mb-0"><code class="tx-id fs-6">{{ $payin->transaction_id }}</code></h4>
    @include("partials.status-badge", ["status" => $payin->status])
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card p-3">
            <h6 class="mb-3">Payment Details</h6>
            <table class="table table-sm mb-0">
                <tr><th>Merchant</th><td>{{ $payin->merchant->name }}</td></tr>
                <tr><th>Amount</th><td>{{ number_format($payin->amount, 2) }} {{ $payin->currency }}</td></tr>
                <tr><th>Payment Method</th><td>{{ $payin->payment_method ?? "-" }}</td></tr>
                <tr><th>Remarks</th><td>{{ $payin->remarks ?? "-" }}</td></tr>
                <tr><th>Failure Reason</th><td>{{ $payin->failure_reason ?? "-" }}</td></tr>
                <tr><th>Created At</th><td>{{ $payin->created_at }}</td></tr>
                <tr><th>Processed At</th><td>{{ $payin->processed_at ?? "Not processed yet" }}</td></tr>
            </table>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-3">
            <h6 class="mb-3">Customer Details</h6>
            <table class="table table-sm mb-0">
                <tr><th>Name</th><td>{{ $payin->customer_details["name"] ?? "-" }}</td></tr>
                <tr><th>Email</th><td>{{ $payin->customer_details["email"] ?? "-" }}</td></tr>
                <tr><th>Phone</th><td>{{ $payin->customer_details["phone"] ?? "-" }}</td></tr>
            </table>
        </div>
    </div>
</div>
@endsection
