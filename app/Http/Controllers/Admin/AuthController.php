<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $throttleKey = 'admin_login:' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->with('error', "Too many failed attempts. Please try again in {$seconds} seconds.");
        }

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt(array_merge($credentials, ['is_admin' => true]), $remember)) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            AuditLog::logAction('admin_login_success', 'User', (string) Auth::id(), [
                'email' => $credentials['email'],
            ]);

            return redirect()->intended(route('admin.dashboard'))
                ->with('success', 'Welcome back, ' . Auth::user()->name);
        }

        RateLimiter::hit($throttleKey, 300);

        AuditLog::logAction('admin_login_failed', 'User', null, [
            'email' => $credentials['email'],
        ]);

        return back()->withInput($request->only('email'))
            ->with('error', 'Invalid administrator credentials.');
    }

    public function logout(Request $request)
    {
        $adminId = Auth::id();

        AuditLog::logAction('admin_logout', 'User', (string) $adminId);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('success', 'Logged out successfully.');
    }
}
