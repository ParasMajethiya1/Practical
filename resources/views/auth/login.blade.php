<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Pay-in & Payout Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: "Inter", ui-sans-serif, system-ui, sans-serif; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-b from-slate-900 via-[#1a2138] to-indigo-950 px-4">

    <div class="w-full max-w-sm">
        <div class="flex flex-col items-center mb-6">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-400 to-indigo-600 shadow-lg shadow-indigo-900/40 mb-3">
                <i class="bi bi-wallet2 text-2xl text-white"></i>
            </div>
            <h1 class="text-white font-bold text-lg tracking-tight">Pay-in / Payout Admin</h1>
            <p class="text-slate-400 text-sm mt-1">Sign in to the back office</p>
        </div>

        <div class="bg-white rounded-2xl shadow-2xl p-6 sm:p-8">
            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif

            @if (session("status"))
                <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-700">
                    {{ session("status") }}
                </div>
            @endif

            <form method="POST" action="{{ route("login") }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input id="email" type="email" name="email" value="{{ old("email") }}" required autofocus
                           autocomplete="username"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                    <input id="password" type="password" name="password" required
                           autocomplete="current-password"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    Remember me
                </label>

                <button type="submit"
                        class="w-full rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm py-2.5 transition-colors">
                    Sign in
                </button>
            </form>
        </div>

        <p class="text-center text-slate-500 text-xs mt-6">Internal staff access only.</p>
    </div>

</body>
</html>
