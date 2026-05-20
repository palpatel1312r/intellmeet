<?php

namespace App\Events;

use App\Models\Meeting;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewTranscription implements ShouldBroadcast
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  public $meeting;
  public $text;

  public function __construct(Meeting $meeting, $text)
  {
    $this->meeting = $meeting;
    $this->text = $text;
  }

  public function broadcastOn()
  {
    return new Channel('meeting.' . $this->meeting->id);
  }

  public function broadcastAs()
  {
    return 'new-transcription';
  }
}
