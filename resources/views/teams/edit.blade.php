@extends('layouts.app')

@section('title', 'Edit ' . $team->name)

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 text-white">
                <div class="flex items-center">
                    <a href="{{ route('teams.show', $team) }}" class="text-white hover:text-indigo-200 mr-4">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold">Edit Team</h1>
                        <p class="text-indigo-100">Update your team information</p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form action="{{ route('teams.update', $team) }}" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                @method('PUT')

                <!-- Team Avatar -->
                {{-- <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">Team Avatar</label>
                    <div class="flex items-center space-x-4">
                        <div class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden">
                            @if ($team->avatar_url)
                                <img src="{{ Storage::url($team->avatar_url) }}" class="w-full h-full object-cover">
                            @else
                                <i class="fas fa-building text-3xl text-gray-400"></i>
                            @endif
                        </div>
                        <div>
                            <input type="file" name="avatar" accept="image/*" class="text-sm text-gray-500">
                            <p class="text-xs text-gray-400 mt-1">Recommended: Square image, max 2MB</p>
                        </div>
                    </div>
                </div> --}}

                <!-- Team Name -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Team Name *</label>
                    <input type="text" name="name" required value="{{ old('name', $team->name) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Description</label>
                    <textarea name="description" rows="4"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="What does your team do?">{{ old('description', $team->description) }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- <div class="grid grid-cols-2 gap-4 mb-4">
                    <!-- Website -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Website</label>
                        <input type="url" name="website" value="{{ old('website', $team->website) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="https://example.com">
                    </div>

                    <!-- Location -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Location</label>
                        <input type="text" name="location" value="{{ old('location', $team->location) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="City, Country">
                    </div>
                </div> --}}

                <!-- Team Settings -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <h3 class="font-semibold text-gray-800 mb-3">Team Settings</h3>
                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="checkbox" name="allow_member_invites"
                                {{ $team->settings['allow_member_invites'] ?? false ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-600">
                            <span class="ml-2 text-sm text-gray-700">Allow members to invite others</span>
                        </label>
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Default role for new members</label>
                            <select name="default_role"
                                class="border rounded-lg px-3 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="member"
                                    {{ ($team->settings['default_role'] ?? 'member') == 'member' ? 'selected' : '' }}>
                                    Member</option>
                                <option value="admin"
                                    {{ ($team->settings['default_role'] ?? 'member') == 'admin' ? 'selected' : '' }}>Admin
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <a href="{{ route('teams.show', $team) }}"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-save mr-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Danger Zone (for owners only) -->
        @if ($team->owner_id === auth()->id())
            <div class="mt-6 bg-red-50 rounded-xl shadow-sm overflow-hidden border border-red-200">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-red-800 mb-2">Danger Zone</h3>
                    <p class="text-sm text-red-600 mb-4">Once you delete a team, there is no going back. Please be certain.
                    </p>

                    <div class="flex space-x-4">
                        <!-- Transfer Ownership -->
                        <button onclick="openTransferModal()"
                            class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700">
                            <i class="fas fa-exchange-alt mr-2"></i>Transfer Ownership
                        </button>

                        <!-- Delete Team -->
                        <button onclick="openDeleteModal()"
                            class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                            <i class="fas fa-trash mr-2"></i>Delete Team
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Transfer Ownership Modal -->
        <div id="transferModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Transfer Ownership</h3>
                    <button onclick="closeTransferModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form action="{{ route('teams.transfer', $team) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select New Owner</label>
                        <select name="user_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select a team member...</option>
                            @foreach ($team->members as $member)
                                @if ($member->id !== $team->owner_id)
                                    <option value="{{ $member->id }}">{{ $member->name }} ({{ $member->email }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <p class="text-sm text-yellow-600 mb-4">You will become an admin after transferring ownership.</p>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeTransferModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                            Transfer Ownership
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
    </div>

    <script>
        function openDeleteModal() {
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        function openTransferModal() {
            document.getElementById('transferModal').classList.remove('hidden');
        }

        function closeTransferModal() {
            document.getElementById('transferModal').classList.add('hidden');
        }
    </script>
@endsection
