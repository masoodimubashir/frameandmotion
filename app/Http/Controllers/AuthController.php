<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{


    public function welcome()
    {

        if (Auth::check() && Auth::user()->role_name === 'admin') {
            return redirect('admin/dashboard');
        } elseif (Auth::check() && Auth::user()->role_name === 'client') {
            return redirect('client/dashboard');
        }

        return view('welcome');
    }

    public function login(Request $request)
    {


        $request->validate([
            'username' => 'required|string|min:5',
            'password' => Password::defaults()->required()
        ]);


        if (Auth::attempt([
            'username' => $request->username,
            'password' => $request->password
        ])) {
            $request->session()->regenerate();

            if (Auth::user()->role_name === 'admin') {

                return redirect()->intended('/admin/dashboard');
            } elseif (Auth::user()->role_name === 'client'  && Auth::user()->is_active === 1) {

                return redirect()->intended('/client/dashboard');
            } else {

                Auth::logout();

                return back()->withErrors([
                    'password' => 'Your account is not active',
                ])->onlyInput('password');
            }
        }

        return back()->withErrors([
            'password' => 'The provided credentials do not match our records.',
        ])->onlyInput('password');
    }

    public function logout(Request $request)
    {

        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
