<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Card;
use App\Models\Board;
use App\Models\User;
use App\Models\HelpRequest;
use App\Models\TimeLog;
use App\Models\Comment;
use App\Models\Notification;

class LeaderController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        
        // Get projects where user is leader
        $projectIds = ProjectMember::where('user_id', $user->user_id)
                                 ->where('role', 'leader')
                                 ->pluck('project_id');
        
        $projects = Project::whereIn('project_id', $projectIds)->get();
        
        // Get team members
        $teamMemberIds = ProjectMember::whereIn('project_id', $projectIds)
                                    ->where('user_id', '!=', $user->user_id)
                                    ->pluck('user_id');
        
        $teamMembers = User::whereIn('user_id', $teamMemberIds)->get();
        
        // Get tasks statistics
        $boardIds = Board::whereIn('project_id', $projectIds)->pluck('board_id');
        
        $totalTasks = Card::whereIn('board_id', $boardIds)->count();
        $completedTasks = Card::whereIn('board_id', $boardIds)->where('status', 'done')->count();
        $inProgressTasks = Card::whereIn('board_id', $boardIds)->where('status', 'in_progress')->count();
        $blockedTasks = Card::whereIn('board_id', $boardIds)->where('status', 'blocked')->count();
        
        // Get recent activities
        $recentTasks = Card::whereIn('board_id', $boardIds)
                          ->with(['assignee', 'board.project'])
                          ->latest()
                          ->limit(5)
                          ->get();
        
        // Get pending help requests
        $helpRequests = HelpRequest::whereIn('card_id', function($query) use ($boardIds) {
            $query->select('card_id')
                  ->from('cards')
                  ->whereIn('board_id', $boardIds);
        })
        ->where('status', 'pending')
        ->with(['requester', 'card'])
        ->latest()
        ->get();
        
        // Get team performance data
        $teamPerformance = [];
        foreach ($teamMembers as $member) {
            $memberTasks = Card::whereIn('board_id', $boardIds)
                              ->where('assigned_to', $member->user_id)
                              ->get();
                              
            $completedCount = $memberTasks->where('status', 'done')->count();
            $totalCount = $memberTasks->count();
            $totalTimeSpent = TimeLog::where('user_id', $member->user_id)
                                   ->whereIn('card_id', $memberTasks->pluck('card_id'))
                                   ->sum('duration_minutes');
                                   
            $teamPerformance[] = [
                'user' => $member,
                'total_tasks' => $totalCount,
                'completed_tasks' => $completedCount,
                'completion_rate' => $totalCount > 0 ? round(($completedCount / $totalCount) * 100, 1) : 0,
                'total_time_hours' => round($totalTimeSpent / 60, 1),
                'current_status' => $member->current_task_status
            ];
        }

        return view('leader.dashboard', compact(
            'projects', 
            'teamMembers', 
            'totalTasks', 
            'completedTasks', 
            'inProgressTasks', 
            'blockedTasks',
            'recentTasks',
            'helpRequests',
            'teamPerformance'
        ));
    }

    public function tasks()
    {
        $user = Auth::user();
        
        // Get projects where user is leader
        $projectIds = ProjectMember::where('user_id', $user->user_id)
                                 ->where('role', 'leader')
                                 ->pluck('project_id');
        
        $boardIds = Board::whereIn('project_id', $projectIds)->pluck('board_id');
        
        $tasks = Card::whereIn('board_id', $boardIds)
                    ->with(['assignee', 'board.project', 'creator'])
                    ->latest()
                    ->paginate(20);
                    
        $projects = Project::whereIn('project_id', $projectIds)->get();
        
        return view('leader.tasks.index', compact('tasks', 'projects'));
    }

    public function createTask()
    {
        $user = Auth::user();
        
        // Get projects where user is leader
        $projectIds = ProjectMember::where('user_id', $user->user_id)
                                 ->where('role', 'leader')
                                 ->pluck('project_id');
        
        $projects = Project::with('boards')->whereIn('project_id', $projectIds)->get();
        
        // Get team members
        $teamMemberIds = ProjectMember::whereIn('project_id', $projectIds)
                                    ->pluck('user_id');
        
        $users = User::whereIn('user_id', $teamMemberIds)->get();
        
        return view('leader.tasks.create', compact('projects', 'users'));
    }

    public function storeTask(Request $request)
    {
        $request->validate([
            'board_id' => 'required|exists:boards,board_id',
            'card_title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,user_id',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'estimated_hours' => 'nullable|numeric|min:0',
        ]);

        // Check if assigned user is already working on another task
        if ($request->assigned_to) {
            $hasActiveTask = Card::where('assigned_to', $request->assigned_to)
                               ->whereIn('status', ['in_progress'])
                               ->exists();
            
            if ($hasActiveTask) {
                return back()->withErrors(['assigned_to' => 'User is already working on another task.']);
            }
        }

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
        ]);

        // Send notification to assigned user
        if ($request->assigned_to) {
            Notification::create([
                'user_id' => $request->assigned_to,
                'from_user_id' => Auth::id(),
                'title' => 'New Task Assigned',
                'message' => "You have been assigned a new task: {$task->card_title}",
                'type' => 'task_assigned',
                'related_id' => $task->card_id,
            ]);
        }

        return redirect()->route('leader.tasks')->with('success', 'Task created successfully!');
    }

    public function showTask($id)
    {
        $task = Card::with(['board.project', 'assignee', 'creator', 'comments.user', 'timeLogs.user', 'subtasks'])
                   ->findOrFail($id);
                   
        // Check if leader has access to this task
        $hasAccess = ProjectMember::where('user_id', Auth::id())
                                 ->where('project_id', $task->board->project_id)
                                 ->where('role', 'leader')
                                 ->exists();
                                 
        if (!$hasAccess) {
            abort(403);
        }

        $isWorkingOn = TimeLog::where('card_id', $task->card_id)
                             ->where('user_id', Auth::id())
                             ->whereNull('end_time')
                             ->exists();

        return view('leader.tasks.show', compact('task', 'isWorkingOn'));
    }

    public function editTask($id)
    {
        $task = Card::with(['board.project'])->findOrFail($id);
        
        // Check if leader has access to this task
        $hasAccess = ProjectMember::where('user_id', Auth::id())
                                 ->where('project_id', $task->board->project_id)
                                 ->where('role', 'leader')
                                 ->exists();
                                 
        if (!$hasAccess) {
            abort(403);
        }

        $user = Auth::user();
        $projectIds = ProjectMember::where('user_id', $user->user_id)
                                 ->where('role', 'leader')
                                 ->pluck('project_id');
        
        $projects = Project::with('boards')->whereIn('project_id', $projectIds)->get();
        
        $teamMemberIds = ProjectMember::whereIn('project_id', $projectIds)
                                    ->pluck('user_id');
        
        $users = User::whereIn('user_id', $teamMemberIds)->get();
        
        return view('leader.tasks.edit', compact('task', 'projects', 'users'));
    }

    public function updateTask(Request $request, $id)
    {
        $task = Card::findOrFail($id);
        
        // Check if leader has access to this task
        $hasAccess = ProjectMember::where('user_id', Auth::id())
                                 ->where('project_id', $task->board->project_id)
                                 ->where('role', 'leader')
                                 ->exists();
                                 
        if (!$hasAccess) {
            abort(403);
        }

        $request->validate([
            'board_id' => 'required|exists:boards,board_id',
            'card_title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,user_id',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'estimated_hours' => 'nullable|numeric|min:0',
            'status' => 'required|in:todo,in_progress,review,done,blocked',
        ]);

        // Check if assigned user is already working on another task (only if changing assignment)
        if ($request->assigned_to && $request->assigned_to != $task->assigned_to) {
            $hasActiveTask = Card::where('assigned_to', $request->assigned_to)
                               ->whereIn('status', ['in_progress'])
                               ->where('card_id', '!=', $task->card_id)
                               ->exists();
            
            if ($hasActiveTask) {
                return back()->withErrors(['assigned_to' => 'User is already working on another task.']);
            }
        }

        $oldAssignedTo = $task->assigned_to;
        
        $task->update([
            'board_id' => $request->board_id,
            'card_title' => $request->card_title,
            'description' => $request->description,
            'assigned_to' => $request->assigned_to,
            'due_date' => $request->due_date,
            'priority' => $request->priority,
            'estimated_hours' => $request->estimated_hours,
            'status' => $request->status,
        ]);

        // Send notification if assignment changed
        if ($request->assigned_to && $request->assigned_to != $oldAssignedTo) {
            Notification::create([
                'user_id' => $request->assigned_to,
                'from_user_id' => Auth::id(),
                'title' => 'Task Reassigned',
                'message' => "You have been assigned to task: {$task->card_title}",
                'type' => 'task_assigned',
                'related_id' => $task->card_id,
            ]);
        }

        return redirect()->route('leader.task.show', $task->card_id)
                        ->with('success', 'Task updated successfully!');
    }

    public function helpRequests()
    {
        $user = Auth::user();
        
        // Get projects where user is leader
        $projectIds = ProjectMember::where('user_id', $user->user_id)
                                 ->where('role', 'leader')
                                 ->pluck('project_id');
        
        $boardIds = Board::whereIn('project_id', $projectIds)->pluck('board_id');
        
        $helpRequests = HelpRequest::whereIn('card_id', function($query) use ($boardIds) {
            $query->select('card_id')
                  ->from('cards')
                  ->whereIn('board_id', $boardIds);
        })
        ->with(['requester', 'card.board.project'])
        ->orderBy('priority', 'desc')
        ->orderBy('created_at', 'desc')
        ->paginate(15);

        return view('leader.help-requests.index', compact('helpRequests'));
    }

    public function showHelpRequest($id)
    {
        $helpRequest = HelpRequest::with(['requester', 'card.board.project', 'teamLead'])
                                 ->findOrFail($id);
                                 
        // Check if leader has access to this help request
        $hasAccess = ProjectMember::where('user_id', Auth::id())
                                 ->where('project_id', $helpRequest->card->board->project_id)
                                 ->where('role', 'leader')
                                 ->exists();
                                 
        if (!$hasAccess) {
            abort(403);
        }

        return view('leader.help-requests.show', compact('helpRequest'));
    }

    public function updateHelpRequestStatus(Request $request, $id)
    {
        $helpRequest = HelpRequest::findOrFail($id);
        
        // Check if leader has access to this help request
        $hasAccess = ProjectMember::where('user_id', Auth::id())
                                 ->where('project_id', $helpRequest->card->board->project_id)
                                 ->where('role', 'leader')
                                 ->exists();
                                 
        if (!$hasAccess) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:pending,in_progress,resolved,closed',
            'response' => 'nullable|string',
        ]);

        $helpRequest->update([
            'status' => $request->status,
            'team_lead_id' => Auth::id(),
        ]);

        // Add comment if response provided
        if ($request->response) {
            Comment::create([
                'card_id' => $helpRequest->card_id,
                'user_id' => Auth::id(),
                'content' => "Response to help request: " . $request->response,
            ]);
        }

        // Send notification to requester
        Notification::create([
            'user_id' => $helpRequest->requester_id,
            'from_user_id' => Auth::id(),
            'title' => 'Help Request Updated',
            'message' => "Your help request has been updated to: {$request->status}",
            'type' => 'help_request',
            'related_id' => $helpRequest->help_request_id,
        ]);

        return redirect()->route('leader.help-requests.show', $helpRequest->help_request_id)
                        ->with('success', 'Help request updated successfully!');
    }

    public function reports()
    {
        $user = Auth::user();
        
        // Get projects where user is leader
        $projectIds = ProjectMember::where('user_id', $user->user_id)
                                 ->where('role', 'leader')
                                 ->pluck('project_id');
        
        $projects = Project::whereIn('project_id', $projectIds)->get();
        
        return view('leader.reports.index', compact('projects'));
    }

    public function generateReport(Request $request)
    {
        $request->validate([
            'project_id' => 'nullable|exists:projects,project_id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'report_type' => 'required|in:project_summary,team_performance,task_details',
        ]);

        $user = Auth::user();
        $projectIds = ProjectMember::where('user_id', $user->user_id)
                                 ->where('role', 'leader')
                                 ->pluck('project_id');

        // Filter by specific project if selected
        if ($request->project_id) {
            $projectIds = $projectIds->filter(function($id) use ($request) {
                return $id == $request->project_id;
            });
        }

        $projects = Project::whereIn('project_id', $projectIds)->get();
        $boardIds = Board::whereIn('project_id', $projectIds)->pluck('board_id');
        
        // Apply date filters
        $tasksQuery = Card::whereIn('board_id', $boardIds);
        if ($request->date_from) {
            $tasksQuery->where('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $tasksQuery->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $tasks = $tasksQuery->with(['assignee', 'board.project', 'timeLogs'])->get();
        
        // Generate report data based on type
        $reportData = [];
        
        switch ($request->report_type) {
            case 'project_summary':
                foreach ($projects as $project) {
                    $projectTasks = $tasks->where('board.project_id', $project->project_id);
                    $reportData[] = [
                        'project' => $project,
                        'total_tasks' => $projectTasks->count(),
                        'completed_tasks' => $projectTasks->where('status', 'done')->count(),
                        'in_progress_tasks' => $projectTasks->where('status', 'in_progress')->count(),
                        'blocked_tasks' => $projectTasks->where('status', 'blocked')->count(),
                        'total_time_spent' => $projectTasks->sum(function($task) {
                            return $task->timeLogs->sum('duration_minutes');
                        }),
                    ];
                }
                break;
                
            case 'team_performance':
                $teamMemberIds = ProjectMember::whereIn('project_id', $projectIds)->pluck('user_id');
                $teamMembers = User::whereIn('user_id', $teamMemberIds)->get();
                
                foreach ($teamMembers as $member) {
                    $memberTasks = $tasks->where('assigned_to', $member->user_id);
                    $reportData[] = [
                        'user' => $member,
                        'total_tasks' => $memberTasks->count(),
                        'completed_tasks' => $memberTasks->where('status', 'done')->count(),
                        'in_progress_tasks' => $memberTasks->where('status', 'in_progress')->count(),
                        'total_time_spent' => $memberTasks->sum(function($task) {
                            return $task->timeLogs->where('user_id', $member->user_id)->sum('duration_minutes');
                        }),
                        'avg_completion_time' => $this->calculateAvgCompletionTime($memberTasks->where('status', 'done')),
                    ];
                }
                break;
                
            case 'task_details':
                $reportData = $tasks->map(function($task) {
                    return [
                        'task' => $task,
                        'time_spent' => $task->timeLogs->sum('duration_minutes'),
                        'completion_percentage' => $this->calculateTaskCompletion($task),
                    ];
                });
                break;
        }

        return view('leader.reports.show', compact('reportData', 'request'));
    }

    private function calculateAvgCompletionTime($completedTasks)
    {
        if ($completedTasks->isEmpty()) return 0;
        
        $totalTime = 0;
        $count = 0;
        
        foreach ($completedTasks as $task) {
            $timeSpent = $task->timeLogs->sum('duration_minutes');
            if ($timeSpent > 0) {
                $totalTime += $timeSpent;
                $count++;
            }
        }
        
        return $count > 0 ? round($totalTime / $count, 2) : 0;
    }

    private function calculateTaskCompletion($task)
    {
        switch ($task->status) {
            case 'done':
                return 100;
            case 'review':
                return 90;
            case 'in_progress':
                return 50;
            case 'todo':
                return 0;
            case 'blocked':
                return 25;
            default:
                return 0;
        }
    }
}