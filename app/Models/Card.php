<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'actual_hours',
    ];

    public function board()
    {
        return $this->belongsTo(Board::class, 'board_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'card_id');
    }

    public function subtasks()
    {
        return $this->hasMany(Subtask::class, 'card_id');
    }

    public function timeLogs()
    {
        return $this->hasMany(TimeLog::class, 'card_id');
    }
}
