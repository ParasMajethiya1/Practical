@extends("layouts.app")
@section("title", "Merchants")

@section("content")
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <h3 class="flex items-center gap-2 text-xl font-bold text-slate-800">
        <i class="bi bi-shop text-brand-600"></i> Merchants
    </h3>
    <a href="{{ route("merchants.create") }}" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm shadow-brand-600/30 hover:bg-brand-700 transition">
        <i class="bi bi-plus-lg"></i> New Merchant
    </a>
</div>

<div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm mb-6">
    <form method="GET" class="flex flex-wrap items-end gap-3">
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500">Search</label>
            <input type="text" name="search" value="{{ request("search") }}" placeholder="Name or email" class="rounded-lg border-slate-200 bg-slate-50 py-1.5 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500">Status</label>
            <select name="status" class="rounded-lg border-slate-200 bg-slate-50 py-1.5 pl-3 pr-8 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                <option value="">All</option>
                <option value="active" @selected(request("status")==="active")>Active</option>
                <option value="inactive" @selected(request("status")==="inactive")>Inactive</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-3.5 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-800 transition"><i class="bi bi-search"></i> Search</button>
            <a href="{{ route("merchants.index") }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3.5 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">Reset</a>
        </div>
    </form>
</div>

<div class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                    <th class="px-5 py-3">Merchant</th>
                    <th class="px-5 py-3">Email</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">Wallet Balance</th>
                    <th class="px-5 py-3">Pay-ins</th>
                    <th class="px-5 py-3">Payouts</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($merchants as $merchant)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-5 py-3 font-semibold text-slate-700">{{ $merchant->name }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ $merchant->email }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $merchant->status === "active" ? "bg-emerald-50 text-emerald-700 ring-emerald-200" : "bg-slate-100 text-slate-500 ring-slate-200" }}">{{ ucfirst($merchant->status) }}</span>
                        </td>
                        <td class="px-5 py-3 font-medium text-slate-700">{{ number_format($merchant->wallet->balance ?? 0, 2) }} {{ $merchant->wallet->currency ?? "" }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ $merchant->payins_count }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ $merchant->payouts_count }}</td>
                        <td class="px-5 py-3 text-right space-x-1">
                            <a href="{{ route("merchants.show", $merchant) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 h-8 w-8 text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition"><i class="bi bi-eye"></i></a>
                            <a href="{{ route("merchants.edit", $merchant) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 h-8 w-8 text-brand-600 hover:bg-brand-50 transition"><i class="bi bi-pencil"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-12 text-center text-slate-400">No merchants found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-5 py-3">
        {{ $merchants->links() }}
    </div>
</div>
@endsection
