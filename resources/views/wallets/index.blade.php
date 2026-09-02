@extends("layouts.app")
@section("title", "Wallets")

@section("content")
<h3 class="mb-4"><i class="bi bi-cash-stack"></i> Wallets</h3>

<div class="card p-3 mb-4">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-auto">
            <label class="form-label small text-muted mb-1">Search Merchant</label>
            <input type="text" name="search" value="{{ request("search") }}" class="form-control form-control-sm">
        </div>
        <div class="col-auto">
            <button class="btn btn-sm btn-dark"><i class="bi bi-search"></i> Search</button>
            <a href="{{ route("wallets.index") }}" class="btn btn-sm btn-outline-secondary">Reset</a>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Merchant</th>
                    <th>Balance</th>
                    <th>Currency</th>
                    <th>Last Updated</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($wallets as $wallet)
                    <tr>
                        <td class="fw-semibold">{{ $wallet->merchant->name }}</td>
                        <td>{{ number_format($wallet->balance, 2) }}</td>
                        <td>{{ $wallet->currency }}</td>
                        <td>{{ $wallet->updated_at->diffForHumans() }}</td>
                        <td class="text-end">
                            <a href="{{ route("wallets.show", $wallet) }}" class="btn btn-sm btn-outline-dark"><i class="bi bi-eye"></i> Ledger</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No wallets found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">
        {{ $wallets->links("pagination::bootstrap-5") }}
    </div>
</div>
@endsection
