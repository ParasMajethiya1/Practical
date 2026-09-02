@extends("layouts.app")
@section("title", "Wallets")

@section("content")
<h3 class="flex items-center gap-2 text-xl font-bold text-slate-800 mb-6">
    <i class="bi bi-cash-stack text-brand-600"></i> Wallets
</h3>

<div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm mb-6">
    <form method="GET" class="flex flex-wrap items-end gap-3">
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500">Search Merchant</label>
            <input type="text" name="search" value="{{ request("search") }}" class="rounded-lg border-slate-200 bg-slate-50 py-1.5 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
        </div>
        <div class="flex gap-2">
            <button class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-3.5 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-800 transition"><i class="bi bi-search"></i> Search</button>
            <a href="{{ route("wallets.index") }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3.5 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">Reset</a>
        </div>
    </form>
</div>

<div class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                    <th class="px-5 py-3">Merchant</th>
                    <th class="px-5 py-3">Balance</th>
                    <th class="px-5 py-3">On Hold</th>
                    <th class="px-5 py-3">Available</th>
                    <th class="px-5 py-3">Currency</th>
                    <th class="px-5 py-3">Last Updated</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($wallets as $wallet)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-5 py-3 font-semibold text-slate-700">{{ $wallet->merchant->name }}</td>
                        <td class="px-5 py-3 text-slate-700">{{ number_format($wallet->balance, 2) }}</td>
                        <td class="px-5 py-3">
                            @if ($wallet->held_amount > 0)
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-200">
                                    <i class="bi bi-lock-fill text-[10px]"></i> {{ number_format($wallet->held_amount, 2) }}
                                </span>
                            @else
                                <span class="text-slate-300">-</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 font-semibold text-emerald-600">{{ number_format($wallet->available_balance, 2) }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ $wallet->currency }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ $wallet->updated_at->diffForHumans() }}</td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route("wallets.show", $wallet) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-800 transition"><i class="bi bi-eye"></i> Ledger</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-12 text-center text-slate-400">No wallets found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-5 py-3">
        {{ $wallets->links() }}
    </div>
</div>
@endsection
