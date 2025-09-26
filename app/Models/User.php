<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $timestamps = false; // karena pakai created_at tanpa updated_at

    protected $fillable = [
        'username',
        'password',
        'full_name',
        'email',
        'current_task_status',
        'role', // tambahkan role
    ];

    protected $hidden = [
        'password',
    ];
}
