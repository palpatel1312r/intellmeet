@extends('layouts.app')

@section('title', $team->name)

@section('content')
    <div class="space-y-6">
        <!-- Back Button -->
        <div>
            <a href="{{ route('teams.index') }}"
                class="inline-flex items-center text-gray-600 hover:text-indigo-600 transition">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Teams
            </a>
        </div>

        <!-- Team Header -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 h-32"></div>
            <div class="px-6 pb-6 -mt-16">
                <div class="flex items-end justify-between flex-wrap gap-4">
                    <div class="flex items-center space-x-4">
                        <div
                            class="w-24 h-24 rounded-full bg-white border-4 border-indigo-600 flex items-center justify-center overflow-hidden">
                            @if ($team->avatar_url)
                                <img src="{{ Storage::url($team->avatar_url) }}" class="w-full h-full object-cover">
                            @else
                                <i class="fas fa-building text-4xl text-indigo-600"></i>
                            @endif
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800">{{ $team->name }}</h1>
                            <p class="text-gray-500 mt-6">
                                <i class="fas fa-user mr-1"></i>Owner: {{ $team->owner->name ?? 'Unknown' }}
                                <span class="mx-2"></span>
                                <i class="fas fa-users mr-1"></i>{{ $team->members->count() }} members

                            </p>
                        </div>
                    </div>

                    <!-- Admin Actions -->
                    <div class="flex space-x-2">
                        @can('update', $team)
                            <a href="{{ route('teams.edit', $team) }}"
                                class="bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600 transition">
                                <i class="fas fa-edit mr-2"></i>Edit Team
                            </a>
                        @endcan

                        @can('delete', $team)
                            <button onclick="confirmDelete()"
                                class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
                                <i class="fas fa-trash mr-2"></i>Delete
                            </button>
                        @endcan

                        @can('invite', $team)
                            <button onclick="openInviteModal()"
                                class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                                <i class="fas fa-user-plus mr-2"></i>Invite Member
                            </button>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Meetings</p>
                        <p class="text-2xl font-bold">{{ $stats['total_meetings'] }}</p>
                    </div>
                    <i class="fas fa-video text-blue-500 text-2xl"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Completed Meetings</p>
                        <p class="text-2xl font-bold">{{ $stats['completed_meetings'] }}</p>
                    </div>
                    <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Tasks</p>
                        <p class="text-2xl font-bold">{{ $stats['total_tasks'] }}</p>
                    </div>
                    <i class="fas fa-tasks text-yellow-500 text-2xl"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Completion Rate</p>
                        <p class="text-2xl font-bold">
                            {{ $stats['total_tasks'] > 0 ? round(($stats['completed_tasks'] / $stats['total_tasks']) * 100) : 0 }}%
                        </p>
                    </div>
                    <i class="fas fa-chart-line text-purple-500 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Description -->
                @if ($team->description)
                    <div class="bg-white rounded-lg p-6 shadow-sm">
                        <h3 class="font-semibold text-gray-800 mb-3">About Team</h3>
                        <p class="text-gray-600">{{ $team->description }}</p>
                    </div>
                @endif

                <!-- Upcoming Meetings -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h3 class="font-semibold text-gray-800">Upcoming Meetings</h3>
                            @if (auth()->user()->role === 'admin')
                                <a href="{{ route('meetings.create', ['team_id' => $team->id]) }}"
                                    class="text-indigo-600 text-sm hover:text-indigo-700">
                                    <i class="fas fa-plus mr-1"></i>Schedule Meeting
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="divide-y divide-gray-200">
                        @forelse($upcomingMeetings as $meeting)
                            <div class="p-4 hover:bg-gray-50">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h4 class="font-medium text-gray-800">{{ $meeting->title }}</h4>
                                        <p class="text-sm text-gray-500 mt-1">
                                            <i class="far fa-calendar-alt mr-1"></i>
                                            {{ \Carbon\Carbon::parse($meeting->start_time)->format('F j, Y g:i A') }}
                                        </p>
                                    </div>
                                    <a href="{{ route('meetings.join', $meeting) }}"
                                        class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                                        Join
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center">
                                <p class="text-gray-500">No upcoming meetings scheduled</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Team Information -->
                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <h3 class="font-semibold text-gray-800 mb-3">Team Information</h3>
                    <div class="space-y-3">
                        <div class="flex items-center text-sm">
                            <i class="fas fa-calendar-alt w-5 text-gray-400"></i>
                            <span>Created {{ $team->created_at->format('M d, Y') }}</span>
                        </div>
                        {{-- @if ($team->website)
                            <div class="flex items-center text-sm">
                                <i class="fas fa-globe w-5 text-gray-400"></i>
                                <a href="{{ $team->website }}" target="_blank"
                                    class="text-indigo-600 hover:underline">{{ $team->website }}</a>
                            </div>
                        @endif
                        @if ($team->location)
                            <div class="flex items-center text-sm">
                                <i class="fas fa-map-marker-alt w-5 text-gray-400"></i>
                                <span>{{ $team->location }}</span>
                            </div>
                        @endif --}}
                    </div>
                </div>

                <!-- Team Members -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h3 class="font-semibold text-gray-800">Team Members ({{ $team->members->count() }})</h3>
                            @can('invite', $team)
                                <button onclick="openInviteModal()" class="text-indigo-600 text-sm hover:text-indigo-700">
                                    <i class="fas fa-plus mr-1"></i>Add Member
                                </button>
                            @endcan
                        </div>
                    </div>
                    <div class="divide-y divide-gray-200">
                        @foreach ($team->members as $member)
                            <div class="p-4 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <img src="{{ $member->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($member->name) . '&background=6366f1&color=fff' }}"
                                            class="w-10 h-10 rounded-full object-cover">
                                        <div>
                                            <p class="font-medium text-gray-800">
                                                {{ $member->name }}
                                                @if ($member->id === $team->owner_id)
                                                    <span
                                                        class="ml-2 text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded">Owner</span>
                                                @elseif($member->pivot->role === 'admin')
                                                    <span
                                                        class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">Admin</span>
                                                @endif
                                            </p>
                                            <p class="text-xs text-gray-500">{{ $member->email }}</p>
                                            @if ($member->pivot->position)
                                                <p class="text-xs text-gray-400 mt-1">{{ $member->pivot->position }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-2">
                                        <!-- Remove Member Button (for owners and admins) -->
                                        @can('removeMember', $team)
                                            @if ($member->id !== $team->owner_id && $member->id !== auth()->id())
                                                <form action="{{ route('teams.removeMember', [$team, $member]) }}"
                                                    method="POST" id="remove-member-form-{{ $member->id }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button"
                                                        onclick="showRemoveMemberConfirm('{{ $member->id }}', '{{ addslashes($member->name) }}', '{{ addslashes($team->name) }}')"
                                                        class="text-red-600 hover:text-red-800 text-sm flex items-center">
                                                        <i class="fas fa-user-minus mr-1"></i> Remove
                                                    </button>
                                                </form>

                                                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                                                <script>
                                                    function showRemoveMemberConfirm(memberId, memberName, teamName) {
                                                        Swal.fire({
                                                            title: 'Remove Team Member?',
                                                            html: `
            <div class="text-left">
                <p class="mb-3">Are you sure you want to remove <strong class="text-red-600">${memberName}</strong> from <strong>${teamName}</strong>?</p>
                <div class="bg-red-50 border-l-4 border-red-400 p-3 my-3">
                    <p class="text-sm text-red-800">
                        <span class="font-semibold">⚠️ Note:</span> This member will:
                    </p>
                    <ul class="mt-1 text-sm text-red-700 list-disc list-inside">
                        <li>Lose access to all team resources</li>
                        <li>No longer receive team notifications</li>
                        <li>Be removed from team collaborations</li>
                    </ul>
                </div>
                <p class="text-xs text-gray-500 mt-2">They can be re-invited to the team at any time.</p>
            </div>
        `,
                                                            icon: 'warning',
                                                            showCancelButton: true,
                                                            confirmButtonColor: '#dc2626',
                                                            cancelButtonColor: '#6b7280',
                                                            confirmButtonText: '<i class="fas fa-user-minus mr-1"></i> Yes, remove member',
                                                            cancelButtonText: '<i class="fas fa-times mr-1"></i> Cancel',
                                                            reverseButtons: true,
                                                            focusCancel: true
                                                        }).then((result) => {
                                                            if (result.isConfirmed) {
                                                                document.getElementById(`remove-member-form-${memberId}`).submit();
                                                            }
                                                        });
                                                    }
                                                </script>
                                            @endif
                                        @endcan

                                        <!-- Leave Team Button (for current user if not owner) -->
                                        @if ($member->id === auth()->id() && $member->id !== $team->owner_id)
                                            <form action="{{ route('teams.leave', $team) }}" method="POST"
                                                onsubmit="return confirm('Are you sure you want to leave this team?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="text-orange-600 hover:text-orange-800 text-sm flex items-center">
                                                    <i class="fas fa-sign-out-alt mr-1"></i> Leave
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invite Member Modal -->
    <div id="inviteModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Invite Team Member</h3>
                <button onclick="closeInviteModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="{{ route('teams.invite', $team) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <input type="email" name="email" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="colleague@example.com">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <select name="role"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="member">Member</option>
                        @can('updateMemberRole', $team)
                            <option value="admin">Admin</option>
                        @endcan
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Position (Optional)</label>
                    <input type="text" name="position"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="e.g., Software Engineer">
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeInviteModal()"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Send Invitation
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Delete Team</h3>
                <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mb-4">
                <p class="text-gray-700">Are you sure you want to delete <strong>"{{ $team->name }}"</strong>?</p>
                <p class="text-sm text-red-600 mt-2">This action cannot be undone. All team data will be permanently
                    deleted.</p>
            </div>
            <div class="flex justify-end space-x-3">
                <button onclick="closeDeleteModal()"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                    Cancel
                </button>
                <form action="{{ route('teams.destroy', $team) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Delete Team
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function openInviteModal() {
                document.getElementById('inviteModal').classList.remove('hidden');
            }

            function closeInviteModal() {
                document.getElementById('inviteModal').classList.add('hidden');
            }

            function confirmDelete() {
                document.getElementById('deleteModal').classList.remove('hidden');
            }

            function closeDeleteModal() {
                document.getElementById('deleteModal').classList.add('hidden');
            }
        </script>
    @endpush
@endsection
