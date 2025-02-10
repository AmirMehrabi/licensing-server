<?php

namespace LaravelReady\LicenseServer\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use LaravelReady\LicenseServer\Models\IpAddress;
use LaravelReady\LicenseServer\Models\LicenseToken;
use LaravelReady\UltimateSupport\Supports\IpSupport;
use LaravelReady\LicenseServer\Services\LicenseService;
use LaravelReady\LicenseServer\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Session;
use LaravelReady\LicenseServer\Models\License;

class AuthController extends BaseController
{
    /**
     * Login with sanctum
     *
     * @param Request $request
     * @return Response
     */
    public function login(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'license_key' => 'required|string|uuid',
        ]);
    
        $domain = $request->input('ls_domain');
        $licenseKey = $request->input('license_key');
    
        // Fetch license from database using the domain and license key
        $license = LicenseService::getLicenseByDomain($domain, $licenseKey);
    
        if ($license) {
            // Remove any existing "tokens" associated with the domain (if needed)
            // We will skip the Sanctum-related "tokens()" method and use a custom solution
            // Here we can either clear old tokens from a database or use another method for tokens
    
            // Get the IP address associated with the license
            $ipAddress = IpAddress::where('license_id', $license->id)->first();
            $serverIpAddress = IpSupport::getIpAddress(); // You should already have this utility in place
    
            // If no IP is found for the license, create a new record
            if (!$ipAddress) {
                $ipAddress = IpAddress::create([
                    'license_id' => $license->id,
                    'ip_address' => $serverIpAddress['ip_address'],
                ]);
            }
    
            // Check if the current server IP matches the allowed IP for this license
            if ($ipAddress && $ipAddress->ip_address == $serverIpAddress['ip_address']) {
                // Create new token
                $token = LicenseToken::create([
                    'license_id' => $license->id,
                    'token' => Str::random(60),
                    'expires_at' => now()->addDays(7), // Token expires in 7 days
                    'last_used_at' => now(),
                ]);

                return response([
                    'status' => true,
                    'message' => 'Successfully logged in.',
                    'access_token' => $token->token,
                ]);
            }
    
            // If the IP addresses do not match, return an error response
            return response([
                'status' => false,
                'message' => 'This IP address is not allowed. Please contact the license provider.',
            ], 401);
        }
    
        // Return a response if license is not found
        return response([
            'status' => false,
            'message' => 'Invalid license key or license source.',
        ], 401);
    }
}
