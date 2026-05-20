<?php
// app/Notifications/TaskAssigned.php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskAssigned extends Notification
{
  use Queueable;

  protected $task;
  protected $assigner;

  public function __construct(Task $task, User $assigner)
  {
    $this->task = $task;
    $this->assigner = $assigner;
  }

  public function via($notifiable)
  {
    return ['database'];
  }

  public function toDatabase($notifiable)
  {
    $priorityColors = [
      'low' => 'text-green-600',
      'medium' => 'text-yellow-600',
      'high' => 'text-orange-600',
      'urgent' => 'text-red-600'
    ];

    $priorityIcons = [
      'low' => 'fa-arrow-down',
      'medium' => 'fa-minus',
      'high' => 'fa-arrow-up',
      'urgent' => 'fa-exclamation-triangle'
    ];

    $message = "{$this->assigner->name} assigned you a task: {$this->task->title}";

    if ($this->task->due_date) {
      $message .= " (Due: " . $this->task->due_date->format('M d, Y') . ")";
    }

    return [
      'type' => 'task',
      'task_id' => $this->task->id,
      'task_title' => $this->task->title,
      'task_description' => $this->task->description,
      'task_due_date' => $this->task->due_date?->toISOString(),
      'task_priority' => $this->task->priority,
      'task_priority_color' => $priorityColors[$this->task->priority] ?? 'text-gray-600',
      'task_priority_icon' => $priorityIcons[$this->task->priority] ?? 'fa-tasks',
      'team_id' => $this->task->team_id,
      'team_name' => $this->task->team?->name,
      'assigner_id' => $this->assigner->id,
      'assigner_name' => $this->assigner->name,
      'assigner_avatar' => $this->assigner->avatar_url,
      'message' => $message,
      'action_url' => route('tasks.show', $this->task->id), // Make sure this route exists
    ];
  }
}
