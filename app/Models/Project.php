<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $primaryKey = 'project_id';

    protected $fillable = [
        'project_name',
        'description',
        'start_date',
        'deadline', // Ganti end_date dengan deadline
        'status',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function boards()
    {
        return $this->hasMany(Board::class, 'project_id');
    }

    public function members()
    {
        return $this->hasMany(ProjectMember::class, 'project_id');
    }
}