<?php
// app/Notifications/TeamCreated.php

namespace App\Notifications;

use App\Models\Team;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TeamCreated extends Notification
{
  use Queueable;

  protected $team;
  protected $creator;

  public function __construct(Team $team, User $creator)
  {
    $this->team = $team;
    $this->creator = $creator;
  }

  public function via($notifiable)
  {
    return ['database'];
  }

  public function toDatabase($notifiable)
  {
    return [
      'type' => 'team',
      'team_id' => $this->team->id,
      'team_name' => $this->team->name,
      'team_description' => $this->team->description,
      'creator_name' => $this->creator->name,
      'message' => "You've been added to a new team: {$this->team->name}",
      'action_url' => route('teams.show', $this->team->id), // Make sure this route exists
    ];
  }
}
