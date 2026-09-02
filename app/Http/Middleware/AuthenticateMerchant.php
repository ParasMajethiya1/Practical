<?php

namespace App\Http\Middleware;

use App\Models\Merchant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Simple API-key authentication for merchants.
 * Merchants call the API with header:  X-API-KEY: <their api key>
 */
class AuthenticateMerchant
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header("X-API-KEY");

        if (! $apiKey) {
            return response()->json([
                "success" => false,
                "message" => "Missing X-API-KEY header.",
            ], 401);
        }

        $merchant = Merchant::where("api_key", $apiKey)->first();

        if (! $merchant) {
            return response()->json([
                "success" => false,
                "message" => "Invalid API key.",
            ], 401);
        }

        if (! $merchant->isActive()) {
            return response()->json([
                "success" => false,
                "message" => "Merchant account is inactive.",
            ], 403);
        }

        // Available to controllers via $request->merchant
        $request->attributes->set("merchant", $merchant);
        $request->setUserResolver(fn () => $merchant);

        return $next($request);
    }
}
