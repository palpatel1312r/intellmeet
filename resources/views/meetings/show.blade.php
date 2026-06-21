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
                        <p class="text-indigo-100">Created by {{ $meeting->creator->name ?? 'Unknown' }}</p>
                        <p class="text-indigo-200 text-sm mt-1">
                            <i class="fas fa-code mr-1"></i> Code: {{ $meeting->meeting_code }}
                        </p>
                    </div>
                    <div class="flex space-x-2">
                        @if ($meeting->status != 'ended')
                            <a href="{{ route('meetings.video-room', $meeting) }}"
                                class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition font-semibold">
                                <i class="fas fa-video mr-2"></i>Join Meeting
                            </a>
                        @endif

                        {{-- ONLY the creator can see these buttons --}}
                        @if ($meeting->created_by == auth()->id())
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

            <!-- Add Participant Form - ONLY CREATOR -->
            @if ($meeting->created_by == auth()->id() && $meeting->status != 'ended')
                <div class="p-8 border-b border-gray-200">
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <h4 class="font-semibold text-gray-800 mb-2">Add Participant</h4>
                        <form action="{{ route('meetings.add-participant', $meeting) }}" method="POST" class="flex gap-2">
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

            <!-- End Meeting Modal -->
            <div id="endMeetingModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
                <!-- Same modal content as before -->
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                        onclick="closeEndMeetingModal()"></div>
                    <div
                        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">End Meeting?</h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">Are you sure you want to end
                                            <strong>{{ $meeting->title }}</strong>?</p>
                                        <div class="mt-4 p-3 bg-yellow-50 rounded-md border border-yellow-100">
                                            <p class="text-xs text-yellow-800">All participants will be disconnected and
                                                the meeting will be archived.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <form action="{{ route('meetings.end', $meeting) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                    End Meeting
                                </button>
                            </form>
                            <button type="button" onclick="closeEndMeetingModal()"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delete Confirmation Modal -->
            <div id="deleteModal"
                class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Delete Meeting</h3>
                        <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="mb-4">
                        <p class="text-gray-700">Are you sure you want to delete <strong>"{{ $meeting->title }}"</strong>?
                        </p>
                        <p class="text-sm text-red-600 mt-2">This action cannot be undone. All meeting data will be
                            permanently deleted.</p>
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
    </div>
@endsection

@push('scripts')
    <script>
        function showEndMeetingModal() {
            document.getElementById('endMeetingModal').classList.remove('hidden');
        }

        function closeEndMeetingModal() {
            document.getElementById('endMeetingModal').classList.add('hidden');
        }

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

        function showNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
            notification.innerHTML = '<i class="fas fa-check mr-2"></i>' + message;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 2000);
        }
    </script>
@endpush
