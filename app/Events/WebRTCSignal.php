<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebRTCSignal implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $meetingId;
    public $signal;
    public $callerId;

    public function __construct($meetingId, $signal, $callerId)
    {
        $this->meetingId = $meetingId;
        $this->signal = $signal;
        $this->callerId = $callerId;
    }

    public function broadcastOn()
    {
        return new Channel('meeting.' . $this->meetingId);
    }

    public function broadcastAs()
    {
        return 'webrtc-signal';
    }
}
