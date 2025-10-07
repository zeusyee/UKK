<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Form Register
    public function showRegister()
    {
        return view('auth.register');
    }

    // Proses Register
    public function register(Request $request)
    {
        $request->validate([
            'username'   => 'required|unique:users,username',
            'full_name'  => 'required|string|max:100',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|min:6|confirmed',
        ]);

        User::create([
            'username'            => $request->username,
            'full_name'           => $request->full_name,
            'name'                => $request->full_name, // ✅ isi kolom name juga
            'email'               => $request->email,
            'password'            => Hash::make($request->password),
            'role'                => 'user', // default role
            'current_task_status' => 'available', // default status
        ]);

        return redirect()->route('login')->with('success', 'Registrasi berhasil, silakan login.');
    }

    // Form Login
    public function showLogin()
    {
        return view('auth.login');
    }

    // Proses Login (gunakan email)
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Arahkan sesuai role
            return match ($user->role) {
                'admin'  => redirect()->route('admin.dashboard'),
                'leader' => redirect()->route('leader.dashboard'),
                default  => redirect()->route('user.dashboard'),
            };
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
