<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        return $user->id === $task->created_by ||
            $user->id === $task->assigned_to ||
            $task->team->members->contains($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Task $task): bool
    {
        return $user->id === $task->created_by ||
            $user->id === $task->assigned_to ||
            $task->team->owner_id === $user->id;
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->id === $task->created_by ||
            $task->team->owner_id === $user->id;
    }
}
