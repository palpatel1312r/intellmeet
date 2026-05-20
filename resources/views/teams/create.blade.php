@extends('layouts.app')

@section('title', 'Create Team')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 text-white">
                <div class="flex items-center">
                    <a href="{{ route('teams.index') }}" class="text-white hover:text-indigo-200 mr-4">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold">Create New Team</h1>
                        <p class="text-indigo-100">Create a team and add members</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('teams.store') }}" method="POST" id="createTeamForm">
                @csrf
                <div class="p-6">
                    <!-- Basic Information -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Basic Information</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Team Name *</label>
                                <input type="text" name="name" required value="{{ old('name') }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="e.g., Product Development">
                                @error('name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Description</label>
                                <textarea name="description" rows="3"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="What does this team do?">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Team Members Section -->
                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-gray-800">Team Members</h2>
                            <button type="button" onclick="addMemberField()"
                                class="text-indigo-600 hover:text-indigo-700 text-sm">
                                <i class="fas fa-plus mr-1"></i> Add Member
                            </button>
                        </div>

                        <div id="members-container">
                            <div class="member-row bg-gray-50 rounded-lg p-4 mb-3">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                        <input type="email" name="members[0][email]"
                                            class="member-email w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            placeholder="member@example.com">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                                        <select name="members[0][role]"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            <option value="member">Member</option>
                                            <option value="admin">Admin</option>
                                        </select>
                                    </div>
                                    <div class="flex items-end">
                                        <button type="button" onclick="removeMemberField(this)"
                                            class="text-red-600 hover:text-red-700 text-sm">
                                            <i class="fas fa-trash mr-1"></i> Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <p class="text-sm text-gray-500 mt-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            You will be automatically added as the team owner. Add other members here.
                        </p>
                    </div>

                    <!-- Team Settings -->
                    <div class="mb-6 bg-gray-50 rounded-lg p-4">
                        <h2 class="text-lg font-semibold text-gray-800 mb-3">Team Settings</h2>
                        <div class="space-y-2">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" name="allow_member_invites" value="1"
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700">Allow members to invite others</span>
                            </label>
                            <div>
                                <label class="block text-sm text-gray-700 mb-1">Default role for new members</label>
                                <select name="default_role"
                                    class="px-3 py-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="member">Member</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-6 py-4 bg-gray-50 border-t flex justify-end space-x-3">
                    <a href="{{ route('teams.index') }}"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-plus mr-2"></i>Create Team
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            let memberCount = 1;

            function addMemberField() {
                const container = document.getElementById('members-container');
                const newRow = document.createElement('div');
                newRow.className = 'member-row bg-gray-50 rounded-lg p-4 mb-3';
                newRow.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" name="members[${memberCount}][email]" 
                            class="member-email w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="member@example.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select name="members[${memberCount}][role]" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="member">Member</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="button" onclick="removeMemberField(this)" 
                            class="text-red-600 hover:text-red-700 text-sm">
                            <i class="fas fa-trash mr-1"></i> Remove
                        </button>
                    </div>
                </div>
            `;
                container.appendChild(newRow);
                memberCount++;
            }

            function removeMemberField(button) {
                const row = button.closest('.member-row');
                if (document.querySelectorAll('.member-row').length > 1) {
                    row.remove();
                } else {
                    alert('You need at least one member row. You can leave it empty if you don\'t want to add members now.');
                }
            }
        </script>
    @endpush
@endsection
