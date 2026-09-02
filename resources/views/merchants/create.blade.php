@extends("layouts.app")
@section("title", "New Merchant")

@section("content")
<h3 class="flex items-center gap-2 text-xl font-bold text-slate-800 mb-6"><i class="bi bi-plus-lg text-brand-600"></i> New Merchant</h3>

<div class="max-w-lg rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
    <form method="POST" action="{{ route("merchants.store") }}" class="space-y-5">
        @csrf
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Name</label>
            <input type="text" name="name" value="{{ old("name") }}" class="w-full rounded-lg border-slate-200 bg-slate-50 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 @error("name") border-rose-400 @enderror">
            @error("name") <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
            <input type="email" name="email" value="{{ old("email") }}" class="w-full rounded-lg border-slate-200 bg-slate-50 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 @error("email") border-rose-400 @enderror">
            @error("email") <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Phone</label>
            <input type="text" name="phone" value="{{ old("phone") }}" class="w-full rounded-lg border-slate-200 bg-slate-50 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 @error("phone") border-rose-400 @enderror">
            @error("phone") <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Status</label>
            <select name="status" class="w-full rounded-lg border-slate-200 bg-slate-50 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <div class="flex gap-3 pt-2">
            <button class="inline-flex items-center rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-brand-600/30 hover:bg-brand-700 transition">Create Merchant</button>
            <a href="{{ route("merchants.index") }}" class="inline-flex items-center rounded-lg border border-slate-200 px-5 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">Cancel</a>
        </div>
    </form>
</div>
@endsection
