@extends("layouts.app")
@section("title", "New Merchant")

@section("content")
<h3 class="mb-4"><i class="bi bi-plus-lg"></i> New Merchant</h3>

<div class="card p-4" style="max-width:560px">
    <form method="POST" action="{{ route("merchants.store") }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" value="{{ old("name") }}" class="form-control @error("name") is-invalid @enderror">
            @error("name") <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="{{ old("email") }}" class="form-control @error("email") is-invalid @enderror">
            @error("email") <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" value="{{ old("phone") }}" class="form-control @error("phone") is-invalid @enderror">
            @error("phone") <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <button class="btn btn-dark">Create Merchant</button>
        <a href="{{ route("merchants.index") }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>
@endsection
