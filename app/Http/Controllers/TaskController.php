<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Card;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    // List tugas untuk team lead (hanya proyek yang dipimpin)
    public function index()
    {
        $user = Auth::user();
        $projects = Project::where('created_by', $user->user_id)->get();
        $tasks = Card::whereIn('project_id', $projects->pluck('project_id'))->get();
        return view('teamlead.task.index', compact('tasks', 'projects'));
    }

    // Form create tugas
    public function create()
    {
        $user = Auth::user();
        $projects = Project::where('created_by', $user->user_id)->get();
        $members = User::where('role', '!=', 'admin')->get();
        return view('teamlead.task.create', compact('projects', 'members'));
    }

    // Simpan tugas baru
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,project_id',
            'title' => 'required',
            'assigned_to' => 'required|exists:users,user_id',
            'priority' => 'required',
            'status' => 'required',
        ]);
        // Aturan bisnis: 1 user hanya bisa mengerjakan 1 tugas aktif
        $activeTask = Card::where('assigned_to', $request->assigned_to)
            ->where('status', '!=', 'done')->first();
        if ($activeTask) {
            return back()->withErrors(['assigned_to' => 'User ini masih memiliki tugas aktif!']);
        }
        Card::create($request->all());
        return redirect()->route('teamlead.task.index')->with('success', 'Tugas berhasil dibuat!');
    }

    // Edit tugas
    public function edit($id)
    {
        $task = Card::findOrFail($id);
        $user = Auth::user();
        $projects = Project::where('created_by', $user->user_id)->get();
        $members = User::where('role', '!=', 'admin')->get();
        return view('teamlead.task.edit', compact('task', 'projects', 'members'));
    }

    // Update tugas
    public function update(Request $request, $id)
    {
        $task = Card::findOrFail($id);
        $request->validate([
            'project_id' => 'required|exists:projects,project_id',
            'title' => 'required',
            'assigned_to' => 'required|exists:users,user_id',
            'priority' => 'required',
            'status' => 'required',
        ]);
        // Aturan bisnis: 1 user hanya bisa mengerjakan 1 tugas aktif
        $activeTask = Card::where('assigned_to', $request->assigned_to)
            ->where('status', '!=', 'done')
            ->where('card_id', '!=', $id)
            ->first();
        if ($activeTask) {
            return back()->withErrors(['assigned_to' => 'User ini masih memiliki tugas aktif!']);
        }
        $task->update($request->all());
        return redirect()->route('teamlead.task.index')->with('success', 'Tugas berhasil diupdate!');
    }

    // Hapus tugas (tidak bisa hapus jika sudah dikerjakan)
    public function destroy($id)
    {
        $task = Card::findOrFail($id);
        if ($task->status === 'done') {
            return back()->withErrors(['delete' => 'Tugas yang sudah selesai tidak bisa dihapus!']);
        }
        $task->delete();
        return redirect()->route('teamlead.task.index')->with('success', 'Tugas berhasil dihapus!');
    }

    // Update status tugas
    public function updateStatus(Request $request, $id)
    {
        $task = Card::findOrFail($id);
        $request->validate([
            'status' => 'required',
        ]);
        $task->status = $request->status;
        $task->save();
        return back()->with('success', 'Status tugas diperbarui!');
    }

    // Tinjau hasil kerja anggota
    public function review($id)
    {
        $task = Card::with('user')->findOrFail($id);
        return view('teamlead.task.review', compact('task'));
    }

    // Fitur pemecahan masalah/blokir
    public function unblock($id)
    {
        $task = Card::findOrFail($id);
        $task->is_blocked = false;
        $task->save();
        return back()->with('success', 'Tugas tidak lagi diblokir!');
    }
}
