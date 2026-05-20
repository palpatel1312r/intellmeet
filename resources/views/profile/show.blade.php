@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Profile Header -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Gradient Banner -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 h-32 relative">
                <!-- Avatar positioned to overlap the banner bottom -->
                <div class="absolute -bottom-12 left-6">
                    <div class="relative">
                        @php
                            $avatarPath = Auth::user()->avatar_url
                                ? Storage::url(Auth::user()->avatar_url)
                                : 'https://ui-avatars.com/api/?name=' .
                                    urlencode(Auth::user()->name) .
                                    '&background=6366f1&color=fff&size=120';
                        @endphp
                        <img src="{{ $avatarPath }}"
                            onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=6366f1&color=fff&size=120';"
                            class="w-24 h-24 rounded-full border-4 border-white object-cover shadow-lg">
                        {{-- <a href="{{ route('profile.edit') }}"
                            class="absolute bottom-0 right-0 bg-indigo-600 rounded-full p-1.5 text-white hover:bg-indigo-700 transition shadow-md">
                            <i class="fas fa-camera text-xs"></i>
                        </a> --}}
                    </div>
                </div>
            </div>

            <!-- Profile Info - Positioned below the banner -->
            <div class="px-6 pt-16 pb-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">{{ Auth::user()->name }}</h1>
                        <p class="text-gray-500">{{ Auth::user()->email }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                {{ ucfirst(Auth::user()->role ?? 'Member') }}
                            </span>
                        </div>
                    </div>

                    <a href="{{ route('profile.edit') }}"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-edit mr-2"></i>Edit Profile
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
            <div class="bg-white rounded-lg p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Meetings</p>
                        <p class="text-2xl font-bold text-gray-800">
                            {{ ($stats['meetings_created'] ?? 0) + ($stats['meetings_joined'] ?? 0) }}</p>
                    </div>
                    <div class="bg-indigo-100 rounded-full p-3">
                        <i class="fas fa-video text-indigo-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Tasks Completed</p>
                        <p class="text-2xl font-bold text-gray-800">
                            {{ $stats['tasks_completed'] ?? 0 }}/{{ $stats['total_tasks'] ?? 0 }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Active Teams</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $stats['teams_count'] ?? 0 }}</p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <i class="fas fa-users text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Action Items</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $stats['action_items'] ?? 0 }}</p>
                    </div>
                    <div class="bg-orange-100 rounded-full p-3">
                        <i class="fas fa-tasks text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bio Section -->
        @if (Auth::user()->bio)
            <div class="bg-white rounded-lg p-6 shadow-sm mt-6">
                <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-user-circle text-indigo-600 mr-2"></i> About Me
                </h3>
                <p class="text-gray-600">{{ Auth::user()->bio }}</p>
            </div>
        @endif

        <!-- Work Information -->
        @if (Auth::user()->company || Auth::user()->position || Auth::user()->phone)
            <div class="bg-white rounded-lg p-6 shadow-sm mt-6">
                <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-briefcase text-indigo-600 mr-2"></i> Work Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @if (Auth::user()->company)
                        <div>
                            <p class="text-sm text-gray-500">Company</p>
                            <p class="font-medium text-gray-800">{{ Auth::user()->company }}</p>
                        </div>
                    @endif
                    @if (Auth::user()->position)
                        <div>
                            <p class="text-sm text-gray-500">Position</p>
                            <p class="font-medium text-gray-800">{{ Auth::user()->position }}</p>
                        </div>
                    @endif
                    @if (Auth::user()->phone)
                        <div>
                            <p class="text-sm text-gray-500">Phone</p>
                            <p class="font-medium text-gray-800">{{ Auth::user()->phone }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            <!-- Recent Meetings -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-4 border-b bg-gray-50 rounded-t-lg">
                    <h3 class="font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-video text-indigo-600 mr-2"></i> Recent Meetings
                    </h3>
                </div>
                <div class="divide-y">
                    @forelse(($recentMeetings ?? []) as $meeting)
                        <div class="p-4 hover:bg-gray-50 transition">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="font-medium text-gray-800">{{ $meeting->title }}</p>
                                    <p class="text-sm text-gray-500">{{ $meeting->created_at->diffForHumans() }}</p>
                                </div>
                                <a href="{{ route('meetings.show', $meeting) }}"
                                    class="text-indigo-600 text-sm hover:text-indigo-800">
                                    View <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-gray-500">No meetings yet</div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Tasks -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-4 border-b bg-gray-50 rounded-t-lg">
                    <h3 class="font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-tasks text-indigo-600 mr-2"></i> Recent Tasks
                    </h3>
                </div>
                <div class="divide-y">
                    @forelse(($recentTasks ?? []) as $task)
                        <div class="p-4 hover:bg-gray-50 transition">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="font-medium text-gray-800">{{ $task->title }}</p>
                                    <p class="text-sm text-gray-500">
                                        Status:
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        {{ $task->status === 'done'
                                            ? 'bg-green-100 text-green-800'
                                            : ($task->status === 'in_progress'
                                                ? 'bg-yellow-100 text-yellow-800'
                                                : 'bg-gray-100 text-gray-800') }}">
                                            {{ ucfirst($task->status ?? 'pending') }}
                                        </span>
                                    </p>
                                </div>
                                <a href="{{ route('tasks.show', $task) }}"
                                    class="text-indigo-600 text-sm hover:text-indigo-800">
                                    View <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-gray-500">No tasks yet</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
