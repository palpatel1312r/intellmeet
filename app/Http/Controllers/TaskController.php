<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\Team;
use App\Models\User;
use App\Notifications\TaskAssigned;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    /**
     * Check if user is admin
     */
    private function isAdmin()
    {
        return Auth::user() && Auth::user()->role === 'admin';
    }

    /**
     * Display tasks dashboard (Kanban board)
     */
    public function index(Request $request)
    {
        $teamId = $request->get('team_id', Auth::user()->teams->first()?->id);

        $query = Task::where('team_id', $teamId)
            ->with(['assignee', 'creator', 'meeting']);

        // Filter by assignee
        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        $tasks = $query->get();

        // Group tasks by status for Kanban board
        $kanbanTasks = [
            'todo' => $tasks->where('status', 'todo')->values(),
            'in_progress' => $tasks->where('status', 'in_progress')->values(),
            'review' => $tasks->where('status', 'review')->values(),
            'done' => $tasks->where('status', 'done')->values(),
        ];

        $teams = Auth::user()->teams;
        $teamMembers = Team::find($teamId)?->members ?? collect();

        $stats = [
            'total' => $tasks->count(),
            'completed' => $tasks->where('status', 'done')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'overdue' => $tasks->filter(function ($task) {
                return $task->isOverdue();
            })->count(),
        ];

        return view('tasks.index', compact('kanbanTasks', 'teams', 'teamId', 'teamMembers', 'tasks', 'stats'));
    }

    /**
     * Show create task form - ADMIN ONLY
     */
    public function create(Request $request)
    {
        if (!$this->isAdmin()) {
            abort(403, 'Only administrators can create tasks.');
        }

        $teamId = $request->get('team_id');
        $meetingId = $request->get('meeting_id');

        $teams = Auth::user()->teams;
        $selectedTeam = $teamId ? Team::find($teamId) : $teams->first();
        $teamMembers = $selectedTeam ? $selectedTeam->members : collect();
        $meeting = $meetingId ? Meeting::find($meetingId) : null;

        return view('tasks.create', compact('teams', 'teamMembers', 'selectedTeam', 'meeting'));
    }

    /**
     * Store new task - ADMIN ONLY
     */
    public function store(Request $request)
    {
        // Only admin can create tasks
        if (!$this->isAdmin()) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            abort(403, 'Only administrators can create tasks.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'team_id' => 'required|exists:teams,id',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'meeting_id' => 'nullable|exists:meetings,id',
            'attachments.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif'
        ]);

        DB::transaction(function () use ($request) {
            $task = Task::create([
                'title' => $request->title,
                'description' => $request->description,
                'team_id' => $request->team_id,
                'assigned_to' => $request->assigned_to,
                'created_by' => Auth::id(),
                'meeting_id' => $request->meeting_id,
                'priority' => $request->priority,
                'due_date' => $request->due_date,
                'status' => 'todo',
            ]);

            // Handle file attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $filePath = $file->storeAs('task-attachments/' . $task->id, $filename, 'public');

                    $task->attachments()->create([
                        'uploaded_by' => Auth::id(),
                        'filename' => $filename,
                        'original_filename' => $file->getClientOriginalName(),
                        'file_path' => $filePath,
                        'file_type' => $file->getMimeType(),
                        'file_extension' => $file->getClientOriginalExtension(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }

            // Send notification if task is assigned to someone
            if ($request->assigned_to) {
                $assigner = Auth::user();
                $assignedUser = User::find($request->assigned_to);

                if ($assignedUser) {
                    $assignedUser->notify(new TaskAssigned($task, $assigner));
                    Log::info("Task #{$task->id} assigned to {$assignedUser->email} with notification");
                }
            } else {
                // If no specific assignee, notify all team members
                $team = Team::find($request->team_id);
                if ($team) {
                    $assigner = Auth::user();
                    $notifiedCount = 0;

                    foreach ($team->members as $member) {
                        if ($member->id != Auth::id()) {
                            $member->notify(new TaskAssigned($task, $assigner));
                            $notifiedCount++;
                        }
                    }

                    Log::info("Task #{$task->id} created for team #{$request->team_id}, notified {$notifiedCount} members");
                }
            }
        });

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Task created and notifications sent!']);
        }

        return redirect()->route('tasks.index', ['team_id' => $request->team_id])
            ->with('success', 'Task created successfully with ' . ($request->hasFile('attachments') ? count($request->file('attachments')) . ' attachment(s)!' : ''));
    }

    /**
     * Show single task
     */
    public function show(Task $task)
    {
        $task->load(['assignee', 'creator', 'team', 'meeting']);
        return view('tasks.show', compact('task'));
    }

    /**
     * Show edit form - ADMIN ONLY
     */
    public function edit(Task $task)
    {
        if (!$this->isAdmin()) {
            abort(403, 'Only administrators can edit tasks.');
        }

        $teams = Auth::user()->teams;
        $teamMembers = $task->team->members;
        return view('tasks.edit', compact('task', 'teams', 'teamMembers'));
    }

    /**
     * Update task - ADMIN ONLY
     */
    public function update(Request $request, Task $task)
    {
        // Only admin can update tasks
        if (!$this->isAdmin()) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            abort(403, 'Only administrators can update tasks.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'status' => 'required|in:todo,in_progress,review,done',
        ]);

        $oldAssignee = $task->assigned_to;

        $task->update($request->only([
            'title',
            'description',
            'assigned_to',
            'priority',
            'due_date',
            'status'
        ]));

        if ($request->status === 'done' && !$task->completed_at) {
            $task->update(['completed_at' => now()]);
        } elseif ($request->status !== 'done') {
            $task->update(['completed_at' => null]);
        }

        // Send notification if assignee changed
        if ($request->assigned_to && $request->assigned_to != $oldAssignee) {
            $assigner = Auth::user();
            $newAssignee = User::find($request->assigned_to);

            if ($newAssignee) {
                $newAssignee->notify(new TaskAssigned($task, $assigner));
                Log::info("Task #{$task->id} re-assigned from user #{$oldAssignee} to {$newAssignee->email}");
            }
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'task' => $task]);
        }

        return redirect()->route('tasks.index', ['team_id' => $task->team_id])
            ->with('success', 'Task updated successfully!');
    }

    /**
     * Update task status - Allow any authenticated user
     */
    public function updateStatus(Request $request, Task $task)
    {
        $request->validate([
            'status' => 'required|in:todo,in_progress,review,done',
        ]);

        $oldStatus = $task->status;
        $task->update(['status' => $request->status]);

        if ($request->status === 'done') {
            $task->update(['completed_at' => now()]);
        } elseif ($oldStatus === 'done' && $request->status !== 'done') {
            $task->update(['completed_at' => null]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Delete task - ADMIN ONLY
     */
    public function destroy(Task $task)
    {
        // Only admin can delete tasks
        if (!$this->isAdmin()) {
            abort(403, 'Only administrators can delete tasks.');
        }

        $teamId = $task->team_id;
        $task->delete();

        return redirect()->route('tasks.index', ['team_id' => $teamId])
            ->with('success', 'Task deleted successfully!');
    }

    /**
     * Get tasks for API (AJAX)
     */
    public function getTasks(Request $request)
    {
        $teamId = $request->get('team_id');

        $tasks = Task::where('team_id', $teamId)
            ->with(['assignee', 'creator'])
            ->get()
            ->groupBy('status');

        return response()->json($tasks);
    }

    /**
     * My tasks (tasks assigned to current user)
     */
    public function myTasks()
    {
        $tasks = Task::where('assigned_to', Auth::id())
            ->with(['team', 'creator', 'meeting'])
            ->orderBy('due_date')
            ->get();

        $stats = [
            'total' => $tasks->count(),
            'completed' => $tasks->where('status', 'done')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'overdue' => $tasks->filter(function ($task) {
                return $task->isOverdue() && $task->status !== 'done';
            })->count(),
        ];

        return view('tasks.my-tasks', compact('tasks', 'stats'));
    }

    /**
     * Get unread notifications count for dashboard
     */
    public function getUnreadNotificationsCount()
    {
        $count = Auth::user()->unreadNotifications->count();
        return response()->json(['count' => $count]);
    }
    /**
     * Upload attachment to task
     */

    public function uploadAttachment(Request $request, Task $task)
    {
        // Check if user has permission (assigned user, creator, or admin)
        $user = Auth::user();
        $isAdmin = $user->role === 'admin';
        $isCreator = $task->created_by === $user->id;
        $isAssignee = $task->assigned_to === $user->id;

        if (!$isAdmin && !$isCreator && !$isAssignee) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            abort(403, 'You do not have permission to upload attachments to this task.');
        }

        $request->validate([
            'attachment' => 'required|file|max:10240', // Max 10MB
            'description' => 'nullable|string|max:500'
        ]);

        $file = $request->file('attachment');
        $originalFilename = $file->getClientOriginalName();
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('task-attachments/' . $task->id, $filename, 'public');

        $attachment = $task->attachments()->create([
            'uploaded_by' => Auth::id(),
            'filename' => $filename,
            'original_filename' => $originalFilename,
            'file_path' => $filePath,
            'file_type' => $file->getMimeType(),
            'file_extension' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'description' => $request->description
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'attachment' => $attachment,
                'message' => 'File uploaded successfully!'
            ]);
        }

        return redirect()->back()->with('success', 'File uploaded successfully!');
    }

    /**
     * Delete attachment
     */
    public function deleteAttachment(Task $task, TaskAttachment $attachment)
    {
        // Check if user has permission
        $user = Auth::user();
        $isAdmin = $user->role === 'admin';
        $isCreator = $task->created_by === $user->id;
        $isUploader = $attachment->uploaded_by === $user->id;

        if (!$isAdmin && !$isCreator && !$isUploader) {
            abort(403, 'You do not have permission to delete this attachment.');
        }

        // Delete file from storage
        if (\Storage::disk('public')->exists($attachment->file_path)) {
            \Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        return redirect()->back()->with('success', 'File deleted successfully!');
    }

    /**
     * Download attachment
     */
    public function downloadAttachment(Task $task, TaskAttachment $attachment)
    {
        // Check if user has access to the task
        $user = Auth::user();
        $isAdmin = $user->role === 'admin';
        $isCreator = $task->created_by === $user->id;
        $isAssignee = $task->assigned_to === $user->id;
        $isTeamMember = $task->team->members()->where('user_id', $user->id)->exists();

        if (!$isAdmin && !$isCreator && !$isAssignee && !$isTeamMember) {
            abort(403, 'You do not have permission to download this file.');
        }

        $filePath = storage_path('app/public/' . $attachment->file_path);

        if (!file_exists($filePath)) {
            abort(404, 'File not found.');
        }

        return response()->download($filePath, $attachment->original_filename);
    }
}
