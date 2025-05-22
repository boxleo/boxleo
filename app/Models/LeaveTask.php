<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'leave_id',
        'assignee_id',
        'task_description',
    ];

    // Relationships

    /**
     * The leave request this task belongs to
     */
    public function leave()
    {
        return $this->belongsTo(Leave::class);
    }

    /**
     * The user this task is assigned to
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }
}
