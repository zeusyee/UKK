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
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'leader') {
            return redirect()->route('leader.dashboard');
        }
        return view('user.dashboard', compact('user'));
    }

    // List semua user (admin)
    public function index()
    {
        $users = \App\Models\User::all();
        return view('admin.user.index', compact('users'));
    }

    // Tampilkan form create user
    public function create()
    {
        return view('admin.user.create');
    }

    // Simpan user baru
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required',
        ]);
        \App\Models\User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            'full_name' => $request->full_name,
        ]);
        return redirect()->route('admin.user.index')->with('success', 'User berhasil dibuat!');
    }

    // Edit user
    public function edit($id)
    {
        $user = \App\Models\User::findOrFail($id);
        return view('admin.user.edit', compact('user'));
    }

    // Update user
    public function update(Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);
        $request->validate([
            'username' => 'required|unique:users,username,' . $user->user_id . ',user_id',
            'email' => 'required|email|unique:users,email,' . $user->user_id . ',user_id',
            'role' => 'required',
        ]);
        $user->update($request->only(['username', 'email', 'role', 'full_name']));
        return redirect()->route('admin.user.index')->with('success', 'User berhasil diupdate!');
    }

    // Hapus user
    public function destroy($id)
    {
        $user = \App\Models\User::findOrFail($id);
        $user->delete();
        return redirect()->route('admin.user.index')->with('success', 'User berhasil dihapus!');
    }

    // Lihat progres user
    public function progress($id)
    {
        $user = \App\Models\User::findOrFail($id);
        $tasks = \App\Models\Card::where('assigned_to', $id)->get();
        return view('admin.user.progress', compact('user', 'tasks'));
    }

    // Laporan performa user
    public function report($id)
    {
        $user = \App\Models\User::findOrFail($id);
        $tasks = \App\Models\Card::where('assigned_to', $id)->get();
        $done = $tasks->where('status', 'done')->count();
        $total = $tasks->count();
        return view('admin.user.report', compact('user', 'tasks', 'done', 'total'));
    }
}
