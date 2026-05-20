<?php

namespace App\Events;

use App\Models\User;
use App\Models\Meeting;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserJoinedMeeting implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $meeting;
    public $user;
    public $participantInfo;

    public function __construct(Meeting $meeting, User $user, array $participantInfo)
    {
        $this->meeting = $meeting;
        $this->user = $user;
        $this->participantInfo = $participantInfo;
    }

    public function broadcastOn()
    {
        return new Channel('meeting.' . $this->meeting->meeting_code);
    }

    public function broadcastWith()
    {
        return [
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'role' => $this->user->role,
                'is_video_on' => $this->participantInfo['is_video_on'],
                'is_audio_on' => $this->participantInfo['is_audio_on'],
            ]
        ];
    }
}
