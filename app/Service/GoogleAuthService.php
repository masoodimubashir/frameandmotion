<?php

namespace App\Service;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthService
{


    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->scopes([
                'openid',
                'profile',
                'email',
                'https://www.googleapis.com/auth/calendar',
                'https://www.googleapis.com/auth/drive.file',
            ])
            ->with(['access_type' => 'offline'])
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        return Socialite::driver('google')->user();
    }

    public function refreshToken($refreshToken)
    {
        try {
            $response = Http::post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'refresh_token' => $refreshToken,
                'grant_type' => 'refresh_token',
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Token refresh failed', ['response' => $response->json()]);
            return null;
        } catch (\Exception $e) {
            Log::error('Token refresh exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function updateUserGoogleTokens($user, $googleUser)
    {
        try {
            $updateData = [
                'google_id' => $googleUser->getId(),
                'google_access_token' => $googleUser->token,
            ];

            // Only update refresh token if it's provided (first-time login)
            if ($googleUser->refreshToken) {
                $updateData['google_refresh_token'] = $googleUser->refreshToken;
            }

            $user->update($updateData);
            session(['google_token' => $googleUser->token]);

            Log::info('Google tokens updated for user', [
                'user_id' => $user->id,
                'has_refresh_token' => !empty($googleUser->refreshToken)
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update Google tokens', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
