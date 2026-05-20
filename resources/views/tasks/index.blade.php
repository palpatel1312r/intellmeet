@extends('layouts.app')

@section('title', 'Tasks')

@section('content')
    <div class="space-y-6">

        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Tasks</h1>
                <p class="text-gray-500 mt-1">Manage and track your tasks</p>
            </div>

            <!-- Only show "New Task" button for admins -->
            @if (auth()->user()->role === 'admin')
                <a href="{{ route('tasks.create') }}"
                    class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-plus mr-2"></i>New Task
                </a>
            @endif
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <p class="text-gray-500 text-sm">Total</p>
                <p class="text-2xl font-bold">{{ $stats['total'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <p class="text-gray-500 text-sm">In Progress</p>
                <p class="text-2xl font-bold">{{ $stats['in_progress'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <p class="text-gray-500 text-sm">Completed</p>
                <p class="text-2xl font-bold">{{ $stats['completed'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <p class="text-gray-500 text-sm">Overdue</p>
                <p class="text-2xl font-bold text-red-600">{{ $stats['overdue'] ?? 0 }}</p>
            </div>
        </div>

        <!-- Task Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($tasks ?? [] as $task)
                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition">
                    <!-- Gradient Header -->
                    <div class="h-20 bg-gradient-to-r from-indigo-500 to-purple-600"></div>

                    <!-- Content -->
                    <div class="p-5 text-center">
                        <!-- Icon -->
                        <div class="w-16 h-16 mx-auto -mt-10 bg-white rounded-full flex items-center justify-center shadow">
                            <i class="fas fa-tasks text-indigo-600 text-xl"></i>
                        </div>

                        <!-- Title -->
                        <h3 class="mt-3 text-lg font-semibold text-gray-800">
                            {{ $task->title }}
                        </h3>

                        <!-- Status -->
                        <p class="text-sm text-gray-500">
                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                        </p>

                        <!-- Description -->
                        <p class="text-xs text-gray-400 mt-2">
                            {{ Str::limit($task->description, 60) }}
                        </p>

                        <!-- Meta -->
                        <div class="flex justify-center gap-6 mt-4 text-sm">
                            <div>
                                <p class="font-bold text-gray-800">
                                    {{ $task->assignee ? 1 : 0 }}
                                </p>
                                <p class="text-gray-500">Assigned</p>
                            </div>
                            <div>
                                <p class="font-bold text-gray-800">
                                    {{ $task->due_date ? $task->due_date->format('d') : '-' }}
                                </p>
                                <p class="text-gray-500">Due</p>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="mt-4 flex justify-center gap-2">
                            <a href="{{ route('tasks.show', $task) }}"
                                class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">
                                View
                            </a>

                            <!-- Edit & Delete buttons - Admin only -->
                            @if (auth()->user()->role === 'admin')
                                <a href="{{ route('tasks.edit', $task) }}"
                                    class="bg-gray-200 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-300">
                                    <i class="fas fa-cog"></i>
                                </a>

                                <button type="button" onclick="openDeleteModal({{ $task->id }})"
                                    class="bg-red-100 text-red-600 px-3 py-2 rounded-lg hover:bg-red-200">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Delete Modal for this task (outside the card) -->
                @if (auth()->user()->role === 'admin')
                    <div id="deleteModal{{ $task->id }}"
                        class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Confirm Delete</h3>
                                <button onclick="closeDeleteModal({{ $task->id }})"
                                    class="text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="mb-4">
                                <p class="text-gray-700">Are you sure you want to delete the task
                                    <strong>"{{ $task->title }}"</strong>?
                                </p>
                                <p class="text-red-600 text-sm mt-2">This action cannot be undone!</p>
                            </div>
                            <div class="flex justify-end space-x-3">
                                <button type="button" onclick="closeDeleteModal({{ $task->id }})"
                                    class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300">
                                    Cancel
                                </button>
                                <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                        Delete Task
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            @empty
                <div class="col-span-3 text-center p-12">
                    <i class="fas fa-tasks text-5xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No tasks yet</h3>
                    @if (auth()->user()->role === 'admin')
                        <a href="{{ route('tasks.create') }}" class="bg-indigo-600 text-white px-6 py-2 rounded-lg">
                            Create Task
                        </a>
                    @else
                        <p class="text-gray-500">Tasks will appear here once created by an administrator.</p>
                    @endif
                </div>
            @endforelse
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openDeleteModal(taskId) {
            const modal = document.getElementById('deleteModal' + taskId);
            if (modal) {
                modal.classList.remove('hidden');
                // Prevent body scroll when modal is open
                document.body.style.overflow = 'hidden';
            }
        }

        function closeDeleteModal(taskId) {
            const modal = document.getElementById('deleteModal' + taskId);
            if (modal) {
                modal.classList.add('hidden');
                // Restore body scroll
                document.body.style.overflow = 'auto';
            }
        }

        // Close modal when clicking outside (optional)
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('fixed') && event.target.classList.contains('inset-0')) {
                const modals = document.querySelectorAll('[id^="deleteModal"]');
                modals.forEach(modal => {
                    if (!modal.classList.contains('hidden')) {
                        modal.classList.add('hidden');
                        document.body.style.overflow = 'auto';
                    }
                });
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modals = document.querySelectorAll('[id^="deleteModal"]');
                modals.forEach(modal => {
                    if (!modal.classList.contains('hidden')) {
                        modal.classList.add('hidden');
                        document.body.style.overflow = 'auto';
                    }
                });
            }
        });
    </script>
@endpush
