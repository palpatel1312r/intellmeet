<?php

namespace App\Http\Controllers;

use App\Models\ActionItem;
use App\Models\Meeting;
use App\Models\MeetingParticipant;
use App\Models\Team;
use App\Models\User;
use App\Notifications\MeetingCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MeetingController extends Controller
{
    /**
     * Display list of meetings
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            $meetings = Meeting::with('creator')
                ->latest()
                ->paginate(12);
        } else {
            $meetings = Meeting::where('created_by', $user->id)
                ->orWhereHas('participants', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->with('creator')
                ->latest()
                ->paginate(12);
        }

        return view('meetings.index', compact('meetings'));
    }

    /**
     * Show create meeting form
     */
    public function create()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Only admins can create meetings.');
        }

        $teams = auth()->user()->teams;
        return view('meetings.create', compact('teams'));
    }

    /**
     * Store new meeting
     */
    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Only admins can create meetings.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'time' => 'required',
            'team_id' => 'nullable|exists:teams,id',
        ]);

        $startTime = $request->date . ' ' . $request->time;

        DB::transaction(function () use ($request, $startTime) {
            $meeting = Meeting::create([
                'title' => $request->title,
                'description' => $request->description,
                'meeting_code' => Str::upper(Str::random(8)),
                'start_time' => $startTime,
                'created_by' => auth()->id(),
                'team_id' => $request->team_id,
                'status' => 'scheduled'
            ]);

            // Add creator as participant
            $meeting->participants()->attach(auth()->id(), [
                'joined_at' => now(),
                'is_speaker' => true,
                'is_video_on' => true,
                'is_audio_on' => true,
            ]);

            $notifiedUsers = [];
            $creator = auth()->user();

            // Add all team members as participants if team is selected
            if ($request->team_id) {
                $team = Team::with('members')->find($request->team_id);

                foreach ($team->members as $member) {
                    // Skip the creator (already added)
                    if ($member->id != auth()->id()) {
                        // Check if already a participant
                        $exists = $meeting->participants()->where('user_id', $member->id)->exists();

                        if (!$exists) {
                            $meeting->participants()->attach($member->id, [
                                'joined_at' => null,
                                'is_speaker' => false,
                                'is_video_on' => false,
                                'is_audio_on' => false,
                                'left_at' => null,
                            ]);

                            // Send notification to team member
                            $member->notify(new MeetingCreated($meeting, $creator));
                            $notifiedUsers[] = $member->email;
                        }
                    }
                }
            }

            // Log notification status
            if (!empty($notifiedUsers)) {
                \Log::info('Meeting notifications sent to: ' . implode(', ', $notifiedUsers));
            }
        });

        $message = 'Meeting created successfully!';
        if ($request->team_id) {
            $message .= ' Team members have been invited and notified.';
        }

        return redirect()->route('meetings.index')->with('success', $message);
    }

    /**
     * Show meeting details
     */
    public function show(Meeting $meeting)
    {
        $user = auth()->user();

        if ($user->role !== 'admin' && $meeting->created_by != $user->id && !$meeting->participants->contains($user->id)) {
            abort(403, 'You do not have access to this meeting.');
        }

        $meeting->load(['creator', 'participants', 'actionItems.assignee']);
        return view('meetings.show', compact('meeting'));
    }

    /**
     * Show edit meeting form
     */
    public function edit(Meeting $meeting)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Only admins can edit meetings.');
        }

        if ($meeting->status == 'ended') {
            return redirect()->route('meetings.index')->with('error', 'Cannot edit ended meetings.');
        }

        $teams = auth()->user()->teams;
        return view('meetings.edit', compact('meeting', 'teams'));
    }

    /**
     * Update meeting
     */
    public function update(Request $request, Meeting $meeting)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Only admins can update meetings.');
        }

        if ($meeting->status == 'ended') {
            return redirect()->route('meetings.index')->with('error', 'Cannot update ended meetings.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'time' => 'required',
            'team_id' => 'nullable|exists:teams,id',
        ]);

        $startTime = $request->date . ' ' . $request->time;

        $meeting->update([
            'title' => $request->title,
            'description' => $request->description,
            'start_time' => $startTime,
            'team_id' => $request->team_id,
        ]);

        return redirect()->route('meetings.show', $meeting)
            ->with('success', 'Meeting updated successfully!');
    }

    /**
     * Delete meeting
     */
    public function destroy(Meeting $meeting)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Only admins can delete meetings.');
        }

        DB::transaction(function () use ($meeting) {
            $meeting->participants()->detach();
            $meeting->actionItems()->delete();
            $meeting->delete();
        });

        return redirect()->route('meetings.index')
            ->with('success', 'Meeting deleted successfully!');
    }

    /**
     * Join a meeting
     */

    public function join(Meeting $meeting)
    {
        $user = Auth::user();

        // Check if user can join
        $isAdmin = $user->role === 'admin';
        $isCreator = $meeting->created_by === $user->id;
        $isParticipant = $meeting->participants()->where('user_id', $user->id)->exists();

        if (!$isAdmin && !$isCreator && !$isParticipant) {
            // Add as participant if not already - REMOVE 'role' from here
            $meeting->participants()->attach($user->id, [
                'joined_at' => now(),
                'is_speaker' => false,
                'is_video_on' => false,
                'is_audio_on' => false,
                // Remove 'role' => 'participant' from here
            ]);
        } elseif ($isParticipant) {
            // Update joined_at for existing participant
            $meeting->participants()->updateExistingPivot($user->id, [
                'joined_at' => now(),
            ]);
        }

        // Update meeting status to ongoing if it's scheduled
        if ($meeting->status === 'scheduled') {
            $meeting->update(['status' => 'ongoing']);
        }

        return redirect()->route('meetings.video-room', $meeting);
    }

    /**
     * Join meeting by code (public entry point)
     */
    public function joinByCode($code)
    {
        $meeting = Meeting::where('meeting_code', strtoupper($code))->firstOrFail();

        if ($meeting->status === 'ended') {
            return redirect()->route('meetings.index')->with('error', 'This meeting has already ended.');
        }

        if (auth()->check()) {
            return redirect()->route('meetings.join', $meeting);
        }

        session(['intended_meeting_code' => $meeting->meeting_code]);
        return redirect()->route('login')->with('info', 'Please login to join the meeting.');
    }

    /**
     * Join video meeting room
     */
    public function joinVideo($code)
    {
        $meeting = Meeting::where('meeting_code', $code)->firstOrFail();
        return $this->join($meeting);
    }

    /**
     * Video room view (UNIFIED - only ONE version!)
     */
    public function videoRoom(Meeting $meeting)
    {
        $user = auth()->user();

        $canJoin = $meeting->created_by == $user->id ||
            $user->role === 'admin' ||
            $meeting->participants->contains($user->id);

        if (!$canJoin) {
            abort(403, 'You do not have access to this meeting.');
        }

        // Ensure user is added as participant
        MeetingParticipant::updateOrCreate(
            [
                'meeting_id' => $meeting->id,
                'user_id' => $user->id,
            ],
            [
                'joined_at' => now(),
                'left_at' => null,
                'is_speaker' => $meeting->created_by == $user->id,
                'is_video_on' => true,
                'is_audio_on' => true,
            ]
        );

        $participants = MeetingParticipant::where('meeting_id', $meeting->id)
            ->whereNull('left_at')
            ->with('user')
            ->get();

        return view('meetings.video-room', compact('meeting', 'participants'));
    }

    /**
     * Start meeting
     */
    public function startMeeting(Meeting $meeting)
    {
        if ($meeting->created_by != auth()->id() && auth()->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $meeting->update([
            'status' => 'ongoing',
            'started_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * End meeting
     */
    public function end(Meeting $meeting)
    {
        // Allow creator or admin to end meeting
        if ($meeting->created_by != auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'Only the meeting creator or admin can end this meeting.');
        }

        $meeting->update([
            'status' => 'ended',
            'end_time' => now(),
        ]);

        return redirect()->route('meetings.show', $meeting)
            ->with('success', 'Meeting ended successfully! You can now upload the recording for AI analysis.');
    }

    /**
     * Force end meeting
     */
    public function forceEnd(Meeting $meeting)
    {
        return $this->end($meeting);
    }

    /**
     * Leave meeting
     */
    public function leave($code)
    {
        $meeting = Meeting::where('meeting_code', $code)->firstOrFail();

        MeetingParticipant::where('meeting_id', $meeting->id)
            ->where('user_id', auth()->id())
            ->whereNull('left_at')
            ->update(['left_at' => now()]);

        return redirect()->route('dashboard')->with('success', 'You left the meeting');
    }

    /**
     * Get meeting participants (API)
     */
    public function getParticipants($code)
    {
        $meeting = Meeting::where('meeting_code', $code)->firstOrFail();

        $participants = MeetingParticipant::where('meeting_id', $meeting->id)
            ->whereNull('left_at')
            ->with('user')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->user->id,
                    'name' => $p->user->name,
                    'email' => $p->user->email,
                    'avatar' => $p->user->avatar_url,
                    'is_speaker' => $p->is_speaker,
                    'is_video_on' => $p->is_video_on,
                    'is_audio_on' => $p->is_audio_on,
                    'joined_at' => $p->joined_at,
                ];
            });

        return response()->json([
            'count' => $participants->count(),
            'participants' => $participants
        ]);
    }

    /**
     * Update participant status (API)
     */
    public function updateParticipantStatus(Request $request, $code)
    {
        $meeting = Meeting::where('meeting_code', $code)->firstOrFail();

        $request->validate([
            'is_video_on' => 'boolean',
            'is_audio_on' => 'boolean',
            'is_screen_sharing' => 'boolean',
        ]);

        MeetingParticipant::where('meeting_id', $meeting->id)
            ->where('user_id', auth()->id())
            ->whereNull('left_at')
            ->update($request->only(['is_video_on', 'is_audio_on', 'is_screen_sharing']));

        return response()->json(['success' => true]);
    }

    /**
     * Add participant to meeting
     */
    public function addParticipant(Request $request, Meeting $meeting)
    {
        if ($meeting->created_by != auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'Only the meeting creator can add participants.');
        }

        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::where('email', $request->email)->first();

        if ($meeting->participants->contains($user)) {
            return back()->with('error', 'User is already a participant');
        }

        $meeting->participants()->attach($user->id, [
            'joined_at' => now(),
            'is_speaker' => false,
            'is_video_on' => true,
            'is_audio_on' => true,
        ]);

        return back()->with('success', "{$user->name} added to the meeting!");
    }

    /**
     * Get invite link
     */
    public function getInviteLink(Meeting $meeting)
    {
        $joinUrl = route('meetings.join.by-code', $meeting->meeting_code);

        return response()->json([
            'join_url' => $joinUrl,
            'meeting_code' => $meeting->meeting_code,
            'email_template' => "Join my meeting: {$joinUrl}\n\nMeeting Code: {$meeting->meeting_code}",
        ]);
    }

    /**
     * Mark action item as complete
     */
    public function markActionComplete($id)
    {
        $actionItem = ActionItem::findOrFail($id);

        if ($actionItem->assigned_to == auth()->id() || $actionItem->meeting->created_by == auth()->id()) {
            $actionItem->update(['status' => 'completed']);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 403);
    }

    /**
     * Simple video fallback
     */
    public function simpleVideo(Meeting $meeting)
    {
        if ($meeting->created_by != auth()->id() && !$meeting->participants->contains(auth()->id())) {
            abort(403, 'You do not have access to this meeting.');
        }

        return view('meetings.video-room', compact('meeting'));
    }

    public function simpleVideoRoom(Meeting $meeting)
    {
        return view('meetings.simple-video', compact('meeting'));
    }

    public function videoRoomNew(Meeting $meeting)
    {
        $user = auth()->user();

        $canJoin = $meeting->created_by == $user->id ||
            $user->role === 'admin' ||
            $meeting->participants->contains($user->id);

        if (!$canJoin) {
            abort(403);
        }

        return view('meetings.video-room', compact('meeting'));
    }

    public function getParticipantName($userId)
    {
        $user = User::find($userId);
        return response()->json(['name' => $user->name]);
    }

    public function saveRecording(Request $request)
    {
        $request->validate([
            'recording' => 'required|file|mimes:webm,mp4|max:512000', // Max 500MB
            'meeting_id' => 'required|exists:meetings,id',
            'meeting_code' => 'required|string'
        ]);

        $file = $request->file('recording');
        $meetingId = $request->meeting_id;
        $meetingCode = $request->meeting_code;

        $filename = 'meeting-' . $meetingCode . '-' . now()->format('Y-m-d-H-i-s') . '.webm';
        $path = $file->storeAs('meeting-recordings/' . $meetingId, $filename, 'public');

        // Optional: Save recording info to database
        // Recording::create([
        //     'meeting_id' => $meetingId,
        //     'filename' => $filename,
        //     'path' => $path,
        //     'recorded_by' => auth()->id(),
        //     'size' => $file->getSize(),
        //     'duration' => $request->duration ?? null
        // ]);

        return response()->json(['success' => true, 'path' => $path]);
    }
}
