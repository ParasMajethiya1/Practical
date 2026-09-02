<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Session-based login for the Blade back-office panel (routes/web.php).
 * Separate from merchant API auth (X-API-KEY, see AuthenticateMerchant).
 */
class AdminAuthController extends Controller
{
    public function showLoginForm(): \Illuminate\View\View
    {
        return view("auth.login");
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            "email" => ["required", "email"],
            "password" => ["required", "string"],
        ]);

        $remember = $request->boolean("remember");

        if (! Auth::guard("admin")->attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                "email" => "These credentials do not match our records.",
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route("merchants.index"))
            ->with("status", "Welcome back, " . Auth::guard("admin")->user()->name . "!");
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard("admin")->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route("login")->with("status", "You have been logged out.");
    }
}
