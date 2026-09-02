<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middleware = [
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    protected $middlewareGroups = [
        "web" => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        "api" => [
            \Illuminate\Routing\Middleware\ThrottleRequests::class . ":api",
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    // NOTE: this file assumes the standard Laravel 10 skeleton middleware
    // classes already exist under app/Http/Middleware (TrustProxies,
    // TrimStrings, EncryptCookies, VerifyCsrfToken, etc.) - these ship with
    // every fresh `laravel new` / `composer create-project laravel/laravel`
    // install and are intentionally NOT duplicated in this module. Only
    // AuthenticateMerchant below is custom to this project.
    protected $middlewareAliases = [
        "auth" => \Illuminate\Auth\Middleware\Authenticate::class,
        "auth.merchant" => \App\Http\Middleware\AuthenticateMerchant::class,
        "guest" => \App\Http\Middleware\RedirectIfAuthenticated::class,
        "throttle" => \Illuminate\Routing\Middleware\ThrottleRequests::class,
    ];
}
