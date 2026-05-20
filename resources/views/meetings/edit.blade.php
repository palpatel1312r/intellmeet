@extends('layouts.app')

@section('title', 'Edit ' . $meeting->title)

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 text-white">
                <div class="flex items-center">
                    <a href="{{ route('meetings.show', $meeting) }}" class="text-white hover:text-indigo-200 mr-4">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold">Edit Meeting</h1>
                        <p class="text-indigo-100">Update your meeting details</p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form action="{{ route('meetings.update', $meeting) }}" method="POST" class="p-6">
                @csrf
                @method('PUT')

                <!-- Meeting Code (Read-only) -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Meeting Code</label>
                    <div class="bg-gray-100 px-4 py-2 rounded-lg flex justify-between items-center">
                        <span class="font-mono font-semibold">{{ $meeting->meeting_code }}</span>
                        <button type="button" onclick="copyToClipboard('{{ $meeting->meeting_code }}')"
                            class="text-indigo-600 hover:text-indigo-700">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Share this code with participants to join</p>
                </div>

                <!-- Title -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Meeting Title *</label>
                    <input type="text" name="title" required value="{{ old('title', $meeting->title) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('title')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Description</label>
                    <textarea name="description" rows="4"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="What will this meeting cover?">{{ old('description', $meeting->description) }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date and Time -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Date *</label>
                        <input type="date" name="date" required
                            value="{{ old('date', \Carbon\Carbon::parse($meeting->start_time)->format('Y-m-d')) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Time *</label>
                        <input type="time" name="time" required
                            value="{{ old('time', \Carbon\Carbon::parse($meeting->start_time)->format('H:i')) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Team -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">Team (Optional)</label>
                    <select name="team_id"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Personal Meeting</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" {{ $meeting->team_id == $team->id ? 'selected' : '' }}>
                                {{ $team->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Badge -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Current Status</p>
                            <span
                                class="inline-block px-2 py-1 rounded-full text-xs font-semibold
                            @if ($meeting->status == 'scheduled') bg-yellow-100 text-yellow-800
                            @elseif($meeting->status == 'ongoing') bg-green-100 text-green-800
                            @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($meeting->status) }}
                            </span>
                        </div>
                        @if ($meeting->status == 'scheduled')
                            <p class="text-xs text-gray-500">You can edit this meeting until it starts</p>
                        @endif
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-between items-center pt-4 border-t">
                    <button type="button" onclick="confirmDelete()"
                        class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        <i class="fas fa-trash mr-2"></i>Delete Meeting
                    </button>
                    <div class="space-x-3">
                        <a href="{{ route('meetings.show', $meeting) }}"
                            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            <i class="fas fa-save mr-2"></i>Save Changes
                        </button>
                    </div>
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

    @push('scripts')
        <script>
            function copyToClipboard(text) {
                navigator.clipboard.writeText(text);
                alert('Meeting code copied!');
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
