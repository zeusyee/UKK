<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // Dashboard untuk user, admin, dan leader
    public function dashboard()
    {
        $user = Auth::user();
        // Redirect ke dashboard sesuai role jika akses ke /dashboard
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'leader') {
            return redirect()->route('leader.dashboard');
        }
        // Default: user
        return view('user.dashboard', compact('user'));
    }
}
