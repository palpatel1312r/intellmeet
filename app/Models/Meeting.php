<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $created_by
 * @property int|null $team_id
 * @property string $title
 * @property string|null $description
 * @property string $meeting_code
 * @property \Illuminate\Support\Carbon|null $start_time
 * @property \Illuminate\Support\Carbon|null $end_time
 * @property string|null $recording_url
 * @property string|null $transcript
 * @property string|null $summary
 * @property string|null $ai_metadata
 * @property array<array-key, mixed>|null $settings
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ActionItem> $actionItems
 * @property-read int|null $action_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ChatMessage> $chatMessages
 * @property-read int|null $chat_messages_count
 * @property-read \App\Models\User|null $creator
 * @property-read mixed $join_link
 * @property-read int|null $participants_count
 * @property-read mixed $shareable_url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $participants
 * @property-read \App\Models\Team|null $team
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereAiMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereMeetingCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereRecordingUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereTranscript($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Meeting extends Model
{
    protected $fillable = [
        'title',
        'description',
        'team_id',
        'created_by',
        'meeting_code',
        'start_time',
        'end_time',
        'recording_url',
        'status',
        'settings',
        'transcript',
        'summary'
    ];

    protected $casts = [
        'settings' => 'array',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // ✅ KEEP this (good logic)
            if (!$model->meeting_code) {
                $model->meeting_code = strtoupper(substr(md5(uniqid()), 0, 8));
            }
        });
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'meeting_participants')
            ->withPivot('joined_at', 'left_at', 'is_speaker')
            ->withTimestamps();
    }

    public function actionItems()
    {
        return $this->hasMany(ActionItem::class);
    }

    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function isActive()
    {
        return $this->status === 'ongoing';
    }

    public function getParticipantsCountAttribute()
    {
        return $this->participants()->count();
    }

    /**
     * Get the meeting join link
     */
    public function getJoinLinkAttribute()
    {
        return route('meetings.join', $this->meeting_code);
    }

    /**
     * Get shareable meeting URL
     */
    public function getShareableUrlAttribute()
    {
        return url('/join/' . $this->meeting_code);
    }
}
