<?php

namespace App\Notifications;

use App\Models\Meeting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class MeetingInvitation extends Notification
{
    use Queueable;

    protected $meeting;

    public function __construct(Meeting $meeting)
    {
        $this->meeting = $meeting;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Meeting Invitation: ' . $this->meeting->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have been invited to a meeting: ' . $this->meeting->title)
            ->line('Date: ' . $this->meeting->start_time->format('F j, Y g:i A'))
            ->action('Join Meeting', route('meetings.join', $this->meeting))
            ->line('Meeting Code: ' . $this->meeting->meeting_code)
            ->line('Thank you for using IntellMeet!');
    }

    public function toArray($notifiable)
    {
        return [
            'meeting_id' => $this->meeting->id,
            'title' => $this->meeting->title,
            'start_time' => $this->meeting->start_time,
            'message' => 'You have been invited to a meeting: ' . $this->meeting->title
        ];
    }
}
