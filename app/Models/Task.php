<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['description', 'status', 'parent_task_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subtasks()
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    public function scopeParentOnly($query)
    {
        return $query->whereNull('parent_task_id');
    }

    public function isInProgress()
    {
        return $this->status === TaskStatus::IN_PROGRESS->value;
    }

    public function isSubtask(): bool
    {
        return $this->parent_task_id !== null;
    }
}
