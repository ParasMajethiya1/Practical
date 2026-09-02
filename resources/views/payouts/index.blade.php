@extends("layouts.app")
@section("title", "Payouts")

@section("content")
<h3 class="mb-4"><i class="bi bi-arrow-up-circle"></i> Payouts</h3>

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
                @forelse ($payouts as $payout)
                    <tr>
                        <td><code class="tx-id">{{ $payout->transaction_id }}</code></td>
                        <td>{{ $payout->merchant->name }}</td>
                        <td>{{ number_format($payout->amount, 2) }} {{ $payout->currency }}</td>
                        <td>{{ $payout->payout_method ?? "-" }}</td>
                        <td>@include("partials.status-badge", ["status" => $payout->status])</td>
                        <td>{{ $payout->created_at->format("d M Y H:i") }}</td>
                        <td class="text-end">
                            <a href="{{ route("payouts.show", $payout) }}" class="btn btn-sm btn-outline-dark"><i class="bi bi-eye"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No payouts found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">
        {{ $payouts->links("pagination::bootstrap-5") }}
    </div>
</div>
@endsection
