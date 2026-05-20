<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $meeting_id
 * @property string $title
 * @property string|null $description
 * @property int|null $assigned_to
 * @property int $assigned_by
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property string $status
 * @property int $priority
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $assignee
 * @property-read \App\Models\User|null $assigner
 * @property-read \App\Models\Meeting|null $meeting
 * @property-read \App\Models\Task|null $task
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionItem completed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionItem pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionItem whereAssignedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionItem whereAssignedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionItem whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionItem whereMeetingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionItem wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionItem whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionItem whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ActionItem extends Model
{
    protected $fillable = [
        'meeting_id',
        'title',
        'description',
        'assigned_to',
        'assigned_by',
        'due_date',
        'status',
        'priority'
    ];

    protected $casts = [
        'due_date' => 'date',
        'priority' => 'integer',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function task()
    {
        return $this->hasOne(Task::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
