<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MeetingResource;
use App\Http\Resources\TaskResource;
use App\Models\Meeting;
use App\Models\Task;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    /**
     * Aggregated stats + recent items for the React dashboard.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $stats = [
            'total_meetings' => Meeting::where('created_by', $user->id)
                ->orWhereHas('participants', fn ($q) => $q->where('users.id', $user->id))->count(),
            'upcoming_meetings' => Meeting::where('start_time', '>=', now())
                ->where('status', 'scheduled')->count(),
            'active_meetings' => Meeting::where('status', 'ongoing')->count(),
            'total_tasks' => Task::where('assigned_to', $user->id)->count(),
            'pending_tasks' => Task::where('assigned_to', $user->id)
                ->whereIn('status', ['todo', 'in_progress'])->count(),
            'completed_tasks' => Task::where('assigned_to', $user->id)
                ->where('status', 'done')->count(),
            'teams_count' => Team::where('owner_id', $user->id)
                ->orWhereHas('members', fn ($q) => $q->where('users.id', $user->id))->count(),
        ];

        $recentMeetings = Meeting::with('creator')
            ->where(fn ($q) => $q->where('created_by', $user->id)
                ->orWhereHas('participants', fn ($p) => $p->where('user_id', $user->id)))
            ->latest('start_time')->take(5)->get();

        $recentTasks = Task::with('assignee')
            ->where('assigned_to', $user->id)
            ->latest()->take(5)->get();

        return response()->json([
            'stats' => $stats,
            'recent_meetings' => MeetingResource::collection($recentMeetings),
            'recent_tasks' => TaskResource::collection($recentTasks),
        ]);
    }
}
