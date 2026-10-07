<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\AuditLog;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        $demoUsers = User::whereIn('email', [
            'admin@simpledairy.com',
            'operator@simpledairy.com',
            'delivery@simpledairy.com',
            'accountant@simpledairy.com',
            'farmer@simpledairy.com',
            'customer@simpledairy.com',
            'superadmin@simpledairy.com',
        ])->get();

        return view('auth.login', compact('demoUsers'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if (Auth::attempt([$field => $request->login, 'password' => $request->password], $request->filled('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->status === 'blocked') {
                Auth::logout();
                return back()->withErrors(['login' => 'Your account is blocked. Please contact dairy admin.']);
            }

            AuditLog::log('User Logged In', 'User', $user->id, ['ip' => $request->ip()]);

            return redirect()->intended(route('dashboard'))->with('success', 'Welcome back, ' . $user->name . '!');
        }

        return back()->withErrors([
            'login' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('login'));
    }

    public function demoLogin(User $user)
    {
        Auth::login($user);
        request()->session()->regenerate();
        AuditLog::log('Demo Switch Login', 'User', $user->id);
        return redirect()->route('dashboard')->with('success', 'Logged in as ' . $user->name . ' (' . ucfirst(str_replace('_', ' ', $user->role)) . ')');
    }

    public function logout(Request $request)
    {
        AuditLog::log('User Logged Out', 'User', Auth::id());
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('info', 'You have been logged out.');
    }

    public function profile()
    {
        $user = Auth::user();
        return view('auth.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20|unique:users,phone,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $user->name = $validated['name'];
        if (!empty($validated['phone'])) {
            $user->phone = $validated['phone'];
        }
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        AuditLog::log('Profile Updated', 'User', $user->id);
        return back()->with('success', 'Profile updated successfully.');
    }
}
