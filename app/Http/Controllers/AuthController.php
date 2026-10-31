<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $loginInput = $request->input('email');
        $password = $request->input('password');

        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        if (Auth::attempt([$fieldType => $loginInput, 'password' => $password], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'))->with('success', 'Selamat datang kembali, Pemasok!');
        }

        // Also fallback to check if user typed 'admin' matching email starting with admin
        if ($fieldType === 'name') {
            $userByEmail = User::where('email', 'like', $loginInput . '@%')->first();
            if ($userByEmail && \Illuminate\Support\Facades\Hash::check($password, $userByEmail->password)) {
                Auth::login($userByEmail, $request->boolean('remember'));
                $request->session()->regenerate();
                return redirect()->intended(route('admin.dashboard'))->with('success', 'Selamat datang kembali, Pemasok!');
            }
        }

        return back()->withErrors([
            'email' => 'Username/Email atau password salah. Silakan coba lagi.',
        ])->onlyInput('email');
    }

    public function loginDemo()
    {
        $admin = User::first();
        if (!$admin) {
            $admin = User::create([
                'name' => 'Distributor Keripik Pisang Juara',
                'email' => 'admin@keripikpisang.com',
                'password' => bcrypt('admin123'),
            ]);
        }

        Auth::login($admin);
        request()->session()->regenerate();
        return redirect()->route('admin.dashboard')->with('success', 'Berhasil masuk ke Dashboard Pemasok (Mode Demo Admin).');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('info', 'Anda telah keluar dari sistem.');
    }
}
