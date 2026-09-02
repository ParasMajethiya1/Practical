<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield("title", "Pay-in & Payout Admin")</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background:#f4f6f9; font-family: "Segoe UI", Roboto, Arial, sans-serif; }
        .sidebar {
            min-height: 100vh; width: 240px; position: fixed; top:0; left:0;
            background: linear-gradient(180deg,#141c2e,#1f2937);
        }
        .sidebar .brand { color:#fff; font-weight:700; font-size:1.15rem; padding:1.25rem 1.25rem .5rem; }
        .sidebar .nav-link { color:#c9d1e0; padding:.65rem 1.25rem; border-radius:.4rem; margin:.15rem .75rem; font-size:.92rem; }
        .sidebar .nav-link.active, .sidebar .nav-link:hover { background:#2f3b52; color:#fff; }
        .main { margin-left:240px; padding:2rem; }
        .stat-card { border:none; border-radius:14px; box-shadow:0 2px 10px rgba(0,0,0,.06); }
        .badge-status-PENDING { background:#f5b301; }
        .badge-status-SUCCESS { background:#1fa855; }
        .badge-status-FAILED { background:#e5484d; }
        .card { border:none; border-radius:14px; box-shadow:0 2px 10px rgba(0,0,0,.05); }
        .table thead th { text-transform:uppercase; font-size:.72rem; letter-spacing:.04em; color:#7c8797; border-bottom-width:1px; }
        code.tx-id { background:#eef1f6; padding:.15rem .4rem; border-radius:.3rem; font-size:.82rem; }
    </style>
</head>
<body>
    <nav class="sidebar">
        <div class="brand"><i class="bi bi-wallet2"></i> Pay-in / Payout</div>
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("merchants.*") ? "active" : "" }}" href="{{ route("merchants.index") }}">
                    <i class="bi bi-shop me-2"></i> Merchants
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("payins.*") ? "active" : "" }}" href="{{ route("payins.index") }}">
                    <i class="bi bi-arrow-down-circle me-2"></i> Pay-ins
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("payouts.*") ? "active" : "" }}" href="{{ route("payouts.index") }}">
                    <i class="bi bi-arrow-up-circle me-2"></i> Payouts
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("wallets.*") ? "active" : "" }}" href="{{ route("wallets.index") }}">
                    <i class="bi bi-cash-stack me-2"></i> Wallets
                </a>
            </li>
        </ul>
    </nav>

    <div class="main">
        @if (session("status"))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session("status") }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session("error"))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session("error") }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield("content")
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
