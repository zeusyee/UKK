<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * Menampilkan daftar semua proyek
     */
    public function index()
    {
        $projects = Project::with(['creator', 'members'])->latest()->get();
        return view('admin.project.index', compact('projects'));
    }

    /**
     * Menampilkan form tambah proyek
     */
    public function create()
    {
        // Tidak perlu kirim status, karena default selalu planning
        return view('admin.project.create');
    }

    /**
     * Menyimpan proyek baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_name' => 'required|string|max:255',
            'description'  => 'nullable|string',
            'deadline'     => 'nullable|date',
        ]);

        Project::create([
            'project_name' => $request->project_name,
            'description'  => $request->description,
            'start_date'   => now(),
            'deadline'     => $request->deadline,
            'status'       => 'planning', // default otomatis planning
            'created_by'   => Auth::id(),
        ]);

        return redirect()
            ->route('admin.project.index')
            ->with('success', 'Proyek berhasil dibuat dengan status Planning!');
    }

    /**
     * Menampilkan form edit proyek
     */
    public function edit($project_id)
    {
        $project = Project::findOrFail($project_id);
        $statuses = ['planning', 'in_progress', 'completed', 'on_hold'];
        return view('admin.project.edit', compact('project', 'statuses'));
    }

    /**
     * Update proyek
     */
    public function update(Request $request, $project_id)
    {
        $request->validate([
            'project_name' => 'required|string|max:255',
            'description'  => 'nullable|string',
            'deadline'     => 'nullable|date',
            'status'       => 'required|in:planning,in_progress,completed,on_hold',
        ]);

        $project = Project::findOrFail($project_id);

        $project->update([
            'project_name' => $request->project_name,
            'description'  => $request->description,
            'deadline'     => $request->deadline,
            'status'       => $request->status,
        ]);

        return redirect()
            ->route('admin.project.index')
            ->with('success', 'Proyek berhasil diperbarui!');
    }

    /**
     * Hapus proyek
     */
    public function destroy($project_id)
    {
        $project = Project::findOrFail($project_id);
        $project->delete();

        return redirect()
            ->route('admin.project.index')
            ->with('success', 'Proyek berhasil dihapus!');
    }

    /**
     * Lihat progres proyek
     */
    public function progress($project_id)
    {
        $project = Project::with(['members', 'boards'])->findOrFail($project_id);
        $tasks = Card::where('project_id', $project_id)->get();

        return view('admin.project.progress', compact('project', 'tasks'));
    }

    /**
     * Laporan proyek
     */
    public function report($project_id)
    {
        $project = Project::with(['members'])->findOrFail($project_id);
        $tasks = Card::where('project_id', $project_id)->get();

        $done = $tasks->where('status', 'done')->count();
        $total = $tasks->count();

        return view('admin.project.report', compact('project', 'tasks', 'done', 'total'));
    }

    /**
     * Review hasil kerja proyek
     */
    public function review($project_id)
    {
        $project = Project::with(['members', 'boards'])->findOrFail($project_id);
        $tasks = Card::where('project_id', $project_id)->get();

        return view('admin.project.review', compact('project', 'tasks'));
    }
}


