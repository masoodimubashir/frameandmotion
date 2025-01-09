<?php

namespace App\Http\Middleware;

use App\Service\TokenValidationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ValidateGoogleToken
{
    protected $tokenValidationService;

    public function __construct(TokenValidationService $tokenValidationService)
    {
        $this->tokenValidationService = $tokenValidationService;
    }

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();


        if (!$user || !$user->google_access_token) {

            Log::warning('!$user || !$user->google_access_token');
            return $next($request);
        }


        // Validate the current access token
        if (!$this->tokenValidationService->validateToken($user->google_access_token)) {
           
           
            Log::warning('validateToken method');
            
            // Token is invalid or expired, try to handle it
            if (!$this->tokenValidationService->handleTokenExpiration($user)) {
                // If token handling failed, redirect to login with error
            Log::warning('handleTokenExpiration method');

                return redirect()->route('login')->withErrors([
                    'error' => 'Your Google session has expired. Please log in again.'
                ]);
            }
        }

        Log::warning('No if executed');

        return $next($request);
    }
}
