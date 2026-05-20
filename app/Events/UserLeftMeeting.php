<?php

namespace App\Events;

use App\Models\Meeting;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class UserLeftMeeting implements ShouldBroadcast
{
    public $meeting;
    public $user;

    public function __construct(Meeting $meeting, User $user)
    {
        $this->meeting = $meeting;
        $this->user = $user;
    }

    public function broadcastOn()
    {
        return new Channel('meeting.' . $this->meeting->id);
    }

    public function broadcastWith()
    {
        return [
            'userId' => $this->user->id,
            'userName' => $this->user->name,
        ];
    }
}
