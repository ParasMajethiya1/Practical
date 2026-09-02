<form method="GET" class="flex flex-wrap items-end gap-3">
    <div>
        <label class="mb-1 block text-xs font-medium text-slate-500">Status</label>
        <select name="status" class="rounded-lg border-slate-200 bg-slate-50 py-1.5 pl-3 pr-8 text-sm text-slate-700 shadow-sm focus:border-brand-500 focus:ring-brand-500">
            <option value="">All</option>
            @foreach (["PENDING", "SUCCESS", "FAILED"] as $s)
                <option value="{{ $s }}" @selected(request("status") === $s)>{{ $s }}</option>
            @endforeach
        </select>
    </div>

    @isset($merchants)
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500">Merchant</label>
            <select name="merchant_id" class="rounded-lg border-slate-200 bg-slate-50 py-1.5 pl-3 pr-8 text-sm text-slate-700 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                <option value="">All merchants</option>
                @foreach ($merchants as $merchant)
                    <option value="{{ $merchant->id }}" @selected((string) request("merchant_id") === (string) $merchant->id)>{{ $merchant->name }}</option>
                @endforeach
            </select>
        </div>
    @endisset

    <div>
        <label class="mb-1 block text-xs font-medium text-slate-500">From</label>
        <input type="date" name="date_from" value="{{ request("date_from") }}" class="rounded-lg border-slate-200 bg-slate-50 py-1.5 text-sm text-slate-700 shadow-sm focus:border-brand-500 focus:ring-brand-500">
    </div>
    <div>
        <label class="mb-1 block text-xs font-medium text-slate-500">To</label>
        <input type="date" name="date_to" value="{{ request("date_to") }}" class="rounded-lg border-slate-200 bg-slate-50 py-1.5 text-sm text-slate-700 shadow-sm focus:border-brand-500 focus:ring-brand-500">
    </div>

    <div class="flex gap-2">
        <button class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-3.5 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-800 transition">
            <i class="bi bi-funnel"></i> Filter
        </button>
        <a href="{{ url()->current() }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3.5 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
            Reset
        </a>
    </div>
</form>
