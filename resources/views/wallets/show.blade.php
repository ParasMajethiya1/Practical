@extends("layouts.app")
@section("title", $wallet->merchant->name . " Wallet")

@section("content")
<a href="{{ route("wallets.index") }}" class="text-decoration-none">&larr; Back to Wallets</a>

<div class="d-flex justify-content-between align-items-center my-3">
    <h4 class="mb-0">{{ $wallet->merchant->name }}</h4>
    <div class="fs-4 fw-bold">{{ number_format($wallet->balance, 2) }} {{ $wallet->currency }}</div>
</div>

<div class="card p-3 mb-4">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-auto">
            <label class="form-label small text-muted mb-1">Type</label>
            <select name="type" class="form-select form-select-sm">
                <option value="">All</option>
                <option value="CREDIT" @selected(request("type")==="CREDIT")>Credit</option>
                <option value="DEBIT" @selected(request("type")==="DEBIT")>Debit</option>
            </select>
        </div>
        <div class="col-auto">
            <label class="form-label small text-muted mb-1">From</label>
            <input type="date" name="date_from" value="{{ request("date_from") }}" class="form-control form-control-sm">
        </div>
        <div class="col-auto">
            <label class="form-label small text-muted mb-1">To</label>
            <input type="date" name="date_to" value="{{ request("date_to") }}" class="form-control form-control-sm">
        </div>
        <div class="col-auto">
            <button class="btn btn-sm btn-dark"><i class="bi bi-funnel"></i> Filter</button>
            <a href="{{ route("wallets.show", $wallet) }}" class="btn btn-sm btn-outline-secondary">Reset</a>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Balance Before</th>
                    <th>Balance After</th>
                    <th>Reference</th>
                    <th>Description</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $tx)
                    <tr>
                        <td>
                            <span class="badge {{ $tx->type === "CREDIT" ? "bg-success" : "bg-danger" }}">{{ $tx->type }}</span>
                        </td>
                        <td>{{ number_format($tx->amount, 2) }}</td>
                        <td>{{ number_format($tx->balance_before, 2) }}</td>
                        <td>{{ number_format($tx->balance_after, 2) }}</td>
                        <td class="text-uppercase small text-muted">{{ $tx->reference_type }} #{{ $tx->reference_id }}</td>
                        <td>{{ $tx->description }}</td>
                        <td>{{ $tx->created_at->format("d M Y H:i") }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No wallet transactions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">
        {{ $transactions->links("pagination::bootstrap-5") }}
    </div>
</div>
@endsection
