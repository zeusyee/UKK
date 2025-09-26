<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use App\Models\ProjectMember;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // Dashboard Admin
    public function dashboard()
    {
        $users = User::all();
        $projects = Project::all();
        $members = ProjectMember::with(['user','project'])->get();

        return view('admin.dashboard', compact('users', 'projects', 'members'));
    }

    // Tampilkan halaman kelola anggota proyek
    public function projectMember()
    {
        $users = User::all();
        $projects = Project::all();
        $members = ProjectMember::with(['project', 'user'])->get();
        return view('admin.project-member', compact('users', 'projects', 'members'));
    }

    // Tambah user ke project_members
    public function addMember(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'project_id' => 'required|exists:projects,project_id',
            'role' => 'required|in:admin,leader,user'
        ]);

        ProjectMember::create([
            'user_id' => $request->user_id,
            'project_id' => $request->project_id,
            'role' => $request->role
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'User berhasil ditambahkan ke project.');
    }

    // Hapus member dari project
    public function removeMember($id)
    {
        ProjectMember::findOrFail($id)->delete();
        return redirect()->route('admin.dashboard')->with('success', 'Member berhasil dihapus.');
    }
}
