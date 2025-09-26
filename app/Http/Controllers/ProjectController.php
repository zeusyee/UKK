<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{

    public function index()
    {
        $projects = Project::with('creator')->latest()->get();

        return view('admin.project.index', compact('projects'));
    }
    // Tampilkan form create project (khusus admin)
    public function create()
    {
        return view('admin.project.create');
    }

    // Proses simpan project baru
    public function store(Request $request)
    {
        $request->validate([
            'project_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date',
        ]);

        Project::create([
            'project_name' => $request->project_name,
            'description' => $request->description,
            'created_by' => Auth::id(),
            'deadline' => $request->deadline,
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Proyek berhasil dibuat!');
    }
}
