<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HelpRequest extends Model
{
    protected $primaryKey = 'help_request_id';

    protected $fillable = [
        'card_id',
        'requester_id',
        'team_lead_id',
        'subject',
        'message',
        'status',
        'priority',
    ];

    public function card()
    {
        return $this->belongsTo(Card::class, 'card_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function teamLead()
    {
        return $this->belongsTo(User::class, 'team_lead_id');
    }
}
