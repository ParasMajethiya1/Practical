@extends("layouts.app")
@section("title", $payout->transaction_id)

@section("content")
<a href="{{ route("payouts.index") }}" class="text-decoration-none">&larr; Back to Payouts</a>

<div class="d-flex justify-content-between align-items-center my-3">
    <h4 class="mb-0"><code class="tx-id fs-6">{{ $payout->transaction_id }}</code></h4>
    @include("partials.status-badge", ["status" => $payout->status])
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card p-3">
            <h6 class="mb-3">Payment Details</h6>
            <table class="table table-sm mb-0">
                <tr><th>Merchant</th><td>{{ $payout->merchant->name }}</td></tr>
                <tr><th>Amount</th><td>{{ number_format($payout->amount, 2) }} {{ $payout->currency }}</td></tr>
                <tr><th>Payout Method</th><td>{{ $payout->payout_method ?? "-" }}</td></tr>
                <tr><th>Remarks</th><td>{{ $payout->remarks ?? "-" }}</td></tr>
                <tr><th>Failure Reason</th><td>{{ $payout->failure_reason ?? "-" }}</td></tr>
                <tr><th>Created At</th><td>{{ $payout->created_at }}</td></tr>
                <tr><th>Processed At</th><td>{{ $payout->processed_at ?? "Not processed yet" }}</td></tr>
            </table>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-3">
            <h6 class="mb-3">Beneficiary Details</h6>
            <table class="table table-sm mb-0">
                <tr><th>Name</th><td>{{ $payout->beneficiary_details["name"] ?? "-" }}</td></tr>
                <tr><th>Account Number</th><td>{{ $payout->beneficiary_details["account_number"] ?? "-" }}</td></tr>
                <tr><th>IFSC</th><td>{{ $payout->beneficiary_details["ifsc"] ?? "-" }}</td></tr>
                <tr><th>Bank Name</th><td>{{ $payout->beneficiary_details["bank_name"] ?? "-" }}</td></tr>
            </table>
        </div>
    </div>
</div>
@endsection
