@extends('layouts.app')

@section('title', 'Edit Task - ' . $task->title)

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 text-white">
                <div class="flex items-center">
                    <a href="{{ route('tasks.show', $task) }}" class="text-white hover:text-indigo-200 mr-4">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold">Edit Task</h1>
                        <p class="text-indigo-100">Update task details and status</p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form action="{{ route('tasks.update', $task) }}" method="POST" class="p-6">
                @csrf
                @method('PUT')

                <!-- Task Title -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Task Title *</label>
                    <input type="text" name="title" required value="{{ old('title', $task->title) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('title')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Description</label>
                    <textarea name="description" rows="5"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Detailed description of the task...">{{ old('description', $task->description) }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <!-- Assign To -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Assign To</label>
                        <select name="assigned_to"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Unassigned</option>
                            @foreach ($teamMembers ?? $task->team->members as $member)
                                <option value="{{ $member->id }}"
                                    {{ old('assigned_to', $task->assigned_to) == $member->id ? 'selected' : '' }}>
                                    {{ $member->name }} ({{ $member->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Status</label>
                        <select name="status"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="todo" {{ old('status', $task->status) == 'todo' ? 'selected' : '' }}>📋 To Do
                            </option>
                            <option value="in_progress"
                                {{ old('status', $task->status) == 'in_progress' ? 'selected' : '' }}>🔄 In Progress
                            </option>
                            <option value="review" {{ old('status', $task->status) == 'review' ? 'selected' : '' }}>👀
                                Review</option>
                            <option value="done" {{ old('status', $task->status) == 'done' ? 'selected' : '' }}>✅ Done
                            </option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <!-- Priority -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Priority</label>
                        <select name="priority"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="low" {{ old('priority', $task->priority) == 'low' ? 'selected' : '' }}>🟢 Low
                            </option>
                            <option value="medium" {{ old('priority', $task->priority) == 'medium' ? 'selected' : '' }}>🟡
                                Medium</option>
                            <option value="high" {{ old('priority', $task->priority) == 'high' ? 'selected' : '' }}>🟠
                                High</option>
                            <option value="urgent" {{ old('priority', $task->priority) == 'urgent' ? 'selected' : '' }}>🔴
                                Urgent</option>
                        </select>
                    </div>

                    <!-- Due Date -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Due Date</label>
                        <input type="date" name="due_date"
                            value="{{ old('due_date', $task->due_date ? $task->due_date->format('Y-m-d') : '') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Task from Meeting (read-only if exists) -->
                @if ($task->meeting)
                    <div class="bg-indigo-50 rounded-lg p-4 mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-video text-indigo-600 text-xl mr-3"></i>
                            <div>
                                <p class="text-sm text-gray-600">This task was created from meeting:</p>
                                <a href="{{ route('meetings.show', $task->meeting) }}"
                                    class="font-semibold text-indigo-600 hover:text-indigo-700">
                                    {{ $task->meeting->title }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Progress Bar for Status -->
                <div class="bg-gray-100 rounded-lg p-4 mb-6">
                    <div class="flex justify-between text-sm text-gray-600 mb-2">
                        <span>Task Progress</span>
                        <span>
                            @if ($task->status == 'todo')
                                0%
                            @elseif($task->status == 'in_progress')
                                33%
                            @elseif($task->status == 'review')
                                66%
                            @else
                                100%
                            @endif
                        </span>
                    </div>
                    <div class="w-full bg-gray-300 rounded-full h-2">
                        <div class="bg-indigo-600 rounded-full h-2 transition-all duration-300"
                            style="width: 
                            @if ($task->status == 'todo') 0%
                            @elseif($task->status == 'in_progress') 33%
                            @elseif($task->status == 'review') 66%
                            @else 100% @endif">
                        </div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 mt-2">
                        <span>To Do</span>
                        <span>In Progress</span>
                        <span>Review</span>
                        <span>Done</span>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <a href="{{ route('tasks.show', $task) }}"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-save mr-2"></i>Update Task
                    </button>
                </div>
            </form>
        </div>

        <!-- Activity Log (Optional) -->
        <div class="mt-6 bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h3 class="font-semibold text-gray-800">Task Activity</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div class="flex items-start space-x-3">
                        <div class="bg-green-100 rounded-full p-2">
                            <i class="fas fa-check-circle text-green-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-800">Task created by {{ $task->creator->name }}</p>
                            <p class="text-xs text-gray-500">{{ $task->created_at->diffForHumans() }}</p>
                        </div>
                    </div>

                    @if ($task->updated_at != $task->created_at)
                        <div class="flex items-start space-x-3">
                            <div class="bg-blue-100 rounded-full p-2">
                                <i class="fas fa-edit text-blue-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-800">Task last updated</p>
                                <p class="text-xs text-gray-500">{{ $task->updated_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endif

                    @if ($task->completed_at)
                        <div class="flex items-start space-x-3">
                            <div class="bg-purple-100 rounded-full p-2">
                                <i class="fas fa-trophy text-purple-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-800">Task completed</p>
                                <p class="text-xs text-gray-500">{{ $task->completed_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Danger Zone (for task creators/owners) -->
        @can('delete', $task)
            <div class="mt-6 bg-red-50 rounded-xl shadow-sm overflow-hidden border border-red-200">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-red-800 mb-2">Delete Task</h3>
                    <p class="text-sm text-red-600 mb-4">Once deleted, this task cannot be recovered.</p>
                    <form action="{{ route('tasks.destroy', $task) }}" method="POST"
                        id="delete-task-form-{{ $task->id }}">
                        @csrf
                        @method('DELETE')
                        <button type="button"
                            onclick="showDeleteTaskModal('{{ $task->id }}', '{{ addslashes($task->title) }}')"
                            class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                            <i class="fas fa-trash mr-2"></i>Delete Task
                        </button>
                    </form>

                    <!-- Custom Modal -->
                    <div id="deleteTaskModal" class="fixed inset-0 z-50 hidden overflow-y-auto"
                        style="font-family: system-ui;">
                        <div class="flex items-center justify-center min-h-screen px-4">
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                                onclick="closeDeleteModal()"></div>

                            <div
                                class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-auto transform transition-all">
                                <div class="p-6">
                                    <div class="flex items-start">
                                        <div
                                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </div>
                                        <div class="ml-4 mt-2 text-left">
                                            <h3 class="text-lg font-medium text-gray-900">Delete Task?</h3>
                                            <div class="mt-2">
                                                <p class="text-sm text-gray-500">
                                                    Are you sure you want to delete <strong class="text-red-700"
                                                        id="taskTitleDisplay"></strong>?
                                                </p>
                                                <div class="mt-3 p-3 bg-red-50 rounded-md border border-red-200">
                                                    <p class="text-sm font-semibold text-red-800 mb-2">⚠️ This action cannot be
                                                        undone!</p>
                                                    <p class="text-xs text-red-700">Deleting this task will permanently remove:
                                                    </p>
                                                    <ul class="mt-1 text-xs text-red-700 list-disc list-inside">
                                                        <li>Task details and description</li>
                                                        <li>All comments and discussions</li>
                                                        <li>Attached files and documents</li>
                                                        <li>Task history and activity log</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-row-reverse gap-3">
                                    <button type="button" onclick="confirmDeleteTask()"
                                        class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors">
                                        <i class="fas fa-trash mr-1"></i> Yes, Delete Permanently
                                    </button>
                                    <button type="button" onclick="closeDeleteModal()"
                                        class="px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-md border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors">
                                        <i class="fas fa-times mr-1"></i> Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        let currentTaskId = null;

                        function showDeleteTaskModal(taskId, taskTitle) {
                            currentTaskId = taskId;
                            document.getElementById('taskTitleDisplay').textContent = `"${taskTitle}"`;
                            document.getElementById('deleteTaskModal').classList.remove('hidden');

                            // Add animation
                            const modal = document.querySelector('#deleteTaskModal .bg-white');
                            modal.style.transform = 'scale(0.95)';
                            modal.style.opacity = '0';
                            setTimeout(() => {
                                modal.style.transform = 'scale(1)';
                                modal.style.opacity = '1';
                            }, 10);
                        }

                        function closeDeleteModal() {
                            document.getElementById('deleteTaskModal').classList.add('hidden');
                            currentTaskId = null;
                        }

                        function confirmDeleteTask() {
                            if (currentTaskId) {
                                // Optional: Add loading state to button
                                const confirmBtn = event.target;
                                if (confirmBtn) {
                                    confirmBtn.disabled = true;
                                    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Deleting...';
                                }
                                document.getElementById(`delete-task-form-${currentTaskId}`).submit();
                            }
                        }

                        // Close modal on Escape key
                        document.addEventListener('keydown', function(event) {
                            if (event.key === 'Escape') {
                                closeDeleteModal();
                            }
                        });

                        // Close modal when clicking outside
                        document.addEventListener('click', function(event) {
                            const modal = document.getElementById('deleteTaskModal');
                            if (event.target === modal) {
                                closeDeleteModal();
                            }
                        });
                    </script>

                    <style>
                        /* Smooth transition for modal */
                        #deleteTaskModal .bg-white {
                            transition: all 0.2s ease-out;
                        }
                    </style>
                </div>
            </div>
        @endcan
    </div>

    @push('scripts')
        <script>
            // Auto-save draft (optional)
            let autoSaveTimer;
            const form = document.querySelector('form');
            const inputs = form.querySelectorAll('input, select, textarea');

            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    clearTimeout(autoSaveTimer);
                    autoSaveTimer = setTimeout(() => {
                        // Show auto-save indicator
                        let indicator = document.getElementById('autoSaveIndicator');
                        if (!indicator) {
                            indicator = document.createElement('div');
                            indicator.id = 'autoSaveIndicator';
                            indicator.className =
                                'fixed bottom-4 right-4 bg-green-500 text-white px-3 py-1 rounded-lg text-sm opacity-0 transition-opacity';
                            indicator.innerHTML = '<i class="fas fa-save mr-1"></i>Draft saved';
                            document.body.appendChild(indicator);
                        }

                        indicator.style.opacity = '1';
                        setTimeout(() => {
                            indicator.style.opacity = '0';
                        }, 2000);
                    }, 3000);
                });
            });
        </script>
    @endpush
@endsection
