@extends('layouts.app')

@section('title', 'Meetings')

@section('content')
    <div>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Meetings</h1>
                <p class="text-gray-500 mt-1">Manage and join your meetings</p>
            </div>

            {{-- DEBUG: Show user info --}}
            @auth
                <div class="text-xs text-gray-400 hidden md:block">
                    Logged in as: {{ auth()->user()->name }} (Role: {{ auth()->user()->role }})
                </div>
            @endauth

            @auth
                <a href="{{ route('meetings.create') }}"
                    class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition flex items-center shadow-md hover:shadow-lg">
                    <i class="fas fa-plus mr-2"></i>Create Meeting
                </a>
            @else
                <a href="{{ route('login') }}"
                    class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition flex items-center">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login to Create
                </a>
            @endauth
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
            <div class="flex flex-wrap gap-4">
                <input type="text" id="searchInput" placeholder="Search meetings..."
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <select id="statusFilter"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="all">All Meetings</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="ongoing">Ongoing</option>
                    <option value="ended">Ended</option>
                </select>
                <button onclick="filterMeetings()" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200">
                    <i class="fas fa-filter mr-1"></i>Filter
                </button>
            </div>
        </div>

        <!-- Meetings Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="meetingsGrid">
            @forelse($meetings as $meeting)
                <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition meeting-card"
                    data-status="{{ $meeting->status }}" data-title="{{ strtolower($meeting->title) }}">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="bg-indigo-100 rounded-lg p-3">
                                <i class="fas fa-video text-indigo-600 text-xl"></i>
                            </div>
                            @if ($meeting->status == 'ongoing')
                                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Live</span>
                            @elseif($meeting->status == 'scheduled')
                                <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">Scheduled</span>
                            @else
                                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">Ended</span>
                            @endif
                        </div>

                        <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ $meeting->title }}</h3>
                        <p class="text-gray-500 text-sm mb-4">{{ Str::limit($meeting->description, 80) }}</p>

                        <div class="space-y-2 mb-4">
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="far fa-calendar-alt w-5"></i>
                                <span>{{ \Carbon\Carbon::parse($meeting->start_time)->format('M d, Y') }}</span>
                            </div>
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="far fa-clock w-5"></i>
                                <span>{{ \Carbon\Carbon::parse($meeting->start_time)->format('g:i A') }}</span>
                            </div>
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fas fa-code-branch w-5"></i>
                                <span>Code: {{ $meeting->meeting_code }}</span>
                            </div>
                        </div>

                        <div class="flex space-x-2">
                            @if ($meeting->status == 'ongoing')
                                <a href="{{ route('meetings.join', $meeting) }}"
                                    class="flex-1 bg-green-600 text-white text-center px-3 py-2 rounded-lg hover:bg-green-700 transition">
                                    <i class="fas fa-video mr-1"></i>Join Now
                                </a>
                            @elseif($meeting->status == 'scheduled')
                                <a href="{{ route('meetings.join', $meeting) }}"
                                    class="flex-1 bg-indigo-600 text-white text-center px-3 py-2 rounded-lg hover:bg-indigo-700 transition">
                                    <i class="fas fa-play mr-1"></i>Start
                                </a>
                            @else
                                <a href="{{ route('meetings.show', $meeting) }}"
                                    class="flex-1 bg-gray-600 text-white text-center px-3 py-2 rounded-lg hover:bg-gray-700 transition">
                                    <i class="fas fa-info-circle mr-1"></i>View
                                </a>
                            @endif

                            @if ($meeting->created_by == auth()->id() && $meeting->status != 'ended')
                                <a href="{{ route('meetings.edit', $meeting) }}"
                                    class="bg-gray-200 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-300"
                                    title="Edit Meeting">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif

                            <a href="{{ route('meetings.show', $meeting) }}"
                                class="border border-gray-300 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-50 transition"
                                title="View Details">
                                <i class="fas fa-info-circle"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 bg-white rounded-xl shadow-sm p-12 text-center">
                    <i class="fas fa-video-slash text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No meetings yet</h3>
                    <p class="text-gray-500 mb-4">Get started by creating your first meeting</p>
                    @auth
                        <a href="{{ route('meetings.create') }}"
                            class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 inline-block transition shadow-md hover:shadow-lg">
                            <i class="fas fa-plus mr-2"></i>Create Your First Meeting
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 inline-block transition">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login to Create
                        </a>
                    @endauth
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if (method_exists($meetings, 'links'))
            <div class="mt-6">
                {{ $meetings->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        function filterMeetings() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const cards = document.querySelectorAll('.meeting-card');

            cards.forEach(card => {
                const title = card.getAttribute('data-title');
                const status = card.getAttribute('data-status');

                const matchesSearch = title.includes(searchTerm);
                const matchesStatus = statusFilter === 'all' || status === statusFilter;

                if (matchesSearch && matchesStatus) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Initialize filter on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('searchInput').addEventListener('keyup', filterMeetings);
            document.getElementById('statusFilter').addEventListener('change', filterMeetings);
        });
    </script>
@endpush
