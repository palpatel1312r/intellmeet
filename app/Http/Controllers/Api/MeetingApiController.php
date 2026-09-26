<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MeetingResource;
use App\Models\Meeting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MeetingApiController extends Controller
{
    /**
     * List meetings for the authenticated user (created / joined), paginated.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Meeting::with(['creator', 'participants', 'actionItems'])
            ->withCount('participants')
            ->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhereHas('participants', fn ($p) => $p->where('user_id', $user->id));
            });

        if ($search = $request->query('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $meetings = $query->latest('start_time')->paginate($request->integer('per_page', 15));

        return MeetingResource::collection($meetings)->response();
    }

    /**
     * Show a single meeting.
     */
    public function show(Request $request, Meeting $meeting): JsonResponse
    {
        $this->authorizeAccess($request, $meeting);

        $meeting->load(['creator', 'participants', 'actionItems'])->loadCount('participants');

        return response()->json(['meeting' => new MeetingResource($meeting)]);
    }

    /**
     * Create a meeting (generates a unique join code).
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after:start_time',
            'team_id' => 'nullable|exists:teams,id',
            'settings' => 'nullable|array',
            'participant_emails' => 'nullable|array',
            'participant_emails.*' => 'email|exists:users,email',
        ]);

        $meeting = Meeting::create([
            ...$data,
            'created_by' => $request->user()->id,
            'meeting_code' => $this->generateUniqueCode(),
            'status' => 'scheduled',
        ]);

        // Creator is always a participant.
        $meeting->participants()->syncWithoutDetaching([$request->user()->id]);

        if (! empty($data['participant_emails'])) {
            $ids = \App\Models\User::whereIn('email', $data['participant_emails'])->pluck('id');
            $meeting->participants()->syncWithoutDetaching($ids);
        }

        $meeting->load(['creator', 'participants'])->loadCount('participants');

        return response()->json([
            'message' => 'Meeting created.',
            'meeting' => new MeetingResource($meeting),
        ], 201);
    }

    /**
     * Update meeting details.
     */
    public function update(Request $request, Meeting $meeting): JsonResponse
    {
        $this->authorizeAccess($request, $meeting);

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after:start_time',
            'status' => 'nullable|in:scheduled,ongoing,completed,cancelled',
            'summary' => 'nullable|string',
            'transcript' => 'nullable|string',
            'settings' => 'nullable|array',
        ]);

        $meeting->update($data);
        $meeting->load(['creator', 'participants'])->loadCount('participants');

        return response()->json([
            'message' => 'Meeting updated.',
            'meeting' => new MeetingResource($meeting),
        ]);
    }

    /**
     * Cancel / delete a meeting (only the creator).
     */
    public function destroy(Request $request, Meeting $meeting): JsonResponse
    {
        if ($meeting->created_by !== $request->user()->id) {
            return response()->json(['message' => 'Only the meeting creator can delete it.'], 403);
        }

        $meeting->delete();

        return response()->json(['message' => 'Meeting deleted.']);
    }

    /**
     * End an ongoing meeting (mark completed + end time).
     */
    public function end(Request $request, Meeting $meeting): JsonResponse
    {
        $this->authorizeAccess($request, $meeting);

        $meeting->update([
            'status' => 'completed',
            'end_time' => now(),
        ]);

        return response()->json([
            'message' => 'Meeting ended.',
            'meeting' => new MeetingResource($meeting),
        ]);
    }

    /**
     * Join a meeting by its shareable code.
     */
    public function joinByCode(Request $request, string $code): JsonResponse
    {
        $meeting = Meeting::where('meeting_code', strtoupper($code))->first();

        if (! $meeting) {
            return response()->json(['message' => 'Meeting not found for this code.'], 404);
        }

        $meeting->participants()->syncWithoutDetaching([$request->user()->id]);
        $meeting->load(['creator', 'participants'])->loadCount('participants');

        return response()->json([
            'message' => 'Joined meeting.',
            'meeting' => new MeetingResource($meeting),
        ]);
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
            $formatted = substr($code, 0, 4) . '-' . substr($code, 4, 4);
        } while (Meeting::where('meeting_code', $formatted)->exists());

        return $formatted;
    }

    private function authorizeAccess(Request $request, Meeting $meeting): void
    {
        $user = $request->user();
        abort_unless(
            $meeting->created_by === $user->id
                || $meeting->participants()->where('users.id', $user->id)->exists(),
            403,
            'You do not have access to this meeting.'
        );
    }
}
