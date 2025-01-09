<?php

namespace App\Service;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TokenValidationService
{
    protected $googleAuthService;

    public function __construct(GoogleAuthService $googleAuthService)
    {
        $this->googleAuthService = $googleAuthService;
    }

    public function validateToken($accessToken)
    {
        try {

            $response = Http::withToken($accessToken)
                ->get('https://www.googleapis.com/oauth2/v3/tokeninfo');

            if ($response->successful()) {

                $tokenInfo = $response->json();

                // Check if token has expired
                if (isset($tokenInfo['exp']) && $tokenInfo['exp'] < time()) {

                    Log::warning('Access token has expired', [
                        'user_id' => Auth::id(),
                        'expiration' => $tokenInfo['exp']
                    ]);

                    return false;
                }

                return true;
            }

            Log::error('Token validation failed', [
                'user_id' => Auth::id(),
                'response' => $response->json()
            ]);

            return false;

        } catch (\Exception $e) {

            Log::error('Token validation exception', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    public function handleTokenExpiration($user)
    {
        try {
            // First try to refresh the token
            if ($user->google_refresh_token) {
                $newTokens = $this->googleAuthService->refreshToken($user->google_refresh_token);

                if ($newTokens && isset($newTokens['access_token'])) {
                    // Update tokens in database
                    $user->update([
                        'google_access_token' => $newTokens['access_token'],
                        'google_refresh_token' => $newTokens['refresh_token'] ?? $user->google_refresh_token,
                        'token_updated_at' => now()
                    ]);

                    // Update session
                    session(['google_token' => $newTokens['access_token']]);

                    Log::info('Tokens refreshed successfully', ['user_id' => $user->id]);
                    return true;
                }
            }

            // If refresh failed or no refresh token, force logout
            $this->forceLogout($user);

            return false;
        } catch (\Exception $e) {
            Log::error('Token refresh exception', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            $this->forceLogout($user);
            return false;
        }
    }

    protected function forceLogout($user)
    {
        // Clear tokens from database
        $user->update([
            'google_access_token' => null,
            'google_refresh_token' => null,
            'token_updated_at' => null
        ]);

        // Clear session and logout
        Auth::logout();
        session()->forget('google_token');
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->to('/');
    }
}
