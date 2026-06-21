@extends('layouts.app')

@section('title', 'Create Meeting')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm p-8">
            <div class="flex items-center mb-6">
                <a href="{{ route('meetings.index') }}" class="text-gray-500 hover:text-gray-700 mr-4">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <h1 class="text-2xl font-bold text-gray-800">Create New Meeting</h1>
            </div>

            <form action="{{ route('meetings.store') }}" method="POST">
                @csrf

                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">Meeting Title *</label>
                    <input type="text" name="title" required value="{{ old('title') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="e.g., Weekly Team Sync">
                    @error('title')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">Description</label>
                    <textarea name="description" rows="4"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="What will this meeting cover?">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Date *</label>
                        <input type="date" name="date" required value="{{ old('date') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Time *</label>
                        <input type="time" name="time" required value="{{ old('time') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Team Selection -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">Team (Optional)</label>
                    <select name="team_id" id="teamSelect"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">No Team (Personal Meeting)</option>
                        @foreach ($teams ?? [] as $team)
                            <option value="{{ $team->id }}" {{ old('team_id') == $team->id ? 'selected' : '' }}>
                                {{ $team->name }} ({{ $team->members->count() }} members)
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Team Members Preview -->
                <div id="teamMembersPreview" class="mb-6 hidden">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-800 text-sm mb-3">
                            <i class="fas fa-users mr-2 text-indigo-500"></i>
                            Team Members
                        </h4>
                        <div id="teamMembersList" class="flex flex-wrap gap-2">
                            <!-- Dynamically loaded team members -->
                        </div>
                    </div>
                </div>

                <div class="bg-indigo-50 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <i class="fas fa-microphone-alt text-indigo-600 mt-1 mr-3"></i>
                        <div>
                            <h4 class="font-semibold text-gray-800">AI-Powered Meeting</h4>
                            <p class="text-sm text-gray-600">This meeting will be automatically transcribed and summarized
                                by AI</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="{{ route('meetings.index') }}"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-calendar-plus mr-2"></i>Create Meeting
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            const teamSelect = document.getElementById('teamSelect');
            const teamPreview = document.getElementById('teamMembersPreview');
            const teamMembersList = document.getElementById('teamMembersList');

            // Store teams data
            const teams = @json($teams ?? []);

            teamSelect.addEventListener('change', function() {
                const teamId = this.value;

                if (teamId) {
                    // Find selected team and show members
                    const selectedTeam = teams.find(t => t.id == teamId);
                    if (selectedTeam && selectedTeam.members) {
                        teamPreview.classList.remove('hidden');
                        displayTeamMembers(selectedTeam.members);
                    } else {
                        // Fetch team members via AJAX if not loaded
                        fetchTeamMembers(teamId);
                    }
                } else {
                    teamPreview.classList.add('hidden');
                }
            });

            function displayTeamMembers(members) {
                if (!members || members.length === 0) {
                    teamMembersList.innerHTML = '<span class="text-gray-500 text-sm">No members in this team</span>';
                    return;
                }

                let html = '';
                members.forEach(member => {
                    html += `
                    <div class="flex items-center bg-white rounded-full px-3 py-1 shadow-sm">
                        <img src="${member.avatar_url || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(member.name) + '&background=6366f1&color=fff'}" 
                             class="w-5 h-5 rounded-full mr-2 object-cover">
                        <span class="text-xs text-gray-700">${member.name}</span>
                    </div>
                `;
                });
                teamMembersList.innerHTML = html;
            }

            async function fetchTeamMembers(teamId) {
                try {
                    const response = await fetch(`/teams/${teamId}/members-json`);
                    const data = await response.json();
                    if (data.members) {
                        displayTeamMembers(data.members);
                    }
                } catch (error) {
                    console.error('Error fetching team members:', error);
                    teamMembersList.innerHTML = '<span class="text-gray-500 text-sm">Unable to load members</span>';
                }
            }

            // If team was pre-selected (e.g., after form validation error)
            if (teamSelect.value) {
                teamSelect.dispatchEvent(new Event('change'));
            }
        </script>
    @endpush
@endsection
