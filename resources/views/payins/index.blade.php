@extends("layouts.app")
@section("title", "Pay-ins")

@section("content")
<h3 class="mb-4"><i class="bi bi-arrow-down-circle"></i> Pay-ins</h3>

<div class="card p-3 mb-4">
    @include("partials.filters")
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Merchant</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payins as $payin)
                    <tr>
                        <td><code class="tx-id">{{ $payin->transaction_id }}</code></td>
                        <td>{{ $payin->merchant->name }}</td>
                        <td>{{ number_format($payin->amount, 2) }} {{ $payin->currency }}</td>
                        <td>{{ $payin->payment_method ?? "-" }}</td>
                        <td>@include("partials.status-badge", ["status" => $payin->status])</td>
                        <td>{{ $payin->created_at->format("d M Y H:i") }}</td>
                        <td class="text-end">
                            <a href="{{ route("payins.show", $payin) }}" class="btn btn-sm btn-outline-dark"><i class="bi bi-eye"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No pay-ins found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">
        {{ $payins->links("pagination::bootstrap-5") }}
    </div>
</div>
@endsection
