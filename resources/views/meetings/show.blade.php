@extends('layouts.app')

@section('title', $meeting->title)

@section('content')
    <div class="max-w-4xl mx-auto">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('meetings.index') }}"
                class="inline-flex items-center text-gray-600 hover:text-indigo-600 transition">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Meetings
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-8 text-white">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-3xl font-bold mb-2">{{ $meeting->title }}</h1>
                        <p class="text-indigo-100">Created by {{ $meeting->creator->name ?? 'You' }}</p>
                    </div>
                    <div class="flex space-x-2">
                        @if ($meeting->status != 'ended')
                            <a href="{{ route('meetings.video-room', $meeting) }}"
                                class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition font-semibold">
                                <i class="fas fa-video mr-2"></i>Join Meeting
                            </a>
                        @endif

                        @if ($meeting->created_by == auth()->id() || auth()->user()->role === 'admin')
                            @if ($meeting->status != 'ended')
                                <a href="{{ route('meetings.edit', $meeting) }}"
                                    class="bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600 transition">
                                    <i class="fas fa-edit mr-2"></i>Edit
                                </a>

                                <!-- End Meeting Button -->
                                <button type="button"
                                    onclick="showEndMeetingModal('{{ $meeting->id }}', '{{ $meeting->title }}')"
                                    class="bg-red-600 hover:bg-red-700 text-white px-6 py-2.5 rounded-lg transition duration-200 flex items-center space-x-2 shadow-md hover:shadow-lg transform hover:scale-105 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                                    </svg>
                                    <span>End Meeting</span>
                                </button>

                                <!-- End Meeting Modal -->
                                <div id="endMeetingModal" class="fixed inset-0 z-50 hidden overflow-y-auto"
                                    style="font-family: system-ui;">
                                    <div
                                        class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                        <!-- Background overlay -->
                                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                                            onclick="closeEndMeetingModal()"></div>

                                        <!-- Modal panel -->
                                        <div
                                            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                <div class="sm:flex sm:items-start">
                                                    <!-- Warning Icon -->
                                                    <div
                                                        class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                                        <svg class="h-6 w-6 text-red-600" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                        </svg>
                                                    </div>

                                                    <!-- Modal Content -->
                                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                        <h3 class="text-lg leading-6 font-medium text-gray-900"
                                                            id="modalTitle">
                                                            End Meeting?
                                                        </h3>
                                                        <div class="mt-2">
                                                            <p class="text-sm text-gray-500" id="modalMessage">
                                                                Are you sure you want to end <strong
                                                                    id="meetingTitle"></strong>?
                                                            </p>

                                                            <!-- Information Box -->
                                                            <div
                                                                class="mt-4 p-3 bg-yellow-50 rounded-md border border-yellow-100">
                                                                <div class="flex items-start">
                                                                    <svg class="h-5 w-5 text-yellow-400 mt-0.5"
                                                                        fill="none" stroke="currentColor"
                                                                        viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                                            stroke-width="2"
                                                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                    </svg>
                                                                    <div class="ml-2">
                                                                        <p class="text-xs text-yellow-800 font-semibold">
                                                                            What happens when you end a meeting:</p>
                                                                        <ul
                                                                            class="mt-1 text-xs text-yellow-700 list-disc list-inside space-y-0.5">
                                                                            <li>All participants will be disconnected</li>
                                                                            <li>The meeting will be archived</li>
                                                                            <li>No one can join this meeting again</li>
                                                                            <li>Meeting recording and chat will be saved
                                                                            </li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Warning for ongoing meeting -->
                                                            <div id="ongoingWarning"
                                                                class="mt-3 p-3 bg-red-50 rounded-md border border-red-100 hidden">
                                                                <div class="flex items-start">
                                                                    <svg class="h-5 w-5 text-red-400 mt-0.5" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                                            stroke-width="2"
                                                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                    </svg>
                                                                    <div class="ml-2">
                                                                        <p class="text-xs text-red-800">
                                                                            <span class="font-semibold">Warning:</span>
                                                                            There are currently <span
                                                                                id="activeParticipants">0</span> active
                                                                            participant(s) in this meeting.
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Modal Buttons -->
                                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                                <form id="endMeetingForm" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" id="confirmEndBtn"
                                                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition duration-200">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                                                        </svg>
                                                        End Meeting
                                                    </button>
                                                </form>
                                                <button type="button" onclick="closeEndMeetingModal()"
                                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition duration-200">
                                                    Cancel
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Loading Overlay -->
                                <div id="loadingOverlay"
                                    class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 backdrop-blur-sm">
                                    <div class="flex items-center justify-center min-h-screen">
                                        <div class="bg-white rounded-lg p-6 shadow-xl text-center">
                                            <div class="inline-block">
                                                <svg class="animate-spin h-10 w-10 text-red-600 mx-auto"
                                                    xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                    </path>
                                                </svg>
                                            </div>
                                            <p class="mt-3 text-gray-600 font-medium">Ending meeting...</p>
                                            <p class="text-xs text-gray-400 mt-1">Please wait</p>
                                        </div>
                                    </div>
                                </div>

                                <script>
                                    let currentMeetingId = null;
                                    let currentMeetingTitle = null;

                                    function showEndMeetingModal(meetingId, meetingTitle) {
                                        currentMeetingId = meetingId;
                                        currentMeetingTitle = meetingTitle;

                                        // Set meeting title in modal
                                        document.getElementById('meetingTitle').textContent = meetingTitle;
                                        document.getElementById('modalMessage').innerHTML =
                                            `Are you sure you want to end <strong class="text-red-600">${meetingTitle}</strong>?`;

                                        // Set form action
                                        const form = document.getElementById('endMeetingForm');
                                        form.action = `/meetings/end/${meetingId}`;

                                        // Check for active participants (optional)
                                        checkActiveParticipants(meetingId);

                                        // Show modal with animation
                                        const modal = document.getElementById('endMeetingModal');
                                        modal.classList.remove('hidden');
                                        modal.style.animation = 'modalSlideIn 0.3s ease-out';

                                        // Add body class to prevent scrolling
                                        document.body.style.overflow = 'hidden';
                                    }

                                    function closeEndMeetingModal() {
                                        const modal = document.getElementById('endMeetingModal');
                                        modal.classList.add('hidden');
                                        document.body.style.overflow = '';
                                        currentMeetingId = null;
                                        currentMeetingTitle = null;
                                    }

                                    // Optional: Check for active participants in the meeting
                                    function checkActiveParticipants(meetingId) {
                                        // This is optional - you can implement an AJAX call to check active participants
                                        // For now, it's hidden by default
                                        /*
                                        fetch(`/meetings/${meetingId}/active-participants`)
                                            .then(response => response.json())
                                            .then(data => {
                                                if (data.count > 0) {
                                                    document.getElementById('activeParticipants').textContent = data.count;
                                                    document.getElementById('ongoingWarning').classList.remove('hidden');
                                                }
                                            });
                                        */
                                    }

                                    // Add loading state when form is submitted
                                    document.getElementById('endMeetingForm').addEventListener('submit', function(e) {
                                        const confirmBtn = document.getElementById('confirmEndBtn');

                                        // Show loading overlay
                                        const loadingOverlay = document.getElementById('loadingOverlay');
                                        loadingOverlay.classList.remove('hidden');

                                        // Disable button and change text
                                        confirmBtn.disabled = true;
                                        confirmBtn.innerHTML = `
            <svg class="animate-spin w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Ending...
        `;

                                        // Close modal
                                        closeEndMeetingModal();
                                    });

                                    // Close modal on escape key
                                    document.addEventListener('keydown', function(event) {
                                        if (event.key === 'Escape') {
                                            closeEndMeetingModal();
                                        }
                                    });

                                    // Add animation CSS
                                    const style = document.createElement('style');
                                    style.textContent = `
        @keyframes modalSlideIn {
            from {
                transform: translateY(-30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .animate-spin {
            animation: spin 1s linear infinite;
        }
    `;
                                    document.head.appendChild(style);
                                </script>
                            @endif

                            <button onclick="confirmDelete()"
                                class="bg-red-700 text-white px-4 py-2 rounded-lg hover:bg-red-800 transition">
                                <i class="fas fa-trash mr-2"></i>Delete
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Meeting Info -->
            <div class="p-8 border-b border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <div class="flex items-center text-gray-500 mb-2">
                            <i class="far fa-calendar-alt w-5"></i>
                            <span class="text-sm">Date</span>
                        </div>
                        <p class="font-semibold">{{ \Carbon\Carbon::parse($meeting->start_time)->format('F j, Y') }}</p>
                    </div>
                    <div>
                        <div class="flex items-center text-gray-500 mb-2">
                            <i class="far fa-clock w-5"></i>
                            <span class="text-sm">Time</span>
                        </div>
                        <p class="font-semibold">{{ \Carbon\Carbon::parse($meeting->start_time)->format('g:i A') }}</p>
                    </div>
                    <div>
                        <div class="flex items-center text-gray-500 mb-2">
                            <i class="fas fa-code-branch w-5"></i>
                            <span class="text-sm">Meeting Code</span>
                        </div>
                        <div class="flex items-center">
                            <p class="font-mono font-semibold">{{ $meeting->meeting_code }}</p>
                            <button onclick="copyToClipboard('{{ $meeting->meeting_code }}')"
                                class="ml-2 text-indigo-600 hover:text-indigo-700">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>

                @if ($meeting->description)
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="font-semibold text-gray-800 mb-2">Description</h3>
                        <p class="text-gray-600">{{ $meeting->description }}</p>
                    </div>
                @endif
            </div>

            <!-- Share Meeting Section -->
            <div class="p-8 border-b border-gray-200 bg-gradient-to-r from-green-50 to-teal-50">
                <div class="flex items-center mb-4">
                    <i class="fas fa-share-alt text-green-600 text-2xl mr-3"></i>
                    <h3 class="text-xl font-bold text-gray-800">Share Meeting</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Join Link</label>
                        <div class="flex items-center">
                            <input type="text" id="joinLink"
                                value="{{ route('meetings.join.by-code', $meeting->meeting_code) }}"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-l-lg bg-gray-50 text-sm" readonly>
                            <button onclick="copyJoinLink()"
                                class="bg-indigo-600 text-white px-4 py-2 rounded-r-lg hover:bg-indigo-700">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Quick Share</label>
                        <div class="flex space-x-2">
                            <button onclick="shareViaWhatsApp()"
                                class="flex-1 bg-green-500 text-white px-3 py-2 rounded-lg hover:bg-green-600">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </button>
                            <button onclick="shareViaLinkedIn()"
                                class="flex-1 bg-blue-700 text-white px-3 py-2 rounded-lg hover:bg-blue-800">
                                <i class="fab fa-linkedin"></i> LinkedIn
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Participants -->
            <div class="p-8 border-b border-gray-200">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold text-gray-800">Participants ({{ $meeting->participants->count() }})</h3>
                    @if ($meeting->status != 'ended')
                        <button onclick="copyJoinLink()" class="text-indigo-600 text-sm hover:text-indigo-700">
                            <i class="fas fa-user-plus mr-1"></i> Invite Others
                        </button>
                    @endif
                </div>
                <div class="flex flex-wrap gap-3">
                    @forelse($meeting->participants as $participant)
                        <div class="flex items-center bg-gray-100 rounded-full px-4 py-2">
                            <img src="{{ $participant->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($participant->name) . '&background=6366f1&color=fff' }}"
                                class="w-6 h-6 rounded-full mr-2 object-cover" alt="">
                            <span class="text-sm">{{ $participant->name }}</span>
                            @if ($participant->id == $meeting->created_by)
                                <span
                                    class="ml-2 text-xs bg-yellow-200 text-yellow-800 px-2 py-0.5 rounded-full">Host</span>
                            @endif
                        </div>
                    @empty
                        <p class="text-gray-500">No participants yet. Share the meeting link to invite others.</p>
                    @endforelse
                </div>
            </div>

            <!-- Add Participant Form -->
            @if ($meeting->created_by == auth()->id())
                <div class="p-8 border-b border-gray-200">
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <h4 class="font-semibold text-gray-800 mb-2">Add Participant</h4>
                        <form action="{{ route('meetings.add-participant', $meeting) }}" method="POST"
                            class="flex gap-2">
                            @csrf
                            <input type="email" name="email" placeholder="Enter email address" required
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <button type="submit"
                                class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                                <i class="fas fa-user-plus mr-1"></i>Add
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <!-- AI Processing Section -->
            <div class="p-8">
                <div class="p-6 bg-gradient-to-r from-purple-50 to-indigo-50 rounded-xl">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-robot text-purple-600 text-2xl mr-3"></i>
                            <h3 class="text-xl font-bold text-gray-800">AI Meeting Intelligence</h3>
                            <span class="ml-3 bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded-full">Powered by
                                OpenAI</span>
                        </div>

                        @if (!$meeting->transcript && ($meeting->created_by == auth()->id() || auth()->user()->role === 'admin'))
                            <div class="flex gap-2">
                                <button onclick="document.getElementById('audioUpload').click()"
                                    class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                                    <i class="fas fa-microphone mr-2"></i>Upload Recording
                                </button>
                                {{-- <button onclick="openTextModal()"
                                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                                    <i class="fas fa-file-alt mr-2"></i>Enter Transcript
                                </button> --}}
                            </div>
                            <form action="{{ route('ai.process', $meeting) }}" method="POST"
                                enctype="multipart/form-data" class="hidden">
                                @csrf
                                <input type="file" name="audio" id="audioUpload" accept="audio/*,video/*"
                                    onchange="this.form.submit()">
                            </form>
                        @endif
                    </div>

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
                                            <div
                                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                                <div class="flex items-center flex-1">
                                                    <input type="checkbox" class="mr-3 rounded border-gray-300"
                                                        onchange="markComplete({{ $item->id }}, this)"
                                                        {{ $item->status === 'completed' ? 'checked' : '' }}>
                                                    <div>
                                                        <p
                                                            class="font-medium text-gray-800 {{ $item->status === 'completed' ? 'line-through text-gray-400' : '' }}">
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
                                                <span
                                                    class="text-xs px-2 py-1 rounded-full 
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
                            <p class="text-gray-500">Upload a recording or paste a transcript to get:</p>
                            <div class="flex justify-center gap-6 mt-3 text-sm text-gray-600">
                                <span><i class="fas fa-file-alt text-indigo-400 mr-1"></i> Summary</span>
                                <span><i class="fas fa-tasks text-green-400 mr-1"></i> Action Items</span>
                                <span><i class="fas fa-chart-line text-purple-400 mr-1"></i> Sentiment Analysis</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Text Transcript Modal -->
        <div id="textModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Enter Meeting Transcript</h3>
                    <button onclick="closeTextModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form action="{{ route('ai.process-text', $meeting) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <textarea name="transcript" rows="10" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Paste your meeting transcript here..."></textarea>
                        <p class="text-xs text-gray-500 mt-1">Minimum 50 characters for accurate AI analysis</p>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeTextModal()"
                            class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                            Process with AI
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div id="deleteModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Delete Meeting</h3>
                    <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <p class="text-gray-700">Are you sure you want to delete <strong>"{{ $meeting->title }}"</strong>?</p>
                    <p class="text-sm text-red-600 mt-2">This action cannot be undone. All meeting data will be permanently
                        deleted.</p>
                </div>
                <div class="flex justify-end space-x-3">
                    <button onclick="closeDeleteModal()"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        Cancel
                    </button>
                    <form action="{{ route('meetings.destroy', $meeting) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            Delete Meeting
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text);
            showNotification('Meeting code copied!');
        }

        function copyJoinLink() {
            const link = document.getElementById('joinLink');
            link.select();
            navigator.clipboard.writeText(link.value);
            showNotification('Join link copied!');
        }

        function shareViaWhatsApp() {
            const link = '{{ route('meetings.join.by-code', $meeting->meeting_code) }}';
            window.open(`https://wa.me/?text=Join%20my%20meeting%3A%20${encodeURIComponent(link)}`, '_blank');
        }

        function shareViaLinkedIn() {
            const link = '{{ route('meetings.join.by-code', $meeting->meeting_code) }}';
            window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(link)}`, '_blank');
        }

        function confirmDelete() {
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        function openTextModal() {
            document.getElementById('textModal').classList.remove('hidden');
        }

        function closeTextModal() {
            document.getElementById('textModal').classList.add('hidden');
        }

        function markComplete(itemId, checkbox) {
            fetch(`/action-items/${itemId}/complete`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    }
                }).then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const parent = checkbox.closest('.flex');
                        const textElement = parent.querySelector('p');
                        if (checkbox.checked) {
                            textElement.classList.add('line-through', 'text-gray-400');
                        } else {
                            textElement.classList.remove('line-through', 'text-gray-400');
                        }
                        showNotification('Action item updated!');
                    }
                });
        }

        function showNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
            notification.innerHTML = '<i class="fas fa-check mr-2"></i>' + message;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 2000);
        }
    </script>
@endpush
