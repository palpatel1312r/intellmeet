<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $meeting_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $joined_at
 * @property \Illuminate\Support\Carbon|null $left_at
 * @property bool $is_speaker
 * @property bool $is_video_on
 * @property bool $is_audio_on
 * @property bool $is_screen_sharing
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Meeting|null $meeting
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingParticipant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingParticipant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingParticipant query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingParticipant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingParticipant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingParticipant whereIsAudioOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingParticipant whereIsScreenSharing($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingParticipant whereIsSpeaker($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingParticipant whereIsVideoOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingParticipant whereJoinedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingParticipant whereLeftAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingParticipant whereMeetingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingParticipant whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeetingParticipant whereUserId($value)
 * @mixin \Eloquent
 */
class MeetingParticipant extends Model
{
  protected $fillable = [
    'meeting_id',
    'user_id',
    'joined_at',
    'left_at',
    'is_speaker',
    'is_video_on',
    'is_audio_on',
    'is_screen_sharing'
  ];

  protected $casts = [
    'joined_at' => 'datetime',
    'left_at' => 'datetime',
    'is_speaker' => 'boolean',
    'is_video_on' => 'boolean',
    'is_audio_on' => 'boolean',
    'is_screen_sharing' => 'boolean',
  ];

  public function meeting()
  {
    return $this->belongsTo(Meeting::class);
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
