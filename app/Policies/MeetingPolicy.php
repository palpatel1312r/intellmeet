<?php

namespace App\Policies;

use App\Models\Meeting;
use App\Models\User;

class MeetingPolicy
{
    /**
     * Determine if the user can view any meetings (list them)
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view meetings list
    }

    /**
     * Determine if the user can view a specific meeting
     */
    public function view(User $user, Meeting $meeting): bool
    {
        return true; // Any authenticated user can view meeting details
    }

    /**
     * Determine if the user can create meetings
     */
    public function create(User $user): bool
    {
        // Only admins can create meetings
        return $user->role === 'admin';
    }

    /**
     * Determine if the user can update the meeting
     */
    public function update(User $user, Meeting $meeting): bool
    {
        // Only admins can update meetings
        return $user->role === 'admin';
    }

    /**
     * Determine if the user can delete the meeting
     */
    public function delete(User $user, Meeting $meeting): bool
    {
        // Only admins can delete meetings
        return $user->role === 'admin';
    }

    /**
     * Determine if the user can join the meeting
     */
    public function join(User $user, Meeting $meeting): bool
    {
        // All authenticated users can join meetings
        return true;
    }
}
