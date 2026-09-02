@extends("layouts.app")
@section("title", "Merchants")

@section("content")
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0"><i class="bi bi-shop"></i> Merchants</h3>
    <a href="{{ route("merchants.create") }}" class="btn btn-dark"><i class="bi bi-plus-lg"></i> New Merchant</a>
</div>

<div class="card p-3 mb-4">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-auto">
            <label class="form-label small text-muted mb-1">Search</label>
            <input type="text" name="search" value="{{ request("search") }}" class="form-control form-control-sm" placeholder="Name or email">
        </div>
        <div class="col-auto">
            <label class="form-label small text-muted mb-1">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">All</option>
                <option value="active" @selected(request("status")==="active")>Active</option>
                <option value="inactive" @selected(request("status")==="inactive")>Inactive</option>
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-sm btn-dark"><i class="bi bi-search"></i> Search</button>
            <a href="{{ route("merchants.index") }}" class="btn btn-sm btn-outline-secondary">Reset</a>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Merchant</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Wallet Balance</th>
                    <th>Pay-ins</th>
                    <th>Payouts</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($merchants as $merchant)
                    <tr>
                        <td class="fw-semibold">{{ $merchant->name }}</td>
                        <td>{{ $merchant->email }}</td>
                        <td>
                            <span class="badge {{ $merchant->status === "active" ? "bg-success" : "bg-secondary" }}">{{ ucfirst($merchant->status) }}</span>
                        </td>
                        <td>{{ number_format($merchant->wallet->balance ?? 0, 2) }} {{ $merchant->wallet->currency ?? "" }}</td>
                        <td>{{ $merchant->payins_count }}</td>
                        <td>{{ $merchant->payouts_count }}</td>
                        <td class="text-end">
                            <a href="{{ route("merchants.show", $merchant) }}" class="btn btn-sm btn-outline-dark"><i class="bi bi-eye"></i></a>
                            <a href="{{ route("merchants.edit", $merchant) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No merchants found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">
        {{ $merchants->links("pagination::bootstrap-5") }}
    </div>
</div>
@endsection
