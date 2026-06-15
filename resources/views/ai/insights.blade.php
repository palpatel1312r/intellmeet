@extends('layouts.app')

@section('title', 'AI Insights')

@section('content')
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">AI Meeting Insights</h1>
                <p class="text-gray-500 mt-1">Powered by OpenAI GPT-4</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl p-4 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-purple-100 text-sm">Total Meetings</p>
                        <p class="text-3xl font-bold">{{ $stats['total_meetings'] }}</p>
                    </div>
                    <i class="fas fa-video text-3xl text-purple-200"></i>
                </div>
            </div>

            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-4 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-blue-100 text-sm">AI Processed</p>
                        <p class="text-3xl font-bold">{{ $stats['ai_processed'] }}</p>
                    </div>
                    <i class="fas fa-robot text-3xl text-blue-200"></i>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-4 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-green-100 text-sm">Action Items</p>
                        <p class="text-3xl font-bold">{{ $stats['total_action_items'] }}</p>
                    </div>
                    <i class="fas fa-tasks text-3xl text-green-200"></i>
                </div>
            </div>

            <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl p-4 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-orange-100 text-sm">Completion Rate</p>
                        <p class="text-3xl font-bold">
                            {{ $stats['total_action_items'] > 0 ? round(($stats['completed_action_items'] / $stats['total_action_items']) * 100) : 0 }}%
                        </p>
                    </div>
                    <i class="fas fa-chart-line text-3xl text-orange-200"></i>
                </div>
            </div>
        </div>

        <!-- AI Processing Section -->
        <div class="p-8">
            <div class="p-6 bg-gradient-to-r from-purple-50 to-indigo-50 rounded-xl">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <i class="fas fa-robot text-purple-600 text-2xl mr-3"></i>
                        <h3 class="text-xl font-bold text-gray-800">AI Meeting Intelligence</h3>
                        <span class="ml-3 bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded-full">Powered by OpenAI</span>
                    </div>
                </div>

                @if (isset($meeting) && $meeting->exists)
                    @if (!$meeting->transcript)
                        <div class="flex justify-end mb-4">
                            <div class="flex gap-2">
                                <!-- Upload Recording Button -->
                                <button onclick="document.getElementById('audioUpload').click()"
                                    class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                                    <i class="fas fa-microphone mr-2"></i>Upload Recording
                                </button>
                            </div>
                            
                            <!-- Audio Upload Form - FIXED ROUTE NAME -->
                            <form id="audioUploadForm" action="{{ route('ai.process', $meeting) }}" method="POST" enctype="multipart/form-data" class="hidden">
                                @csrf
                                <input type="file" name="audio" id="audioUpload" accept="audio/*,video/*" onchange="submitAudioForm()">
                            </form>
                        </div>

                        <!-- Loading Indicator -->
                        <div id="loadingIndicator" class="hidden text-center py-6">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
                            <p class="text-gray-500 mt-2">Processing your meeting with AI...</p>
                            <p class="text-sm text-gray-400 mt-1">This may take a few moments depending on the file size</p>
                        </div>
                    @endif

                    @if ($meeting->transcript)
                        <div class="space-y-4">
                            @if ($meeting->summary)
                                <div class="bg-white rounded-lg p-5 shadow-sm">
                                    <h4 class="font-bold text-gray-800 mb-3 flex items-center">
                                        <i class="fas fa-file-alt text-indigo-500 mr-2"></i>
                                        AI Generated Summary
                                    </h4>
                                    <div class="prose max-w-none text-gray-700">
                                        {!! nl2br(e($meeting->summary)) !!}
                                    </div>
                                </div>
                            @endif

                            @if ($meeting->actionItems && $meeting->actionItems->count() > 0)
                                <div class="bg-white rounded-lg p-5 shadow-sm">
                                    <h4 class="font-bold text-gray-800 mb-3 flex items-center">
                                        <i class="fas fa-tasks text-green-500 mr-2"></i>
                                        AI Extracted Action Items
                                        <span class="ml-2 text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">
                                            {{ $meeting->actionItems->count() }} items
                                        </span>
                                    </h4>
                                    <div class="space-y-2">
                                        @foreach ($meeting->actionItems as $item)
                                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                                <div class="flex items-center flex-1">
                                                    <input type="checkbox" class="mr-3 rounded border-gray-300"
                                                        onchange="markComplete({{ $item->id }}, this)"
                                                        {{ $item->status === 'completed' ? 'checked' : '' }}>
                                                    <div>
                                                        <p class="font-medium text-gray-800 {{ $item->status === 'completed' ? 'line-through text-gray-400' : '' }}">
                                                            {{ $item->title }}
                                                        </p>
                                                        @if ($item->assigned_to)
                                                            <p class="text-xs text-gray-500 mt-1">
                                                                <i class="fas fa-user mr-1"></i>Assigned to:
                                                                {{ $item->assignee->name ?? 'Unknown' }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>
                                                <span class="text-xs px-2 py-1 rounded-full 
                                                    @if ($item->priority >= 4) bg-red-100 text-red-800
                                                    @elseif($item->priority >= 3) bg-yellow-100 text-yellow-800
                                                    @else bg-green-100 text-green-800 @endif">
                                                    {{ $item->priority >= 4 ? 'High' : ($item->priority >= 3 ? 'Medium' : 'Low') }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <details class="bg-white rounded-lg p-5 shadow-sm">
                                <summary class="font-semibold text-gray-800 cursor-pointer">
                                    <i class="fas fa-file-text text-gray-500 mr-2"></i>
                                    Full Transcript
                                </summary>
                                <div class="mt-3 p-3 bg-gray-50 rounded-lg max-h-96 overflow-y-auto">
                                    <p class="text-gray-600 whitespace-pre-wrap">{{ $meeting->transcript }}</p>
                                </div>
                            </details>
                        </div>
                    @else
                        <div class="text-center py-6">
                            <i class="fas fa-microphone-alt text-4xl text-gray-400 mb-3"></i>
                            <p class="text-gray-500">Upload a recording to get AI insights for this meeting.</p>
                            <div class="flex justify-center gap-6 mt-3 text-sm text-gray-600">
                                <span><i class="fas fa-file-alt text-indigo-400 mr-1"></i> Summary</span>
                                <span><i class="fas fa-tasks text-green-400 mr-1"></i> Action Items</span>
                                <span><i class="fas fa-chart-line text-purple-400 mr-1"></i> Sentiment Analysis</span>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="text-center py-6">
                        <i class="fas fa-microphone-alt text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-500">Create a meeting first to get AI-powered insights!</p>
                        <div class="flex justify-center gap-6 mt-3 text-sm text-gray-600">
                            <span><i class="fas fa-file-alt text-indigo-400 mr-1"></i> Summary</span>
                            <span><i class="fas fa-tasks text-green-400 mr-1"></i> Action Items</span>
                            <span><i class="fas fa-chart-line text-purple-400 mr-1"></i> Sentiment Analysis</span>
                        </div>
                        <div class="mt-6">
                            <a href="{{ route('meetings.create') }}" class="inline-flex items-center text-purple-600 hover:text-purple-800">
                                <i class="fas fa-plus-circle mr-2"></i> Create a New Meeting
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function submitAudioForm() {
            const fileInput = document.getElementById('audioUpload');
            if (fileInput.files.length > 0) {
                const loadingIndicator = document.getElementById('loadingIndicator');
                if (loadingIndicator) {
                    loadingIndicator.classList.remove('hidden');
                }
                document.getElementById('audioUploadForm').submit();
            }
        }

        function markComplete(id, checkbox) {
            fetch(`/action-items/${id}/toggle`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ status: checkbox.checked ? 'completed' : 'pending' })
            }).catch(error => console.error('Error:', error));
        }
    </script>
@endsection