<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CardAssignment extends Model
{
    protected $primaryKey = 'assignment_id';

    protected $fillable = [
        'card_id',
        'user_id',
        'assignment_status',
        'assigned_at',
        'accepted_at',
        'completed_at',
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
