<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subtask extends Model
{
    protected $primaryKey = 'subtask_id';

    protected $fillable = [
        'card_id',
        'title',
        'description',
        'status',
        'position',
    ];

    public function card()
    {
        return $this->belongsTo(Card::class, 'card_id');
    }
}
