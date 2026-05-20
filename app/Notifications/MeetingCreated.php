<?php
// app/Notifications/MeetingCreated.php

namespace App\Notifications;

use App\Models\Meeting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MeetingCreated extends Notification
{
    use Queueable;

    protected $meeting;
    protected $creator;

    public function __construct(Meeting $meeting, User $creator)
    {
        $this->meeting = $meeting;
        $this->creator = $creator;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'meeting',
            'meeting_id' => $this->meeting->id,
            'meeting_title' => $this->meeting->title,
            'meeting_time' => $this->meeting->start_time?->format('M d, Y g:i A'),
            'meeting_location' => $this->meeting->location ?? 'Virtual',
            'creator_name' => $this->creator->name,
            'message' => "{$this->creator->name} has scheduled a new meeting: {$this->meeting->title}",
            'action_url' => route('meetings.show', $this->meeting->id), // Make sure this route exists
        ];
    }
}
