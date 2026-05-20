<!-- resources/views/tasks/show.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-purple-50">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-tasks mr-2 text-indigo-600"></i>
                        Task Details
                    </h1>
                    @if (auth()->user()->role === 'admin' || $task->created_by === auth()->id())
                        <div class="space-x-2">
                            <a href="{{ route('tasks.edit', $task) }}"
                                class="px-3 py-1 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="p-6">
                <!-- Task Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">{{ $task->title }}</h2>
                        <p class="text-gray-600 mt-2">{{ $task->description ?? 'No description provided' }}</p>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="font-medium">Priority:</span>
                            <span
                                class="px-2 py-1 rounded-full text-xs font-semibold
                            @if ($task->priority === 'urgent') bg-red-100 text-red-800
                            @elseif($task->priority === 'high') bg-orange-100 text-orange-800
                            @elseif($task->priority === 'medium') bg-yellow-100 text-yellow-800
                            @else bg-green-100 text-green-800 @endif">
                                {{ ucfirst($task->priority) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Status:</span>
                            <span
                                class="px-2 py-1 rounded-full text-xs font-semibold
                            @if ($task->status === 'done') bg-green-100 text-green-800
                            @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800
                            @elseif($task->status === 'review') bg-purple-100 text-purple-800
                            @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                            </span>
                        </div>
                        @if ($task->due_date)
                            <div class="flex justify-between">
                                <span class="font-medium">Due Date:</span>
                                <span class="{{ $task->isOverdue() ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                                    {{ $task->due_date->format('M d, Y') }}
                                    @if ($task->isOverdue())
                                        <i class="fas fa-exclamation-triangle ml-1"></i>
                                    @endif
                                </span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="font-medium">Assigned To:</span>
                            <span>{{ $task->assignee->name ?? 'Unassigned' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Attachments Section -->
                <div class="border-t pt-6 mt-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">
                            <i class="fas fa-paperclip mr-2 text-indigo-600"></i>
                            Attachments ({{ $task->attachments->count() }})
                        </h3>

                        @if (auth()->user()->role === 'admin' || $task->created_by === auth()->id() || $task->assigned_to === auth()->id())
                            <button onclick="showUploadModal()"
                                class="px-3 py-1 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
                                <i class="fas fa-upload"></i> Upload File
                            </button>
                        @endif
                    </div>

                    @if ($task->attachments->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($task->attachments as $attachment)
                                <div class="border rounded-lg p-3 hover:shadow-md transition">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <i class="fas {{ $attachment->file_icon }} text-2xl"></i>
                                            <div>
                                                <p class="text-sm font-medium text-gray-800">
                                                    {{ $attachment->original_filename }}</p>
                                                <p class="text-xs text-gray-500">{{ $attachment->file_size_formatted }} •
                                                    Uploaded by {{ $attachment->uploader->name }} •
                                                    {{ $attachment->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                        <div class="flex space-x-2">
                                            <a href="{{ route('tasks.download-attachment', [$task, $attachment]) }}"
                                                class="text-blue-500 hover:text-blue-700">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            @if (auth()->user()->role === 'admin' || $attachment->uploaded_by === auth()->id())
                                                <form action="{{ route('tasks.delete-attachment', [$task, $attachment]) }}"
                                                    method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        onclick="return confirm('Are you sure you want to delete this file?')"
                                                        class="text-red-500 hover:text-red-700">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                    @if ($attachment->description)
                                        <p class="text-xs text-gray-600 mt-2">{{ $attachment->description }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 bg-gray-50 rounded-lg">
                            <i class="fas fa-paperclip text-gray-400 text-4xl mb-2"></i>
                            <p class="text-gray-500">No attachments yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div id="uploadModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Upload File</h3>
                <form action="{{ route('tasks.upload-attachment', $task) }}" method="POST" enctype="multipart/form-data"
                    id="uploadForm">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select File</label>
                        <input type="file" name="attachment" required
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif" class="w-full">
                        <p class="text-xs text-gray-500 mt-1">Max file size: 10MB</p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                        <textarea name="description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="closeUploadModal()"
                            class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showUploadModal() {
            document.getElementById('uploadModal').classList.remove('hidden');
        }

        function closeUploadModal() {
            document.getElementById('uploadModal').classList.add('hidden');
        }
    </script>
@endsection
