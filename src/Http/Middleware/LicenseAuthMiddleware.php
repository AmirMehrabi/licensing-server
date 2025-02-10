<?php

namespace LaravelReady\LicenseServer\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use LaravelReady\LicenseServer\Models\LicenseToken;

class LicenseAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response(['message' => 'Unauthorized'], 401);
        }

        $licenseToken = LicenseToken::where('token', $token)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$licenseToken) {
            return response(['message' => 'Invalid or expired token'], 401);
        }

        $licenseToken->update(['last_used_at' => now()]);
        $request->merge(['license' => $licenseToken->license]);

        return $next($request);
    }
}
