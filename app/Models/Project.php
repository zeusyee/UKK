<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'projects';
    protected $primaryKey = 'project_id';
    public $timestamps = false;

    protected $fillable = [
        'project_name',
        'description',
        'created_by',
        'deadline'
    ];

    public function members()
    {
        return $this->hasMany(ProjectMember::class, 'project_id', 'project_id');
    }

    // Relasi ke user yang membuat project
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }
}
