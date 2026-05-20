<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string $role
 * @property string|null $avatar_url
 * @property string|null $bio
 * @property string|null $company
 * @property string|null $position
 * @property string|null $phone
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ActionItem> $actionItems
 * @property-read int|null $action_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ActionItem> $assignedActionItems
 * @property-read int|null $assigned_action_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Meeting> $createdMeetings
 * @property-read int|null $created_meetings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Task> $createdTasks
 * @property-read int|null $created_tasks_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Meeting> $meetings
 * @property-read int|null $meetings_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team> $ownedTeams
 * @property-read int|null $owned_teams_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Task> $tasks
 * @property-read int|null $tasks_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team> $teams
 * @property-read int|null $teams_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAvatarUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTrashed()
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_url',
        'role',
        'bio',           // Add this
        'company',       // Add this
        'position',      // Add this
        'phone',         // Add this

    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'deleted_at' => 'datetime',
        'preferences' => 'array',
    ];

    // Fix the avatar accessor to avoid conflicts with the avatar_url column
    public function getAvatarUrlAttribute($value)
    {
        if ($value) {
            return Storage::url($value);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=6366f1&color=fff';
    }

    // Send password reset notification
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\CustomResetPassword($token));
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_members', 'user_id', 'team_id')
            ->withPivot('role', 'position', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Teams owned by the user
     */
    public function ownedTeams()
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    /**
     * Meetings the user participates in
     */
    public function meetings()
    {
        return $this->belongsToMany(Meeting::class, 'meeting_participants')
            ->withPivot('joined_at', 'left_at', 'is_speaker')
            ->withTimestamps();
    }

    /**
     * Meetings created by the user
     */
    public function createdMeetings()
    {
        return $this->hasMany(Meeting::class, 'created_by');
    }

    /**
     * Tasks assigned to the user
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    /**
     * Tasks created by the user
     */
    public function createdTasks()
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    /**
     * Action items assigned to the user
     */
    public function actionItems()
    {
        return $this->hasMany(ActionItem::class, 'assigned_to');
    }

    /**
     * Action items created by the user
     */
    public function assignedActionItems()
    {
        return $this->hasMany(ActionItem::class, 'assigned_by');
    }

    // ========== HELPER METHODS ==========

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is active (not soft deleted)
     */
    public function isActive()
    {
        return $this->deleted_at === null;
    }

    /**
     * Check if user is inactive (soft deleted)
     */
    public function isInactive()
    {
        return $this->deleted_at !== null;
    }

    /**
     * Activate user (restore from soft delete)
     */
    public function activate()
    {
        $this->restore();
    }

    /**
     * Deactivate user (soft delete)
     */
    public function deactivate()
    {
        $this->delete();
    }

    /**
     * Toggle user status
     */
    public function toggleStatus()
    {
        if ($this->trashed()) {
            $this->restore();
            return 'activated';
        } else {
            $this->delete();
            return 'deactivated';
        }
    }
}
