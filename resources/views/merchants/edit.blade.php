@extends("layouts.app")
@section("title", "Edit Merchant")

@section("content")
<h3 class="mb-4"><i class="bi bi-pencil"></i> Edit Merchant</h3>

<div class="card p-4" style="max-width:560px">
    <form method="POST" action="{{ route("merchants.update", $merchant) }}">
        @csrf
        @method("PUT")
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" value="{{ old("name", $merchant->name) }}" class="form-control @error("name") is-invalid @enderror">
            @error("name") <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="{{ old("email", $merchant->email) }}" class="form-control @error("email") is-invalid @enderror">
            @error("email") <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" value="{{ old("phone", $merchant->phone) }}" class="form-control @error("phone") is-invalid @enderror">
            @error("phone") <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="active" @selected($merchant->status === "active")>Active</option>
                <option value="inactive" @selected($merchant->status === "inactive")>Inactive</option>
            </select>
        </div>
        <button class="btn btn-dark">Save Changes</button>
        <a href="{{ route("merchants.show", $merchant) }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>
@endsection
