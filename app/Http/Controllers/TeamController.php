<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use App\Notifications\TeamCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TeamController extends Controller
{
    /**
     * Display a listing of teams.
     */
    public function index()
    {
        // Admin can see ALL teams
        if (Auth::user()->role === 'admin') {
            $teams = Team::withCount(['members', 'meetings', 'tasks'])->get();
            $ownedTeams = Team::where('owner_id', Auth::id())->withCount('members')->get();
        } else {
            // Regular users only see teams they're part of
            $teams = Auth::user()->teams()->withCount(['members', 'meetings', 'tasks'])->get();
            $ownedTeams = Auth::user()->ownedTeams()->withCount('members')->get();
        }

        return view('teams.index', compact('teams', 'ownedTeams'));
    }

    /**
     * Show the form for creating a new team - ADMIN ONLY
     */
    public function create()
    {
        // Only admin can create teams
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only administrators can create teams.');
        }

        return view('teams.create');
    }

    /**
     * Store a newly created team in storage - ADMIN ONLY
     */
    public function store(Request $request)
    {
        // Only admin can create teams
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only administrators can create teams.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string',
            'members' => 'nullable|array',
            'members.*.email' => 'nullable|email|exists:users,email',
            'members.*.role' => 'nullable|in:member,admin',
        ]);

        DB::beginTransaction();

        try {
            // Create the team
            $team = Team::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name) . '-' . Str::random(5),
                'description' => $request->description,
                'owner_id' => Auth::id(),
                'location' => $request->location,
                'settings' => [
                    'allow_member_invites' => $request->has('allow_member_invites'),
                    'default_role' => $request->default_role ?? 'member',
                ],
            ]);

            // Add the creator as team owner
            $team->members()->attach(Auth::id(), [
                'role' => 'owner',
                'position' => 'Team Owner',
                'joined_at' => now(),
            ]);

            // Add additional members and send notifications
            $addedMembers = 0;
            $failedMembers = [];
            $notifiedUsers = [];
            $creator = Auth::user();

            if ($request->has('members')) {
                foreach ($request->members as $member) {
                    if (!empty($member['email'])) {
                        $user = User::where('email', $member['email'])->first();

                        if ($user && $user->id !== Auth::id()) {
                            // Check if user is not already a member
                            if (!$team->members()->where('user_id', $user->id)->exists()) {
                                $team->members()->attach($user->id, [
                                    'role' => $member['role'] ?? 'member',
                                    'position' => 'Team Member',
                                    'joined_at' => now(),
                                ]);
                                $addedMembers++;

                                // Send notification to new member
                                $user->notify(new TeamCreated($team, $creator));
                                $notifiedUsers[] = $user->email;
                            }
                        } elseif ($user && $user->id === Auth::id()) {
                            // Skip adding yourself again
                            continue;
                        } else {
                            $failedMembers[] = $member['email'];
                        }
                    }
                }
            }

            DB::commit();

            // Log notification activity
            if (!empty($notifiedUsers)) {
                Log::info("Team '{$team->name}' created. Notifications sent to: " . implode(', ', $notifiedUsers));
            }

            $message = "Team '{$team->name}' created successfully!";
            if ($addedMembers > 0) {
                $message .= " {$addedMembers} member(s) added and notified.";
            }
            if (!empty($failedMembers)) {
                $message .= " Could not add: " . implode(', ', $failedMembers);
            }

            return redirect()->route('teams.show', $team)
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to create team: " . $e->getMessage());
            return back()->with('error', 'Failed to create team: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified team.
     */
    public function show(Team $team)
    {
        // Admin can view ANY team
        // Regular users must be members
        $isAdmin = Auth::user()->role === 'admin';
        $isMember = $team->members()->where('user_id', Auth::id())->exists();
        $isOwner = $team->owner_id === Auth::id();

        if (!$isAdmin && !$isMember && !$isOwner) {
            abort(403, 'You are not authorized to view this team.');
        }

        $team->load(['owner', 'members' => function ($q) {
            $q->withPivot('role', 'position', 'joined_at');
        }]);

        // Get team statistics
        $stats = [
            'total_meetings' => $team->meetings()->count(),
            'completed_meetings' => $team->meetings()->where('status', 'ended')->count(),
            'total_tasks' => $team->tasks()->count(),
            'completed_tasks' => $team->tasks()->where('status', 'done')->count(),
            'active_members' => $team->members()->count(),
            'recent_meetings' => $team->meetings()->latest()->limit(5)->get(),
            'recent_tasks' => $team->tasks()->latest()->limit(5)->get(),
        ];

        $upcomingMeetings = $team->meetings()
            ->where('start_time', '>', now())
            ->orderBy('start_time', 'asc')
            ->limit(5)
            ->get();

        return view('teams.show', compact('team', 'stats', 'upcomingMeetings'));
    }

    /**
     * Show the form for editing the specified team.
     */
    public function edit(Team $team)
    {
        // Admin can edit ANY team
        $isAdmin = Auth::user()->role === 'admin';
        $isOwner = $team->owner_id === Auth::id();
        $isAdminMember = $team->members()
            ->where('user_id', Auth::id())
            ->wherePivot('role', 'admin')
            ->exists();

        if (!$isAdmin && !$isOwner && !$isAdminMember) {
            abort(403, 'You are not authorized to edit this team.');
        }

        return view('teams.edit', compact('team'));
    }

    /**
     * Update the specified team in storage.
     */
    public function update(Request $request, Team $team)
    {
        // Admin can update ANY team
        $isAdmin = Auth::user()->role === 'admin';
        $isOwner = $team->owner_id === Auth::id();
        $isAdminMember = $team->members()
            ->where('user_id', Auth::id())
            ->wherePivot('role', 'admin')
            ->exists();

        if (!$isAdmin && !$isOwner && !$isAdminMember) {
            abort(403, 'You are not authorized to update this team.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string',
        ]);

        $team->update([
            'name' => $request->name,
            'description' => $request->description,
            'location' => $request->location,
            'settings' => array_merge($team->settings ?? [], [
                'allow_member_invites' => $request->has('allow_member_invites'),
                'default_role' => $request->default_role ?? 'member',
            ]),
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('team-avatars', 'public');
            $team->update(['avatar_url' => $path]);
        }

        return redirect()->route('teams.show', $team)
            ->with('success', 'Team updated successfully!');
    }

    /**
     * Remove the specified team from storage.
     */
    public function destroy(Team $team)
    {
        // Admin can delete ANY team, otherwise only owner
        $isAdmin = Auth::user()->role === 'admin';

        if (!$isAdmin && $team->owner_id !== Auth::id()) {
            abort(403, 'Only the team owner or admin can delete this team.');
        }

        $team->delete();

        return redirect()->route('teams.index')
            ->with('success', 'Team deleted successfully!');
    }

    /**
     * Display team members.
     */
    public function members(Team $team)
    {
        // Check authorization
        $isAdmin = Auth::user()->role === 'admin';
        $isMember = $team->members()->where('user_id', Auth::id())->exists();

        if (!$isAdmin && !$isMember) {
            abort(403, 'You are not authorized to view team members.');
        }

        $team->load(['members' => function ($q) {
            $q->withPivot('role', 'position', 'joined_at');
        }]);

        return view('teams.members', compact('team'));
    }

    /**
     * Add member to team.
     */
    public function addMember(Request $request, Team $team)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|in:member,admin',
        ]);

        $user = User::where('email', $request->email)->first();

        // Check if already a member
        if ($team->members()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'User is already a team member!'
            ], 422);
        }

        // Add member
        $team->members()->attach($user->id, [
            'role' => $request->role,
            'position' => 'Team Member',
            'joined_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Member added successfully!',
            'member' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $request->role,
                'avatar' => $user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name)
            ]
        ]);
    }

    /**
     * Get team members as JSON (for AJAX)
     */
    public function getMembersJson(Team $team)
    {
        $members = $team->members()->get()->map(function ($member) {
            return [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'avatar_url' => $member->avatar_url,
                'role' => $member->pivot->role,
            ];
        });

        return response()->json([
            'members' => $members,
            'count' => $members->count()
        ]);
    }

    /**
     * Leave team (for members to remove themselves)
     */
    public function leave(Team $team)
    {
        // Prevent owner from leaving
        if ($team->owner_id === Auth::id()) {
            return back()->with('error', 'Team owner cannot leave. Transfer ownership first or delete the team.');
        }

        // Check if user is a member
        if (!$team->members()->where('user_id', Auth::id())->exists()) {
            return back()->with('error', 'You are not a member of this team.');
        }

        // Remove the user from team
        $team->members()->detach(Auth::id());

        return redirect()->route('teams.index')
            ->with('success', 'You have left the team.');
    }

    /**
     * Remove member from team.
     */
    public function removeMember(Team $team, User $user)
    {
        // Check authorization - only owner or admin can remove members
        $isOwner = $team->owner_id === Auth::id();
        $isAdmin = $team->members()
            ->where('user_id', Auth::id())
            ->wherePivot('role', 'admin')
            ->exists();

        if (!$isOwner && !$isAdmin) {
            abort(403, 'You are not authorized to remove team members.');
        }

        // Prevent removing the team owner
        if ($user->id === $team->owner_id) {
            return back()->with('error', 'Cannot remove the team owner.');
        }

        // Prevent removing yourself (use leave team for that)
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Use "Leave Team" option to remove yourself.');
        }

        // Remove the member
        $team->members()->detach($user->id);

        return back()->with('success', $user->name . ' has been removed from the team.');
    }

    /**
     * Update member role.
     */
    public function updateMemberRole(Request $request, Team $team, User $user)
    {
        // Check authorization
        $isAdmin = Auth::user()->role === 'admin';
        $isOwner = $team->owner_id === Auth::id();

        if (!$isAdmin && !$isOwner) {
            abort(403, 'You are not authorized to update member roles.');
        }

        $request->validate([
            'role' => 'required|in:member,admin',
        ]);

        $team->members()->updateExistingPivot($user->id, [
            'role' => $request->role,
        ]);

        return back()->with('success', 'Member role updated successfully!');
    }

    /**
     * Invite a member to the team via email
     */
    public function inviteMember(Request $request, Team $team)
    {
        // Check authorization - only owner or admin can invite
        $isOwner = $team->owner_id === Auth::id();
        $isAdmin = $team->members()
            ->where('user_id', Auth::id())
            ->wherePivot('role', 'admin')
            ->exists();
        $allowMemberInvites = $team->settings['allow_member_invites'] ?? false;

        if (!$isOwner && !$isAdmin && !$allowMemberInvites) {
            abort(403, 'You are not authorized to invite members to this team.');
        }

        $request->validate([
            'email' => 'required|email',
            'role' => 'nullable|in:member,admin',
        ]);

        $user = User::where('email', $request->email)->first();

        // Check if user already exists
        if ($user) {
            // Check if already a member
            if ($team->members()->where('user_id', $user->id)->exists()) {
                return back()->with('error', 'User is already a team member!');
            }

            // Add directly if user exists
            $team->members()->attach($user->id, [
                'role' => $request->role ?? 'member',
                'position' => 'Team Member',
                'joined_at' => now(),
            ]);

            return back()->with('success', "{$user->name} has been added to the team!");
        } else {
            // User doesn't exist, send an invitation email
            return back()->with('info', "Invitation will be sent to {$request->email} when they register.");
        }
    }

    /**
     * Accept team invitation via token
     */
    public function acceptInvite($token)
    {
        return redirect()->route('teams.index')
            ->with('info', 'Invitation acceptance feature coming soon.');
    }

    /**
     * Transfer team ownership
     */
    public function transferOwnership(Request $request, Team $team)
    {
        // Only current owner can transfer ownership
        if ($team->owner_id !== Auth::id()) {
            abort(403, 'Only the team owner can transfer ownership.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $newOwner = User::find($request->user_id);

        // Check if new owner is a team member
        if (!$team->members()->where('user_id', $newOwner->id)->exists()) {
            return back()->with('error', 'The new owner must be a team member.');
        }

        // Update team owner
        $team->update(['owner_id' => $newOwner->id]);

        // Update roles: new owner becomes owner, old owner becomes admin
        $team->members()->updateExistingPivot(Auth::id(), ['role' => 'admin']);
        $team->members()->updateExistingPivot($newOwner->id, ['role' => 'owner']);

        return back()->with('success', "Team ownership transferred to {$newOwner->name}");
    }
}
