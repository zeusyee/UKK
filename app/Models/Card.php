<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Card extends Model
{
    protected $primaryKey = 'card_id';
    
    protected $fillable = [
        'board_id',
        'card_title',
        'description',
        'position',
        'created_by',
        'assigned_to',
        'due_date',
        'status',
        'priority',
        'estimated_hours',
        'actual_hours'
    ];

    protected $casts = [
        'due_date' => 'date',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
    ];

    // Relasi ke board
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class, 'board_id', 'board_id');
    }

    // Relasi ke project melalui board
    public function project(): BelongsTo
    {
        return $this->hasOneThrough(
            Project::class,
            Board::class,
            'board_id', // Foreign key on boards table
            'project_id', // Foreign key on projects table
            'board_id', // Local key on cards table
            'project_id' // Local key on boards table
        );
    }

    // Relasi ke user yang ditugaskan
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to', 'user_id');
    }

    // Relasi ke user yang membuat card
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    // Relasi ke komentar
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'card_id', 'card_id');
    }

    // Relasi ke assignment
    public function assignments(): HasMany
    {
        return $this->hasMany(CardAssignment::class, 'card_id', 'card_id');
    }

    // Relasi ke subtasks
    public function subtasks(): HasMany
    {
        return $this->hasMany(Subtask::class, 'card_id', 'card_id');
    }

    // Relasi ke time logs
    public function timeLogs(): HasMany
    {
        return $this->hasMany(TimeLog::class, 'card_id', 'card_id');
    }

    // Relasi ke help requests
    public function helpRequests(): HasMany
    {
        return $this->hasMany(HelpRequest::class, 'card_id', 'card_id');
    }

    // Method untuk menghitung total waktu yang dihabiskan
    public function getTotalTimeSpent()
    {
        return $this->timeLogs()->sum('duration_minutes');
    }

    // Method untuk mengecek apakah card sedang dikerjakan
    public function isBeingWorkedOn()
    {
        return $this->timeLogs()
            ->whereNull('end_time')
            ->exists();
    }

    // Method untuk mengecek apakah card bisa dihapus (aturan bisnis)
    public function canBeDeleted()
    {
        // Card tidak bisa dihapus jika sudah ada assignment atau time logs
        return $this->assignments()->count() === 0 && 
               $this->timeLogs()->count() === 0 &&
               in_array($this->status, ['todo']);
    }

    // Method untuk mendapatkan progress percentage
    public function getProgressPercentage()
    {
        $totalSubtasks = $this->subtasks()->count();
        if ($totalSubtasks === 0) {
            switch ($this->status) {
                case 'todo':
                    return 0;
                case 'in_progress':
                    return 50;
                case 'review':
                    return 80;
                case 'done':
                    return 100;
                case 'blocked':
                    return 25;
                default:
                    return 0;
            }
        }

        $completedSubtasks = $this->subtasks()->where('status', 'done')->count();
        return ($completedSubtasks / $totalSubtasks) * 100;
    }

    // Scope untuk card yang sedang dikerjakan user tertentu
    public function scopeInProgressByUser($query, $userId)
    {
        return $query->where('assigned_to', $userId)
                    ->where('status', 'in_progress');
    }

    // Method untuk start/stop time tracking
    public function startWork($userId)
    {
        // Cek apakah user sudah mengerjakan card lain
        $activeTimeLog = TimeLog::where('user_id', $userId)
                                ->whereNull('end_time')
                                ->first();
        
        if ($activeTimeLog) {
            throw new \Exception('User sedang mengerjakan task lain');
        }

        // Start time tracking
        TimeLog::create([
            'card_id' => $this->card_id,
            'user_id' => $userId,
            'start_time' => now(),
            'description' => 'Mulai mengerjakan: ' . $this->card_title
        ]);

        // Update card status jika masih todo
        if ($this->status === 'todo') {
            $this->update(['status' => 'in_progress']);
        }

        // Update user status
        User::find($userId)->update(['current_task_status' => 'working']);
    }

    public function stopWork($userId)
    {
        $timeLog = TimeLog::where('card_id', $this->card_id)
                         ->where('user_id', $userId)
                         ->whereNull('end_time')
                         ->first();

        if ($timeLog) {
            $endTime = now();
            $duration = $timeLog->start_time->diffInMinutes($endTime);
            
            $timeLog->update([
                'end_time' => $endTime,
                'duration_minutes' => $duration
            ]);

            // Update actual hours pada card
            $totalMinutes = $this->getTotalTimeSpent();
            $this->update([
                'actual_hours' => round($totalMinutes / 60, 2)
            ]);

            // Update user status
            User::find($userId)->update(['current_task_status' => 'available']);
        }
    }

    // Method untuk assign card ke user
    public function assignTo($userId, $assignedBy)
    {
        // Cek apakah user sudah punya active assignment
        $activeAssignment = CardAssignment::where('user_id', $userId)
                                         ->where('assignment_status', 'assigned')
                                         ->first();
        
        if ($activeAssignment) {
            throw new \Exception('User sudah memiliki assignment aktif');
        }

        // Create assignment
        CardAssignment::create([
            'card_id' => $this->card_id,
            'user_id' => $userId,
            'assignment_status' => 'assigned'
        ]);

        // Update card
        $this->update(['assigned_to' => $userId]);

        // Create notification
        Notification::create([
            'user_id' => $userId,
            'from_user_id' => $assignedBy,
            'title' => 'Task Baru Ditugaskan',
            'message' => 'Anda mendapat assignment baru: ' . $this->card_title,
            'type' => 'task_assigned',
            'related_id' => $this->card_id
        ]);
    }
}