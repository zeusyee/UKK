<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeLog extends Model
{
    protected $primaryKey = 'time_log_id';

    protected $fillable = [
        'card_id',
        'user_id',
        'start_time',
        'end_time',
        'duration_minutes',
        'description',
    ];

    public function card()
    {
        return $this->belongsTo(Card::class, 'card_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
