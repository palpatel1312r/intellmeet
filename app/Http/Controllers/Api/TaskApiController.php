<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskApiController extends Controller
{
    /**
     * List tasks visible to the user, filterable by team/status/priority.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Task::with(['assignee', 'creator', 'attachments'])
            ->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                    ->orWhere('created_by', $user->id)
                    ->orWhereHas('team.members', fn ($m) => $m->where('users.id', $user->id));
            });

        if ($teamId = $request->query('team_id')) {
            $query->where('team_id', $teamId);
        }
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($priority = $request->query('priority')) {
            $query->where('priority', $priority);
        }
        if ($request->boolean('mine')) {
            $query->where('assigned_to', $user->id);
        }

        $tasks = $query->latest()->paginate($request->integer('per_page', 20));

        return TaskResource::collection($tasks)->response();
    }

    public function show(Request $request, Task $task): JsonResponse
    {
        $this->authorizeTask($request, $task);
        $task->load(['assignee', 'creator', 'attachments']);

        return response()->json(['task' => new TaskResource($task)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'team_id' => 'required|exists:teams,id',
            'assigned_to' => 'nullable|exists:users,id',
            'meeting_id' => 'nullable|exists:meetings,id',
            'status' => 'nullable|in:todo,in_progress,review,done',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
        ]);

        $task = Task::create([...$data, 'created_by' => $request->user()->id]);
        $task->load(['assignee', 'creator']);

        return response()->json(['message' => 'Task created.', 'task' => new TaskResource($task)], 201);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $this->authorizeTask($request, $task);

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'nullable|in:todo,in_progress,review,done',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
        ]);

        if (($data['status'] ?? null) === 'done' && ! $task->completed_at) {
            $data['completed_at'] = now();
        }

        $task->update($data);
        $task->load(['assignee', 'creator']);

        return response()->json(['message' => 'Task updated.', 'task' => new TaskResource($task)]);
    }

    public function destroy(Request $request, Task $task): JsonResponse
    {
        $this->authorizeTask($request, $task);
        $task->delete();

        return response()->json(['message' => 'Task deleted.']);
    }

    private function authorizeTask(Request $request, Task $task): void
    {
        $user = $request->user();
        abort_unless(
            $task->created_by === $user->id
                || $task->assigned_to === $user->id
                || $task->team?->members()->where('users.id', $user->id)->exists(),
            403,
            'You do not have access to this task.'
        );
    }
}
