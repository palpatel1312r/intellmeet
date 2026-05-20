<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SignalEvent implements ShouldBroadcast
{
  public $meetingId;
  public $userId;
  public $signal;
  public $targetUserId;

  public function __construct($meetingId, $userId, $signal, $targetUserId = null)
  {
    $this->meetingId = $meetingId;
    $this->userId = $userId;
    $this->signal = $signal;
    $this->targetUserId = $targetUserId;
  }

  public function broadcastOn()
  {
    return new Channel('meeting.' . $this->meetingId);
  }

  public function broadcastWith()
  {
    return [
      'userId' => $this->userId,
      'signal' => $this->signal,
      'targetUserId' => $this->targetUserId,
    ];
  }
}
