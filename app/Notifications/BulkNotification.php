<?php
// app/Notifications/BulkNotification.php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BulkNotification extends Notification
{
  use Queueable;

  protected $title;
  protected $message;
  protected $sender;

  public function __construct($title, $message, $sender)
  {
    $this->title = $title;
    $this->message = $message;
    $this->sender = $sender;
  }

  public function via($notifiable)
  {
    return ['database'];
  }

  public function toDatabase($notifiable)
  {
    return [
      'type' => 'bulk',
      'title' => $this->title,
      'message' => $this->message,
      'sender_name' => $this->sender->name,
      'action_url' => route('components.index'),
    ];
  }
}
