<?php

namespace App\Http\Controllers;

use App\Service\GoogleAuthService;
use App\Service\TokenValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Google_Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{


    protected $googleAuthService;
    protected $tokenValidationService;

    public function __construct(
        GoogleAuthService $googleAuthService,
        TokenValidationService $tokenValidationService
    ) {
        $this->googleAuthService = $googleAuthService;
        $this->tokenValidationService = $tokenValidationService;
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string|min:5',
            'password' => 'required',
        ]);

        // dd(Auth::attempt([
        //     'username' => $request->username,
        //     'password' => $request->password,
        // ]));

        if (Auth::attempt([
            'username' => $request->username,
            'password' => $request->password,
        ])) {
            
            
            $request->session()->regenerate();
            $user = Auth::user();

            if (!$user->google_refresh_token) {
                // First-time connection required
                Log::info('User must connect Google account', ['user_id' => $user->id]);
                
                return redirect()->route('google.redirect');
            }

            // Check token validity and handle expiration
            if (!$this->tokenValidationService->validateToken($user->google_access_token)) {
                if (!$this->tokenValidationService->handleTokenExpiration($user)) {
                    return redirect()->route('login')->withErrors([
                        'error' => 'Your Google session has expired. Please log in again.'
                    ]);
                }
            }

            return $this->handleLoginRedirect($user);
        }

        return back()->withErrors([
            'password' => 'The provided credentials do not match our records.',
        ])->onlyInput('password');
    }


    // private function refreshTokenIfNeeded($user)
    // {
    //     try {
    //         $token = $this->googleAuthService->refreshToken($user->google_refresh_token);

    //         if ($token && isset($token['access_token'])) {
    //             Log::info('Token refreshed successfully', ['user_id' => $user->id]);

    //             // Update tokens
    //             $user->update([
    //                 'google_access_token' => $token['access_token'],
    //                 'google_refresh_token' => $token['refresh_token'] ?? $user->google_refresh_token, // Only update if a new one is provided
    //                 'token_updated_at' => now(),
    //             ]);
    //             session(['google_token' => $token['access_token']]);
    //             return true;
    //         }

    //         // Token refresh failed, possibly due to an invalid refresh token
    //         Log::warning('Token refresh failed', ['user_id' => $user->id]);
    //         $this->logout();
    //         return false;
    //     } catch (\Exception $e) {
    //         Log::error('Token refresh exception', [
    //             'user_id' => $user->id,
    //             'error' => $e->getMessage(),
    //         ]);
    //         $this->logout();
    //         return false;
    //     }
    // }



    private function handleLoginRedirect($user)
    {
        if (!$user->google_refresh_token) {
            // If no Google connection, redirect to Google OAuth
            return redirect()->route('google.redirect');
        }

        // User has Google connection, redirect based on role
        if ($user->role_name === 'admin') {
            return redirect()->intended('/admin/dashboard');
        } elseif ($user->role_name === 'client') {
            return redirect()->intended('/client/dashboard');
        }

        // Invalid role
        Auth::logout();
        return back()->withErrors([
            'password' => 'Your account is not active',
        ])->onlyInput('password');
    }

    public function welcome()
    {
        if (Auth::check()) {
            $user = Auth::user();
            return redirect($user->role_name === 'admin' ? 'admin/dashboard' : 'client/dashboard');
        }

        return view('welcome');
    }

    public function logout()
    {
        Auth::logout();
        session()->forget('google_token');
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->to('/');
    }
}
