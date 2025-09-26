@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            {{-- Task Detail --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ $task->card_title }}</h4>
                    <div>
                        <span class="badge badge-{{ $task->priority === 'urgent' ? 'danger' : ($task->priority === 'high' ? 'warning' : 'info') }} mr-2">
                            {{ strtoupper($task->priority) }}
                        </span>
                        <span class="badge badge-{{ $task->status === 'done' ? 'success' : ($task->status === 'in_progress' ? 'primary' : 'secondary') }}">
                            {{ str_replace('_', ' ', strtoupper($task->status)) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Project:</strong> {{ $task->board->project->project_name }}<br>
                            <strong>Board:</strong> {{ $task->board->board_name }}<br>
                            <strong>Created by:</strong> {{ $task->creator->full_name ?? $task->creator->username }}<br>
                            <strong>Assigned to:</strong> 
                            @if($task->assignedUser)
                                {{ $task->assignedUser->full_name ?? $task->assignedUser->username }}
                            @else
                                <span class="text-muted">Unassigned</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <strong>Due Date:</strong> {{ $task->due_date ? $task->due_date->format('d M Y') : 'No deadline' }}<br>
                            <strong>Estimated Hours:</strong> {{ $task->estimated_hours ?? 'Not set' }}<br>
                            <strong>Actual Hours:</strong> {{ $task->actual_hours ?? '0' }}<br>
                            <strong>Created:</strong> {{ $task->created_at->format('d M Y H:i') }}
                        </div>
                    </div>

                    @if($task->description)
                    <div class="mb-3">
                        <strong>Description:</strong>
                        <div class="mt-2 p-3 bg-light rounded">
                            {!! nl2br(e($task->description)) !!}
                        </div>
                    </div>
                    @endif

                    {{-- Time Tracking --}}
                    @if($task->assigned_to === Auth::id())
                    <div class="mb-3">
                        <strong>Time Tracking:</strong>
                        <div class="mt-2">
                            @if($isWorkingOn)
                            <button class="btn btn-danger" onclick="toggleTimeTracking({{ $task->card_id }})">
                                <i class="fas fa-stop"></i> Stop Working
                            </button>
                            <span class="ml-2 text-success">
                                <i class="fas fa-play"></i> Currently working on this task
                            </span>
                            @else
                            <button class="btn btn-success" onclick="toggleTimeTracking({{ $task->card_id }})">
                                <i class="fas fa-play"></i> Start Working
                            </button>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Progress Bar --}}
                    <div class="mb-3">
                        <strong>Progress:</strong>
                        <div class="progress mt-2">
                            <div class="progress-bar" role="progressbar" style="width: {{ $task->getProgressPercentage() }}%">
                                {{ number_format($task->getProgressPercentage(), 1) }}%
                            </div>
                        </div>
                    </div>

                    {{-- Status Change (for assigned user or leader/admin) --}}
                    @if($task->assigned_to === Auth::id() || Auth::user()->role === 'leader' || Auth::user()->role === 'admin')
                    <div class="mb-3">
                        <strong>Change Status:</strong>
                        <div class="mt-2">
                            <select class="form-select" id="statusSelect" onchange="changeStatus({{ $task->card_id }})">
                                <option value="todo" {{ $task->status === 'todo' ? 'selected' : '' }}>To Do</option>
                                <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="review" {{ $task->status === 'review' ? 'selected' : '' }}>Review</option>
                                <option value="done" {{ $task->status === 'done' ? 'selected' : '' }}>Done</option>
                                <option value="blocked" {{ $task->status === 'blocked' ? 'selected' : '' }}>Blocked</option>
                            </select>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Subtasks --}}
            @if($task->subtasks->count() > 0)
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Subtasks</h5>
                </div>
                <div class="card-body">
                    @foreach($task->subtasks as $subtask)
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                        <div>
                            <strong>{{ $subtask->subtask_title }}</strong>
                            @if($subtask->description)
                                <br><small class="text-muted">{{ $subtask->description }}</small>
                            @endif
                        </div>
                        <span class="badge badge-{{ $subtask->status === 'done' ? 'success' : ($subtask->status === 'in_progress' ? 'primary' : 'secondary') }}">
                            {{ str_replace('_', ' ', strtoupper($subtask->status)) }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Time Logs --}}
            @if($task->timeLogs->count() > 0)
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Time Logs</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Duration</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($task->timeLogs->sortByDesc('start_time') as $log)
                                <tr>
                                    <td>{{ $log->user->full_name ?? $log->user->username }}</td>
                                    <td>{{ $log->start_time->format('d/m/Y H:i') }}</td>
                                    <td>{{ $log->end_time ? $log->end_time->format('d/m/Y H:i') : 'Working...' }}</td>
                                    <td>
                                        @if($log->duration_minutes)
                                            {{ floor($log->duration_minutes / 60) }}h {{ $log->duration_minutes % 60 }}m
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $log->description }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-2">
                        <strong>Total Time: {{ floor($task->getTotalTimeSpent() / 60) }}h {{ $task->getTotalTimeSpent() % 60 }}m</strong>
                    </div>
                </div>
            </div>
            @endif

            {{-- Comments --}}
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Comments</h5>
                </div>
                <div class="card-body">
                    {{-- Add Comment Form --}}
                    <form action="{{ route('tasks.add-comment', $task->card_id) }}" method="POST" class="mb-4">
                        @csrf
                        <div class="mb-3">
                            <textarea name="comment_text" class="form-control" rows="3" 
                                      placeholder="Add a comment..." required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <select name="comment_type" class="form-select" required>
                                    <option value="update">Update</option>
                                    <option value="feedback">Feedback</option>
                                </select>
                            </div>
                            <div class="col-md-6 text-end">
                                <button type="submit" class="btn btn-primary">Add Comment</button>
                            </div>
                        </div>
                    </form>

                    {{-- Comments List --}}
                    @foreach($task->comments->sortByDesc('created_at') as $comment)
                    <div class="border-bottom mb-3 pb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>{{ $comment->user->full_name ?? $comment->user->username }}</strong>
                                <span class="badge badge-{{ $comment->comment_type === 'feedback' ? 'warning' : 'info' }} ml-2">
                                    {{ ucfirst($comment->comment_type) }}
                                </span>
                            </div>
                            <small class="text-muted">{{ $comment->created_at->format('d M Y H:i') }}</small>
                        </div>
                        <div class="mt-2">
                            {!! nl2br(e($comment->comment_text)) !!}
                        </div>
                    </div>
                    @endforeach

                    @if($task->comments->count() === 0)
                    <p class="text-muted text-center">No comments yet</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            {{-- Request Help (for assigned user) --}}
            @if($task->assigned_to === Auth::id())
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Need Help?</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('tasks.request-help', $task->card_id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select" required>
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="fas fa-question-circle"></i> Request Help
                        </button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Help Requests --}}
            @if($task->helpRequests->count() > 0)
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Help Requests</h5>
                </div>
                <div class="card-body">
                    @foreach($task->helpRequests->sortByDesc('created_at') as $helpRequest)
                    <div class="border-bottom mb-3 pb-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <strong>{{ $helpRequest->title }}</strong>
                            <span class="badge badge-{{ $helpRequest->status === 'resolved' ? 'success' : ($helpRequest->status === 'in_progress' ? 'primary' : 'warning') }}">
                                {{ ucfirst($helpRequest->status) }}
                            </span>
                        </div>
                        <small class="text-muted d-block">
                            by {{ $helpRequest->requester->full_name ?? $helpRequest->requester->username }}
                            • {{ $helpRequest->created_at->format('d M Y H:i') }}
                        </small>
                        <p class="mt-2 mb-0">{{ $helpRequest->description }}</p>
                        @if($helpRequest->resolved_at)
                        <small class="text-success">
                            Resolved {{ $helpRequest->resolved_at->format('d M Y H:i') }}
                            by {{ $helpRequest->resolver->full_name ?? $helpRequest->resolver->username }}
                        </small>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Task Actions --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Tasks
                        </a>
                        
                        @if(Auth::user()->role === 'admin' || Auth::user()->role === 'leader')
                        <a href="{{ route('tasks.edit', $task->card_id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Task
                        </a>
                        @endif

                        @if($task->canBeDeleted() && (Auth::user()->role === 'admin' || $task->created_by === Auth::id()))
                        <form action="{{ route('tasks.destroy', $task->card_id) }}" method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this task?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash"></i> Delete Task
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleTimeTracking(taskId) {
    fetch(`/tasks/${taskId}/toggle-time-tracking`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'An error occurred');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}

function changeStatus(taskId) {
    const status = document.getElementById('statusSelect').value;
    
    fetch(`/tasks/${taskId}/change-status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'An error occurred');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}
</script>
@endsection

{{-- resources/views/tasks/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Task Management</h1>
                @if(Auth::user()->role === 'admin' || Auth::user()->role === 'leader')
                <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create New Task
                </a>
                @endif
            </div>

            {{-- Filter --}}
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <select name="project_id" class="form-select">
                                <option value="">All Projects</option>
                                @foreach($projects as $project)
                                <option value="{{ $project->project_id }}" {{ $projectId == $project->project_id ? 'selected' : '' }}>
                                    {{ $project->project_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="todo" {{ request('status') === 'todo' ? 'selected' : '' }}>To Do</option>
                                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="review" {{ request('status') === 'review' ? 'selected' : '' }}>Review</option>
                                <option value="done" {{ request('status') === 'done' ? 'selected' : '' }}>Done</option>
                                <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="priority" class="form-select">
                                <option value="">All Priority</option>
                                <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                                <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('tasks.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tasks Table --}}
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Project</th>
                                    <th>Assigned To</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                    <th>Due Date</th>
                                    <th>Time Spent</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tasks as $task)
                                <tr>
                                    <td>
                                        <strong>{{ $task->card_title }}</strong>
                                        @if($task->description)
                                            <br><small class="text-muted">{{ Str::limit($task->description, 50) }}</small>
                                        @endif
                                        @if($task->isBeingWorkedOn())
                                            <br><small class="text-success"><i class="fas fa-play"></i> Currently being worked on</small>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $task->board->project->project_name }}</strong>
                                        <br><small class="text-muted">{{ $task->board->board_name }}</small>
                                    </td>
                                    <td>
                                        @if($task->assignedUser)
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    {{ $task->assignedUser->full_name ?? $task->assignedUser->username }}
                                                    <br><small class="badge badge-{{ $task->assignedUser->current_task_status === 'working' ? 'success' : 'secondary' }}">
                                                        {{ $task->assignedUser->current_task_status }}
                                                    </small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">Unassigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $task->priority === 'urgent' ? 'danger' : ($task->priority === 'high' ? 'warning' : ($task->priority === 'medium' ? 'info' : 'secondary')) }}">
                                            {{ strtoupper($task->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $task->status === 'done' ? 'success' : ($task->status === 'in_progress' ? 'primary' : ($task->status === 'blocked' ? 'danger' : 'secondary')) }}">
                                            {{ str_replace('_', ' ', strtoupper($task->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: {{ $task->getProgressPercentage() }}%">
                                                {{ number_format($task->getProgressPercentage(), 0) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($task->due_date)
                                            {{ $task->due_date->format('d M Y') }}
                                            @if($task->due_date->isPast() && $task->status !== 'done')
                                                <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Overdue</small>
                                            @endif
                                        @else
                                            <span class="text-muted">No deadline</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $totalMinutes = $task->getTotalTimeSpent();
                                            $hours = floor($totalMinutes / 60);
                                            $minutes = $totalMinutes % 60;
                                        @endphp
                                        {{ $hours }}h {{ $minutes }}m
                                        @if($task->estimated_hours)
                                            <br><small class="text-muted">Est: {{ $task->estimated_hours }}h</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group-vertical btn-group-sm">
                                            <a href="{{ route('tasks.show', $task->card_id) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            @if(Auth::user()->role === 'admin' || Auth::user()->role === 'leader')
                                            <a href="{{ route('tasks.edit', $task->card_id) }}" class="btn btn-outline-secondary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">No tasks found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Pagination --}}
                    {{ $tasks->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection