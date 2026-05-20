<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ChatMessageEvent implements ShouldBroadcast
{
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
    $this->timestamp = now()->toISOString();
  }

  public function broadcastOn()
  {
    return new Channel('meeting.' . $this->meetingId);
  }
}
