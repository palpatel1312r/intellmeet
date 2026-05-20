@extends('layouts.app')

@section('title', 'Meeting Analytics')

@section('content')
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Meeting Analytics</h1>
                <p class="text-gray-500 mt-1">Track your meeting productivity</p>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-sm p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Meetings</p>
                        <p class="text-2xl font-bold">{{ $stats['total_meetings'] }}</p>
                    </div>
                    <i class="fas fa-video text-indigo-500 text-2xl"></i>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">AI Processed</p>
                        <p class="text-2xl font-bold">{{ $stats['ai_processed'] }}</p>
                    </div>
                    <i class="fas fa-robot text-purple-500 text-2xl"></i>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Action Items</p>
                        <p class="text-2xl font-bold">{{ $stats['action_items'] }}</p>
                    </div>
                    <i class="fas fa-tasks text-green-500 text-2xl"></i>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Completion Rate</p>
                        <p class="text-2xl font-bold">{{ $stats['completion_rate'] }}%</p>
                    </div>
                    <i class="fas fa-chart-line text-orange-500 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Meeting History -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800">Meeting History</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Meeting</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">AI Summary</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($meetings as $meeting)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $meeting->title }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($meeting->start_time)->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2 py-1 text-xs rounded-full 
                                @if ($meeting->status === 'ended') bg-gray-100 text-gray-800
                                @elseif($meeting->status === 'ongoing') bg-green-100 text-green-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                        {{ ucfirst($meeting->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($meeting->summary)
                                        <span class="text-green-600 text-sm">
                                            <i class="fas fa-check-circle mr-1"></i>Available
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-sm">Not processed</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('meetings.show', $meeting) }}"
                                        class="text-indigo-600 hover:text-indigo-900">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    No meetings found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t">
                {{ $meetings->links() }}
            </div>
        </div>
    </div>
@endsection
