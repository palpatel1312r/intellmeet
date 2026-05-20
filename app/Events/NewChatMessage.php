<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewChatMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $meetingId;
    public $userId;
    public $userName;
    public $message;
    public $timestamp;

    public function __construct($meetingId, $userId, $userName, $message)
    {
        $this->meetingId = $meetingId;
        $this->userId = $userId;
        $this->userName = $userName;
        $this->message = $message;
        $this->timestamp = now()->toIso8601String();
    }

    public function broadcastOn()
    {
        return new Channel('meeting.' . $this->meetingId);
    }

    public function broadcastAs()
    {
        return 'new-message';
    }
}
