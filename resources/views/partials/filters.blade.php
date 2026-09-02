<form method="GET" class="row g-2 align-items-end mb-4">
    <div class="col-auto">
        <label class="form-label small text-muted mb-1">Status</label>
        <select name="status" class="form-select form-select-sm">
            <option value="">All</option>
            @foreach (["PENDING", "SUCCESS", "FAILED"] as $s)
                <option value="{{ $s }}" @selected(request("status") === $s)>{{ $s }}</option>
            @endforeach
        </select>
    </div>

    @isset($merchants)
        <div class="col-auto">
            <label class="form-label small text-muted mb-1">Merchant</label>
            <select name="merchant_id" class="form-select form-select-sm">
                <option value="">All merchants</option>
                @foreach ($merchants as $merchant)
                    <option value="{{ $merchant->id }}" @selected((string) request("merchant_id") === (string) $merchant->id)>{{ $merchant->name }}</option>
                @endforeach
            </select>
        </div>
    @endisset

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
        <a href="{{ url()->current() }}" class="btn btn-sm btn-outline-secondary">Reset</a>
    </div>
</form>
