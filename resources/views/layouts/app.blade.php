<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $flashedToasts = array_filter([
            "success" => session("status"),
            "error" => session("error"),
            "warning" => session("warning"),
        ]);
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield("title", "Pay-in & Payout Admin")</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: "#eef2ff", 100: "#e0e7ff", 400: "#818cf8",
                            500: "#6366f1", 600: "#4f46e5", 700: "#4338ca", 900: "#1e1b4b",
                        },
                    },
                    fontFamily: {
                        sans: ["Inter", "ui-sans-serif", "system-ui", "Segoe UI", "sans-serif"],
                    },
                    keyframes: {
                        "toast-in": {
                            "0%": { opacity: 0, transform: "translateX(120%) scale(.9)" },
                            "100%": { opacity: 1, transform: "translateX(0) scale(1)" },
                        },
                        "toast-out": {
                            "0%": { opacity: 1, transform: "translateX(0) scale(1)", maxHeight: "120px", marginBottom: "12px" },
                            "100%": { opacity: 0, transform: "translateX(120%) scale(.9)", maxHeight: "0px", marginBottom: "0px" },
                        },
                        "toast-shrink": {
                            "0%": { width: "100%" },
                            "100%": { width: "0%" },
                        },
                        "pop-in": {
                            "0%": { opacity: 0, transform: "scale(.7) rotate(-8deg)" },
                            "60%": { opacity: 1, transform: "scale(1.08) rotate(2deg)" },
                            "100%": { opacity: 1, transform: "scale(1) rotate(0)" },
                        },
                    },
                    animation: {
                        "toast-in": "toast-in .45s cubic-bezier(.34,1.56,.64,1) both",
                        "toast-out": "toast-out .35s cubic-bezier(.4,0,1,1) both",
                        "toast-shrink": "toast-shrink linear forwards",
                        "pop-in": "pop-in .5s cubic-bezier(.34,1.56,.64,1) both",
                    },
                },
            },
        };
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: "Inter", ui-sans-serif, system-ui, sans-serif; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
        code.tx-id { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased">

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="hidden lg:flex lg:flex-col w-64 shrink-0 bg-gradient-to-b from-slate-900 via-[#1a2138] to-brand-900 text-white">
            <div class="flex items-center gap-2 px-6 py-6">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-brand-400 to-brand-600 shadow-lg shadow-brand-900/40">
                    <i class="bi bi-wallet2 text-lg"></i>
                </div>
                <span class="font-bold tracking-tight text-[15px]">Pay-in / Payout</span>
            </div>

            <nav class="flex-1 space-y-1 px-3 mt-2">
                @php
                    $navItems = [
                        ["route" => "merchants.*", "href" => "merchants.index", "icon" => "bi-shop", "label" => "Merchants"],
                        ["route" => "payins.*", "href" => "payins.index", "icon" => "bi-arrow-down-circle", "label" => "Pay-ins"],
                        ["route" => "payouts.*", "href" => "payouts.index", "icon" => "bi-arrow-up-circle", "label" => "Payouts"],
                        ["route" => "wallets.*", "href" => "wallets.index", "icon" => "bi-cash-stack", "label" => "Wallets"],
                    ];
                @endphp
                @foreach ($navItems as $item)
                    @php $active = request()->routeIs($item["route"]); @endphp
                    <a href="{{ route($item["href"]) }}"
                       class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition-all duration-150
                              {{ $active ? "bg-white/10 text-white shadow-inner" : "text-slate-300 hover:bg-white/5 hover:text-white" }}">
                        <i class="bi {{ $item["icon"] }} text-base {{ $active ? "text-brand-400" : "text-slate-400 group-hover:text-brand-400" }} transition-colors"></i>
                        {{ $item["label"] }}
                        @if ($active)
                            <span class="ml-auto h-1.5 w-1.5 rounded-full bg-brand-400"></span>
                        @endif
                    </a>
                @endforeach
            </nav>

            <div class="px-4 py-5 mx-3 mb-4 rounded-xl bg-white/5 border border-white/10">
                <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Payout safety</p>
                <p class="mt-1 text-[12.5px] text-slate-300 leading-snug">Funds are <span class="text-amber-300 font-semibold">held</span> the moment a payout starts, then released or debited once it's processed.</p>
            </div>

            @auth("admin")
                <div class="flex items-center justify-between gap-2 px-4 py-3 mx-3 mb-5 rounded-xl bg-white/5 border border-white/10">
                    <div class="min-w-0">
                        <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Signed in as</p>
                        <p class="text-sm text-white truncate">{{ auth("admin")->user()->name }}</p>
                    </div>
                    <form method="POST" action="{{ route("logout") }}">
                        @csrf
                        <button type="submit" title="Log out"
                                class="shrink-0 flex h-8 w-8 items-center justify-center rounded-lg text-slate-300 hover:bg-white/10 hover:text-white transition-colors">
                            <i class="bi bi-box-arrow-right"></i>
                        </button>
                    </form>
                </div>
            @endauth
        </aside>

        <!-- Main -->
        <div class="flex-1 min-w-0">
            <!-- Topbar (mobile) -->
            <header class="lg:hidden sticky top-0 z-30 flex items-center justify-between bg-slate-900 text-white px-4 py-3 shadow-md">
                <div class="flex items-center gap-2">
                    <i class="bi bi-wallet2"></i>
                    <span class="font-bold">Pay-in / Payout</span>
                </div>
                <button onclick="document.getElementById('mobileNav').classList.toggle('hidden')" class="p-1.5 rounded-lg hover:bg-white/10">
                    <i class="bi bi-list text-xl"></i>
                </button>
            </header>
            <nav id="mobileNav" class="hidden lg:hidden bg-slate-900 text-slate-200 px-3 pb-3 space-y-1">
                <a href="{{ route("merchants.index") }}" class="block rounded-lg px-3 py-2 text-sm {{ request()->routeIs("merchants.*") ? "bg-white/10 text-white" : "" }}"><i class="bi bi-shop me-2"></i>Merchants</a>
                <a href="{{ route("payins.index") }}" class="block rounded-lg px-3 py-2 text-sm {{ request()->routeIs("payins.*") ? "bg-white/10 text-white" : "" }}"><i class="bi bi-arrow-down-circle me-2"></i>Pay-ins</a>
                <a href="{{ route("payouts.index") }}" class="block rounded-lg px-3 py-2 text-sm {{ request()->routeIs("payouts.*") ? "bg-white/10 text-white" : "" }}"><i class="bi bi-arrow-up-circle me-2"></i>Payouts</a>
                <a href="{{ route("wallets.index") }}" class="block rounded-lg px-3 py-2 text-sm {{ request()->routeIs("wallets.*") ? "bg-white/10 text-white" : "" }}"><i class="bi bi-cash-stack me-2"></i>Wallets</a>
                @auth("admin")
                    <form method="POST" action="{{ route("logout") }}" class="pt-1">
                        @csrf
                        <button type="submit" class="w-full text-left block rounded-lg px-3 py-2 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                            <i class="bi bi-box-arrow-right me-2"></i>Log out ({{ auth("admin")->user()->name }})
                        </button>
                    </form>
                @endauth
            </nav>

            <main class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto">
                @yield("content")
            </main>
        </div>
    </div>

    <!-- ============== Toast container ============== -->
    <div id="toast-root" class="fixed top-4 right-4 z-[100] w-[92vw] max-w-sm space-y-3 pointer-events-none"></div>

    <script>
        (function () {
            const toastRoot = document.getElementById("toast-root");

            const THEMES = {
                success: {
                    ring: "ring-emerald-100", bar: "from-emerald-400 to-emerald-600",
                    iconBg: "bg-emerald-500", icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="w-5 h-5 text-white"><path stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>',
                    title: "Success",
                },
                error: {
                    ring: "ring-rose-100", bar: "from-rose-400 to-rose-600",
                    iconBg: "bg-rose-500", icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="w-5 h-5 text-white"><path stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>',
                    title: "Something went wrong",
                },
                warning: {
                    ring: "ring-amber-100", bar: "from-amber-400 to-amber-600",
                    iconBg: "bg-amber-500", icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="w-5 h-5 text-white"><path stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a1 1 0 0 0 .86 1.5h18.64a1 1 0 0 0 .86-1.5L13.71 3.86a1 1 0 0 0-1.72 0Z"/></svg>',
                    title: "Heads up",
                },
                info: {
                    ring: "ring-brand-100", bar: "from-brand-400 to-brand-600",
                    iconBg: "bg-brand-500", icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="w-5 h-5 text-white"><path stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" d="M12 8h.01M11 12h1v4h1"/><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/></svg>',
                    title: "Notice",
                },
            };

            window.showToast = function (message, type = "info", opts = {}) {
                type = THEMES[type] ? type : "info";
                const theme = THEMES[type];
                const duration = opts.duration || 6000;
                const title = opts.title || theme.title;

                const el = document.createElement("div");
                el.className = `pointer-events-auto animate-toast-in relative flex w-full overflow-hidden rounded-2xl bg-white/90 backdrop-blur-md shadow-2xl ring-1 ${theme.ring} border border-slate-100`;
                el.innerHTML = `
                    <div class="flex w-full gap-3 p-4 pr-3">
                        <div class="shrink-0 mt-0.5 flex h-9 w-9 items-center justify-center rounded-full ${theme.iconBg} shadow-lg animate-pop-in">
                            ${theme.icon}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-slate-900">${title}</p>
                            <p class="mt-0.5 text-[13px] leading-snug text-slate-600 break-words">${message}</p>
                        </div>
                        <button class="shrink-0 h-6 w-6 -mt-0.5 rounded-full text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center transition" aria-label="Dismiss">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="w-3.5 h-3.5"><path stroke="currentColor" stroke-width="2.5" stroke-linecap="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="absolute bottom-0 left-0 h-1 w-full bg-slate-100">
                        <div class="h-full bg-gradient-to-r ${theme.bar} animate-toast-shrink" style="animation-duration:${duration}ms"></div>
                    </div>
                `;

                const remove = () => {
                    el.classList.remove("animate-toast-in");
                    el.classList.add("animate-toast-out");
                    el.addEventListener("animationend", () => el.remove(), { once: true });
                };

                el.querySelector("button").addEventListener("click", remove);
                const timer = setTimeout(remove, duration);
                el.addEventListener("mouseenter", () => clearTimeout(timer));

                toastRoot.appendChild(el);
            };

            const flashed = @json($flashedToasts);

            Object.entries(flashed).forEach(([type, message], i) => {
                setTimeout(() => window.showToast(message, type), 150 + i * 180);
            });
        })();
    </script>
</body>
</html>
