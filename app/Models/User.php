<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'username',
        'full_name',
        'email',
        'password',
        'role',
        'current_task_status',
    ];

    protected $hidden = ['password', 'remember_token'];

    // Relasi
    public function projectsCreated()
    {
        return $this->hasMany(Project::class, 'created_by');
    }

    public function cardsCreated()
    {
        return $this->hasMany(Card::class, 'created_by');
    }

    public function cardsAssigned()
    {
        return $this->hasMany(Card::class, 'assigned_to');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }
}
