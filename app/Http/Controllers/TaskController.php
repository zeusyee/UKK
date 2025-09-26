<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Board;
use App\Models\Project;
use App\Models\Comment;
use App\Models\TimeLog;
use App\Models\HelpRequest;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    // Tampilkan daftar task berdasarkan role
    public function index(Request $request)
    {
        $user = Auth::user();
        $projectId = $request->get('project_id');
        
        if ($user->role === 'admin') {
            // Admin bisa lihat semua task
            $query = Card::with(['board.project', 'assignedUser', 'creator']);
            if ($projectId) {
                $query->whereHas('board', function($q) use ($projectId) {
                    $q->where('project_id', $projectId);
                });
            }
        } elseif ($user->role === 'leader') {
            // Leader bisa lihat task di project yang dia ikuti
            $query = Card::with(['board.project', 'assignedUser', 'creator'])
                        ->whereHas('board.project.members', function($q) use ($user) {
                            $q->where('user_id', $user->user_id);
                        });
            if ($projectId) {
                $query->whereHas('board', function($q) use ($projectId) {
                    $q->where('project_id', $projectId);
                });
            }
        } else {
            // User hanya bisa lihat task yang ditugaskan ke dia
            $query = Card::with(['board.project', 'assignedUser', 'creator'])
                        ->where('assigned_to', $user->user_id);
        }
        
        $tasks = $query->latest()->paginate(15);
        $projects = Project::all();
        
        return view('tasks.index', compact('tasks', 'projects', 'projectId'));
    }

    // Tampilkan detail task
    public function show($id)
    {
        $task = Card::with(['board.project', 'assignedUser', 'creator', 'comments.user', 'subtasks', 'timeLogs.user', 'helpRequests'])
                    ->findOrFail($id);
        
        // Cek permission
        $user = Auth::user();
        if (!$this->canViewTask($task, $user)) {
            abort(403, 'Tidak memiliki akses ke task ini');
        }
        
        $isWorkingOn = $task->timeLogs()
                           ->where('user_id', $user->user_id)
                           ->whereNull('end_time')
                           ->exists();
        
        return view('tasks.show', compact('task', 'isWorkingOn'));
    }

    // Form create task (hanya admin dan leader)
    public function create()
    {
        $this->authorize('create', Card::class);
        
        $user = Auth::user();
        if ($user->role === 'admin') {
            $projects = Project::with('boards')->get();
        } else {
            $projects = Project::whereHas('members', function($q) use ($user) {
                $q->where('user_id', $user->user_id);
            })->with('boards')->get();
        }
        
        return view('tasks.create', compact('projects'));
    }

    // Store task baru
    public function store(Request $request)
    {
        $this->authorize('create', Card::class);
        
        $request->validate([
            'board_id' => 'required|exists:boards,board_id',
            'card_title' => 'required|string|max:100',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,user_id',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'estimated_hours' => 'nullable|numeric|min:0'
        ]);

        $task = Card::create([
            'board_id' => $request->board_id,
            'card_title' => $request->card_title,
            'description' => $request->description,
            'created_by' => Auth::id(),
            'assigned_to' => $request->assigned_to,
            'due_date' => $request->due_date,
            'priority' => $request->priority,
            'estimated_hours' => $request->estimated_hours,
            'status' => 'todo',
            'position' => Card::where('board_id', $request->board_id)->max('position') + 1
        ]);

        // Kirim notifikasi jika ada yang ditugaskan
        if ($request->assigned_to) {
            $task->assignTo($request->assigned_to, Auth::id());
        }

        return redirect()->route('tasks.index')->with('success', 'Task berhasil dibuat!');
    }

    // Update task
    public function update(Request $request, $id)
    {
        $task = Card::findOrFail($id);
        $this->authorize('update', $task);
        
        $request->validate([
            'card_title' => 'required|string|max:100',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,user_id',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'estimated_hours' => 'nullable|numeric|min:0',
            'status' => 'required|in:todo,in_progress,review,done,blocked'
        ]);

        $oldAssigned = $task->assigned_to;
        $task->update($request->all());

        // Jika assigned_to berubah, kirim notifikasi
        if ($oldAssigned != $request->assigned_to && $request->assigned_to) {
            Notification::create([
                'user_id' => $request->assigned_to,
                'from_user_id' => Auth::id(),
                'title' => 'Task Ditugaskan Ulang',
                'message' => 'Task "' . $task->card_title . '" telah ditugaskan kepada Anda',
                'type' => 'task_assigned',
                'related_id' => $task->card_id
            ]);
        }

        return redirect()->route('tasks.show', $task->card_id)->with('success', 'Task berhasil diupdate!');
    }

    // Delete task (hanya jika belum ada progress)
    public function destroy($id)
    {
        $task = Card::findOrFail($id);
        $this->authorize('delete', $task);
        
        if (!$task->canBeDeleted()) {
            return redirect()->back()->with('error', 'Task tidak dapat dihapus karena sudah ada progress!');
        }

        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Task berhasil dihapus!');
    }

    // Start/Stop time tracking
    public function toggleTimeTracking($id)
    {
        $task = Card::findOrFail($id);
        $user = Auth::user();
        
        // Cek apakah user adalah assignee
        if ($task->assigned_to != $user->user_id) {
            return response()->json(['error' => 'Anda tidak ditugaskan untuk task ini'], 403);
        }

        try {
            $activeLog = $task->timeLogs()
                             ->where('user_id', $user->user_id)
                             ->whereNull('end_time')
                             ->first();

            if ($activeLog) {
                // Stop tracking
                $task->stopWork($user->user_id);
                $message = 'Time tracking dihentikan';
                $isTracking = false;
            } else {
                // Start tracking
                $task->startWork($user->user_id);
                $message = 'Time tracking dimulai';
                $isTracking = true;
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'is_tracking' => $isTracking
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    // Add comment
    public function addComment(Request $request, $id)
    {
        $request->validate([
            'comment_text' => 'required|string',
            'comment_type' => 'required|in:update,feedback'
        ]);

        $task = Card::findOrFail($id);
        
        // Cek permission
        if (!$this->canViewTask($task, Auth::user())) {
            abort(403, 'Tidak memiliki akses ke task ini');
        }

        Comment::create([
            'card_id' => $id,
            'user_id' => Auth::id(),
            'comment_text' => $request->comment_text,
            'comment_type' => $request->comment_type
        ]);

        return redirect()->route('tasks.show', $id)->with('success', 'Komentar berhasil ditambahkan!');
    }

    // Request help (untuk developer/designer)
    public function requestHelp(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent'
        ]);

        $task = Card::findOrFail($id);
        
        // Cek apakah user adalah assignee
        if ($task->assigned_to != Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat meminta bantuan untuk task yang bukan milik Anda!');
        }

        HelpRequest::create([
            'card_id' => $id,
            'requested_by' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority
        ]);

        return redirect()->route('tasks.show', $id)->with('success', 'Permintaan bantuan berhasil dikirim!');
    }

    // Change task status
    public function changeStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:todo,in_progress,review,done,blocked'
        ]);

        $task = Card::findOrFail($id);
        $user = Auth::user();
        
        // Cek permission untuk mengubah status
        if (!$this->canChangeStatus($task, $user)) {
            return response()->json(['error' => 'Tidak memiliki permission untuk mengubah status'], 403);
        }

        $oldStatus = $task->status;
        $task->update(['status' => $request->status]);

        // Kirim notifikasi jika task completed
        if ($request->status === 'done' && $oldStatus !== 'done') {
            // Notifikasi ke creator dan team lead
            $notifyUsers = collect([$task->created_by]);
            
            // Tambahkan team lead dalam project
            $teamLeads = $task->board->project->members()
                            ->whereHas('user', function($q) {
                                $q->where('role', 'leader');
                            })->pluck('user_id');
            
            $notifyUsers = $notifyUsers->merge($teamLeads)->unique()->filter();
            
            foreach ($notifyUsers as $userId) {
                if ($userId != $user->user_id) {
                    Notification::create([
                        'user_id' => $userId,
                        'from_user_id' => $user->user_id,
                        'title' => 'Task Selesai',
                        'message' => 'Task "' . $task->card_title . '" telah diselesaikan',
                        'type' => 'task_completed',
                        'related_id' => $task->card_id
                    ]);
                }
            }
        }

        return response()->json(['success' => true, 'message' => 'Status berhasil diubah']);
    }

    // Helper methods
    private function canViewTask($task, $user)
    {
        if ($user->role === 'admin') return true;
        
        if ($user->role === 'leader') {
            // Leader bisa lihat task di project yang dia ikuti
            return $task->board->project->members()
                       ->where('user_id', $user->user_id)
                       ->exists();
        }
        
        // User hanya bisa lihat task yang ditugaskan ke dia atau yang dia buat
        return $task->assigned_to == $user->user_id || $task->created_by == $user->user_id;
    }

    private function canChangeStatus($task, $user)
    {
        if ($user->role === 'admin') return true;
        if ($user->role === 'leader') return true;
        
        // User hanya bisa mengubah status task yang ditugaskan ke dia
        return $task->assigned_to == $user->user_id;
    }
}