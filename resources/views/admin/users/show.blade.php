@extends('layouts.app')

@section('title', $user->name)

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="mb-4">
            <a href="{{ route('admin.users.index') }}" class="text-indigo-600 hover:text-indigo-700">
                <i class="fas fa-arrow-left mr-2"></i>Back to Users
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-8 text-white">
                <div class="flex items-center">
                    <img class="h-20 w-20 rounded-full object-cover border-4 border-white"
                        src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=fff&color=6366f1&size=80' }}"
                        alt="">
                    <div class="ml-6">
                        <h1 class="text-3xl font-bold">{{ $user->name }}</h1>
                        <p class="text-indigo-100">{{ $user->email }}</p>
                        <div class="flex items-center mt-2 space-x-3">
                            <span
                                class="px-2 py-1 text-xs rounded-full {{ $user->role == 'admin' ? 'bg-yellow-500' : 'bg-gray-500' }}">
                                {{ ucfirst($user->role) }}
                            </span>
                            {{-- <span
                                class="px-2 py-1 text-xs rounded-full {{ $user->email_verified_at ? 'bg-green-500' : 'bg-red-500' }}">
                                {{ $user->email_verified_at ? 'Verified' : 'Unverified' }}
                            </span> --}}
                        </div>
                    </div>
                    {{-- <div class="ml-auto flex space-x-2">
                        <a href="{{ route('admin.users.edit', $user) }}"
                            class="bg-white text-indigo-600 px-4 py-2 rounded-lg hover:bg-gray-100">
                            <i class="fas fa-edit mr-2"></i>Edit
                        </a>
                    </div> --}}
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-4 gap-4 p-6 border-b">
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total_meetings'] ?? 0 }}</p>
                    <p class="text-sm text-gray-500">Meetings</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total_tasks'] ?? 0 }}</p>
                    <p class="text-sm text-gray-500">Tasks</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['completed_tasks'] ?? 0 }}</p>
                    <p class="text-sm text-gray-500">Completed</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['teams_count'] ?? 0 }}</p>
                    <p class="text-sm text-gray-500">Teams</p>
                </div>
            </div>

            <!-- User Info -->
            <div class="p-6">
                <h3 class="font-semibold text-gray-800 mb-4">User Information</h3>
                <div class="space-y-3">
                    <div class="flex">
                        <div class="w-32 text-gray-500">Full Name:</div>
                        <div class="text-gray-800">{{ $user->name }}</div>
                    </div>
                    <div class="flex">
                        <div class="w-32 text-gray-500">Email:</div>
                        <div class="text-gray-800">{{ $user->email }}</div>
                    </div>
                    <div class="flex">
                        <div class="w-32 text-gray-500">Role:</div>
                        <div class="text-gray-800">{{ ucfirst($user->role) }}</div>
                    </div>
                    <div class="flex">
                        <div class="w-32 text-gray-500">Member Since:</div>
                        <div class="text-gray-800">{{ $user->created_at->format('F j, Y') }}</div>
                    </div>
                    <div class="flex">
                        <div class="w-32 text-gray-500">Last Updated:</div>
                        <div class="text-gray-800">{{ $user->updated_at->diffForHumans() }}</div>
                    </div>
                </div>
            </div>

            <!-- Teams -->
            <div class="p-6 border-t">
                <h3 class="font-semibold text-gray-800 mb-4">Teams ({{ $user->teams->count() }})</h3>
                <div class="flex flex-wrap gap-2">
                    @forelse($user->teams as $team)
                        <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm">
                            {{ $team->name }}
                        </span>
                    @empty
                        <p class="text-gray-500">Not a member of any team</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
