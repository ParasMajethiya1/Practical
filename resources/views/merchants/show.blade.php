@extends("layouts.app")
@section("title", $merchant->name)

@section("content")
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0"><i class="bi bi-shop"></i> {{ $merchant->name }}</h3>
    <div>
        <a href="{{ route("merchants.edit", $merchant) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
        <form action="{{ route("merchants.destroy", $merchant) }}" method="POST" class="d-inline" onsubmit="return confirm("Delete this merchant?")">
            @csrf @method("DELETE")
            <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> Delete</button>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Wallet Balance</div>
            <div class="fs-4 fw-bold">{{ number_format($merchant->wallet->balance ?? 0, 2) }} {{ $merchant->wallet->currency ?? "" }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Email</div>
            <div class="fw-semibold">{{ $merchant->email }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Status</div>
            <span class="badge {{ $merchant->status === "active" ? "bg-success" : "bg-secondary" }}">{{ ucfirst($merchant->status) }}</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Member Since</div>
            <div class="fw-semibold">{{ $merchant->created_at->format("d M Y") }}</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card p-3">
            <h6 class="mb-3">Recent Pay-ins</h6>
            <table class="table table-sm mb-0">
                <thead><tr><th>Txn ID</th><th>Amount</th><th>Status</th></tr></thead>
                <tbody>
                @forelse ($recentPayins as $p)
                    <tr>
                        <td><a href="{{ route("payins.show", $p) }}"><code class="tx-id">{{ $p->transaction_id }}</code></a></td>
                        <td>{{ number_format($p->amount, 2) }}</td>
                        <td>@include("partials.status-badge", ["status" => $p->status])</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-muted text-center">No pay-ins yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-3">
            <h6 class="mb-3">Recent Payouts</h6>
            <table class="table table-sm mb-0">
                <thead><tr><th>Txn ID</th><th>Amount</th><th>Status</th></tr></thead>
                <tbody>
                @forelse ($recentPayouts as $p)
                    <tr>
                        <td><a href="{{ route("payouts.show", $p) }}"><code class="tx-id">{{ $p->transaction_id }}</code></a></td>
                        <td>{{ number_format($p->amount, 2) }}</td>
                        <td>@include("partials.status-badge", ["status" => $p->status])</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-muted text-center">No payouts yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
