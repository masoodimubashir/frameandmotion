<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Service\GoogleAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{

    protected $googleAuthService;

    public function __construct(GoogleAuthService $googleAuthService)
    {
        $this->googleAuthService = $googleAuthService;
    }

    public function redirectToGoogle()
    {
        return $this->googleAuthService->redirectToGoogle();
    }

    public function handleGoogleCallback()
    {
        try {

            $googleUser = $this->googleAuthService->handleGoogleCallback();

//            dd($googleUser);

            $user = Auth::user();

            if (!$user) {
                throw new \Exception('No authenticated user found');
            }

            // Store Google credentials
            $user->update([
                'google_id' => $googleUser->getId(),
                'google_access_token' => $googleUser->token,
                'google_refresh_token' => $googleUser->refreshToken,
                'token_updated_at' => now()
            ]);

            // Store in session
            session(['google_token' => $googleUser->token]);

            Log::info('Google account linked successfully', [
                'user_id' => $user->id,
                'has_refresh_token' => !empty($googleUser->refreshToken)
            ]);

            return redirect()->intended($user->role_name === 'admin' ? '/admin/dashboard' : '/client/dashboard')
                ->with('success', 'Google account linked successfully');
        } catch (\Exception $e) {
            Log::error('Google callback error', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to link Google account']);
        }
    }

}
