<?php

namespace App\Http\Controllers;

use App\Models\AdminLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function adminLoginPost(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $key = 'login:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => 'Trop de tentatives. Réessayez dans ' . ceil($seconds / 60) . ' minutes.',
            ])->onlyInput('email');
        }

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($key, 900);
            return back()->withErrors([
                'email' => 'Email ou mot de passe incorrect.',
            ])->onlyInput('email');
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        AdminLog::create([
            'user_id' => Auth::id(),
            'action' => 'Connexion',
            'details' => 'Connexion depuis ' . $request->ip(),
            'ip_address' => $request->ip(),
        ]);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function adminLogout(Request $request)
    {
        AdminLog::create([
            'user_id' => Auth::id(),
            'action' => 'Déconnexion',
            'details' => 'Déconnexion depuis ' . $request->ip(),
            'ip_address' => $request->ip(),
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }
}