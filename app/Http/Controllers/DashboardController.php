<?php

namespace App\Http\Controllers;

use App\Models\ActionItem;
use App\Models\Meeting;
use App\Models\Task;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_meetings' => Meeting::where('created_by', auth()->id())->count(),
            'pending_tasks' => Task::where('assigned_to', auth()->id())
                ->where('status', '!=', 'done')
                ->count(),
            'teams.id' => auth()->user()->teams()->withCount('members')->get()->sum('members_count'),
            'hours_saved' => rand(10, 50), // You can calculate this based on actual data
        ];

        $upcomingMeetings = Meeting::where(function ($q) {
            $q->where('created_by', auth()->id())
                ->orWhereHas('participants', function ($q2) {
                    $q2->where('user_id', auth()->id());
                });
        })
            ->where('start_time', '>', now())
            ->withCount('participants')
            ->orderBy('start_time', 'asc')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact('stats', 'upcomingMeetings'));
    }

    public function meetingAnalytics()
    {
        $user = auth()->user();

        $meetings = Meeting::where('created_by', $user->id)
            ->orWhereHas('participants', fn($q) => $q->where('user_id', $user->id))
            ->latest()
            ->paginate(10);

        $stats = [
            'total_meetings' => $meetings->total(),
            'ai_processed' => Meeting::where('created_by', $user->id)->whereNotNull('summary')->count(),
            'action_items' => ActionItem::whereHas('meeting', fn($q) => $q->where('created_by', $user->id))->count(),
            'completion_rate' => 65, // Calculate from actual data
        ];

        return view('meetings.dashboard', compact('meetings', 'stats'));
    }

    public function analyticsDashboard()
    {
        $meetingsByMonth = Meeting::where('created_by', auth()->id())
            ->selectRaw('DATE_FORMAT(created_at, "%b") as month, COUNT(*) as count')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderByRaw('MIN(created_at)')
            ->get();

        $completedTasks = Task::where('assigned_to', auth()->id())->where('status', 'done')->count();
        $pendingTasks = Task::where('assigned_to', auth()->id())->where('status', '!=', 'done')->count();

        $productivityScore = min(100, round(
            ($completedTasks / max(1, $completedTasks + $pendingTasks)) * 70 +
                (Meeting::where('created_by', auth()->id())->count() / 10) * 30
        ));

        return view('analytics.dashboard', [
            'chartLabels' => $meetingsByMonth->pluck('month'),
            'chartData' => $meetingsByMonth->pluck('count'),
            'completedTasks' => $completedTasks,
            'pendingTasks' => $pendingTasks,
            'weeklyActivity' => [5, 8, 12, 7, 9, 3, 2],
            'productivityScore' => $productivityScore,
        ]);
    }
}
