@extends('layouts.app')

@section('title', $team->name . ' - Members')

@section('content')
    <div class="max-w-6xl mx-auto">
        <!-- Add Member Modal (Hidden by default) -->
        <div id="addMemberModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Add Team Member</h3>
                    <button onclick="document.getElementById('addMemberModal').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form action="{{ route('teams.addMember', $team) }}" method="POST">
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
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Position/Title</label>
                        <input type="text" name="position"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="e.g., Software Engineer">
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="document.getElementById('addMemberModal').classList.add('hidden')"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Add Member
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Invite Modal -->
        <div id="inviteModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Invite Team Member</h3>
                    <button onclick="document.getElementById('inviteModal').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600">
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
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="document.getElementById('inviteModal').classList.add('hidden')"
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

        <!-- Main Content -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <a href="{{ route('teams.show', $team) }}" class="text-white hover:text-indigo-200">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Team
                        </a>
                        <h1 class="text-2xl font-bold mt-2">Team Members</h1>
                        <p class="text-indigo-100">{{ $team->name }} • {{ $team->members->count() }} members</p>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="document.getElementById('addMemberModal').classList.remove('hidden')"
                            class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                            <i class="fas fa-user-plus mr-2"></i>Add Member
                        </button>
                        <button onclick="document.getElementById('inviteModal').classList.remove('hidden')"
                            class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                            <i class="fas fa-envelope mr-2"></i>Invite
                        </button>
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="p-4 border-b border-gray-200">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    <input type="text" id="searchMembers" placeholder="Search members..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <!-- Members List -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Member</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Position</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Joined</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($team->members as $member)
                            <tr class="member-row">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <img class="h-10 w-10 rounded-full object-cover"
                                            src="{{ $member->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($member->name) . '&background=6366f1&color=fff' }}"
                                            alt="">
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $member->name }}
                                                @if ($member->id === $team->owner_id)
                                                    <span
                                                        class="ml-2 text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full">Owner</span>
                                                @endif
                                            </div>
                                            <div class="text-sm text-gray-500">{{ $member->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($member->id !== $team->owner_id)
                                        <form action="{{ route('teams.updateMemberRole', [$team, $member]) }}"
                                            method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <select name="role" onchange="this.form.submit()"
                                                class="text-sm border rounded-lg px-3 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                <option value="member"
                                                    {{ $member->pivot->role == 'member' ? 'selected' : '' }}>Member</option>
                                                <option value="admin"
                                                    {{ $member->pivot->role == 'admin' ? 'selected' : '' }}>Admin</option>
                                            </select>
                                        </form>
                                    @else
                                        <span
                                            class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm rounded-full">Owner</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $member->pivot->position ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($member->pivot->joined_at)->format('M d, Y') }}
                                    <br>
                                    <span
                                        class="text-xs">{{ \Carbon\Carbon::parse($member->pivot->joined_at)->diffForHumans() }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($member->id !== $team->owner_id && $member->id !== auth()->id())
                                        <form action="{{ route('teams.removeMember', [$team, $member]) }}" method="POST"
                                            class="inline"
                                            onsubmit="return confirm('Remove {{ $member->name }} from the team?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-user-minus mr-1"></i>Remove
                                            </button>
                                        </form>
                                    @elseif($member->id === auth()->id() && $member->id !== $team->owner_id)
                                        <form action="{{ route('teams.leave', $team) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Leave this team?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-orange-600 hover:text-orange-900">
                                                <i class="fas fa-sign-out-alt mr-1"></i>Leave
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pending Invitations -->
            @if (isset($pendingInvites) && $pendingInvites->count() > 0)
                <div class="border-t border-gray-200">
                    <div class="bg-gray-50 px-6 py-3">
                        <h3 class="text-sm font-semibold text-gray-700">Pending Invitations</h3>
                    </div>
                    <div class="divide-y divide-gray-200">
                        @foreach ($pendingInvites as $invite)
                            <div class="px-6 py-3 flex justify-between items-center">
                                <div>
                                    <p class="text-sm text-gray-800">{{ $invite->email }}</p>
                                    <p class="text-xs text-gray-500">Invited as {{ $invite->role }} • Expires
                                        {{ \Carbon\Carbon::parse($invite->expires_at)->diffForHumans() }}</p>
                                </div>
                                <form action="{{ route('teams.cancelInvite', [$team, $invite->id]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 text-sm hover:text-red-800">Cancel</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            // Search functionality
            document.getElementById('searchMembers').addEventListener('keyup', function() {
                let searchTerm = this.value.toLowerCase();
                let rows = document.querySelectorAll('.member-row');

                rows.forEach(row => {
                    let text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        </script>
    @endpush
@endsection
