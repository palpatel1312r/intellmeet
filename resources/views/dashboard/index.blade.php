@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="space-y-6">
        <!-- Welcome Banner -->
        <div class="gradient-bg rounded-2xl p-8 text-white">
            <h1 class="text-3xl font-bold mb-2">Welcome back, {{ Auth::user()->name }}! 👋</h1>
            <p class="text-indigo-100">Ready for productive meetings today?</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Meetings</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $stats['total_meetings'] ?? 0 }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-video text-blue-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-green-600 text-sm">↑ 12%</span>
                    <span class="text-gray-500 text-sm ml-1">from last month</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Pending Tasks</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $stats['pending_tasks'] ?? 0 }}</p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <i class="fas fa-tasks text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Team Members</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $stats['team_members'] ?? 0 }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-users text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Hours Saved</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $stats['hours_saved'] ?? 0 }}</p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <i class="fas fa-clock text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Meetings -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-800">Upcoming Meetings</h2>
                    {{-- REMOVED admin check - ALL authenticated users can create meetings --}}
                    <a href="{{ route('meetings.create') }}"
                        class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                        <i class="fas fa-plus mr-2"></i>New Meeting
                    </a>
                </div>
            </div>

            <div class="divide-y divide-gray-200">
                @forelse($upcomingMeetings ?? [] as $meeting)
                    <div class="p-6 hover:bg-gray-50 transition">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="bg-indigo-100 rounded-lg p-3">
                                    <i class="fas fa-calendar text-indigo-600"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-800">{{ $meeting->title }}</h3>
                                    <p class="text-sm text-gray-500 mt-1">
                                        <i class="far fa-clock mr-1"></i>
                                        {{ \Carbon\Carbon::parse($meeting->start_time)->format('F j, Y g:i A') }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        <i class="fas fa-users mr-1"></i>
                                        {{ $meeting->participants_count ?? 0 }} participants
                                    </p>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <a href="{{ route('meetings.join', $meeting) }}"
                                    class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                                    <i class="fas fa-video mr-1"></i>Join
                                </a>
                                <a href="{{ route('meetings.show', $meeting) }}"
                                    class="border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition">
                                    Details
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center">
                        <i class="fas fa-calendar-alt text-5xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500">No upcoming meetings scheduled</p>
                        {{-- REMOVED admin check - ALL authenticated users can create meetings --}}
                        <a href="{{ route('meetings.create') }}"
                            class="inline-block mt-4 text-indigo-600 hover:text-indigo-700">
                            Schedule your first meeting →
                        </a>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Activity & AI Insights -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-sm">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800">Recent Activity</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @forelse($recentActivities ?? [] as $activity)
                            <div class="flex items-start space-x-3">
                                <div class="bg-{{ $activity['color'] ?? 'blue' }}-100 rounded-full p-2 mt-1">
                                    <i
                                        class="fas fa-{{ $activity['icon'] ?? 'circle' }} text-{{ $activity['color'] ?? 'blue' }}-600 text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-800">{{ $activity['message'] ?? '' }}</p>
                                    <p class="text-xs text-gray-500">{{ $activity['time'] ?? '' }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="flex items-start space-x-3">
                                <div class="bg-green-100 rounded-full p-2 mt-1">
                                    <i class="fas fa-check-circle text-green-600 text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-800">Welcome to IntellMeet! Start your first meeting.</p>
                                    <p class="text-xs text-gray-500">Just now</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-sm p-6 text-white">
                <i class="fas fa-robot text-3xl mb-4"></i>
                <h2 class="text-xl font-bold mb-2">AI Meeting Insights</h2>
                <p class="text-indigo-100 mb-4">Your team saved {{ $stats['hours_saved'] ?? 0 }} hours this week using AI
                    summaries</p>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold">{{ $stats['efficiency'] ?? 85 }}%</p>
                        <p class="text-sm text-indigo-100">Meeting efficiency</p>
                    </div>
                    <i class="fas fa-chart-line text-3xl text-indigo-200"></i>
                </div>
                <div class="mt-4">
                    <a href="{{ route('meetings.analytics') }}"
                        class="text-white hover:text-indigo-200 text-sm font-medium">
                        View detailed analytics →
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
        }
    </style>
@endsection
