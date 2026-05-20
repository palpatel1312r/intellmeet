<div class="bg-white rounded-lg p-3 shadow-sm border-l-4 border-{{ $task->priority_color }}-500 hover:shadow-md transition cursor-move"
    data-task-id="{{ $task->id }}">
    <div class="flex justify-between items-start mb-2">
        <h4 class="font-medium text-gray-800 text-sm">{{ Str::limit($task->title, 40) }}</h4>
        <span
            class="text-xs px-2 py-1 rounded-full bg-{{ $task->priority_color }}-100 text-{{ $task->priority_color }}-800">
            {{ ucfirst($task->priority) }}
        </span>
    </div>

    @if ($task->description)
        <p class="text-gray-500 text-xs mb-2">{{ Str::limit($task->description, 60) }}</p>
    @endif

    <div class="flex justify-between items-center mt-2">
        <div class="flex items-center space-x-2">
            @if ($task->assignee)
                <img src="{{ $task->assignee->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($task->assignee->name) }}"
                    class="w-6 h-6 rounded-full" alt="">
                <span class="text-xs text-gray-600">{{ $task->assignee->name }}</span>
            @else
                <span class="text-xs text-gray-400">Unassigned</span>
            @endif
        </div>

        @if ($task->due_date)
            <span class="text-xs {{ $task->isOverdue() ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                <i class="far fa-calendar-alt mr-1"></i>
                {{ $task->due_date->format('M d') }}
            </span>
        @endif

        <div class="flex space-x-2">
            <a href="{{ route('tasks.show', $task) }}"
                class="bg-blue-500 text-white px-3 py-1 rounded-md text-sm hover:bg-blue-600 transition">
                View
            </a>

            <a href="{{ route('tasks.edit', $task) }}"
                class="bg-green-500 text-white px-3 py-1 rounded-md text-sm hover:bg-green-600 transition">
                Edit
            </a>
        </div>
    </div>
</div>
