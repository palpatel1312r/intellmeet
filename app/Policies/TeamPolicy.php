<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    public function view(User $user, Team $team): bool
    {
        return $team->members()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Team $team): bool
    {
        // Check if user is owner
        if ($team->owner_id === $user->id) {
            return true;
        }

        // Check if user is admin - specify the table for role column
        $member = $team->members()->where('user_id', $user->id)->first();
        return $member && $member->pivot->role === 'admin';
    }

    public function delete(User $user, Team $team): bool
    {
        return $team->owner_id === $user->id;
    }

    public function invite(User $user, Team $team): bool
    {
        if ($team->owner_id === $user->id) {
            return true;
        }

        $member = $team->members()->where('user_id', $user->id)->first();
        return $member && ($member->pivot->role === 'admin' || ($team->settings['allow_member_invites'] ?? false));
    }

    public function removeMember(User $user, Team $team): bool
    {
        if ($team->owner_id === $user->id) {
            return true;
        }

        $member = $team->members()->where('user_id', $user->id)->first();
        return $member && $member->pivot->role === 'admin';
    }

    public function updateMemberRole(User $user, Team $team): bool
    {
        return $team->owner_id === $user->id;
    }
}
