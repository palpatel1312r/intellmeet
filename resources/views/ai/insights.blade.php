@extends('layouts.app')

@section('title', 'AI Insights')

@section('content')
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">AI Meeting Insights</h1>
                <p class="text-gray-500 mt-1">Powered by OpenAI GPT-4</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl p-4 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-purple-100 text-sm">Total Meetings</p>
                        <p class="text-3xl font-bold">{{ $stats['total_meetings'] }}</p>
                    </div>
                    <i class="fas fa-video text-3xl text-purple-200"></i>
                </div>
            </div>

            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-4 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-blue-100 text-sm">AI Processed</p>
                        <p class="text-3xl font-bold">{{ $stats['ai_processed'] }}</p>
                    </div>
                    <i class="fas fa-robot text-3xl text-blue-200"></i>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-4 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-green-100 text-sm">Action Items</p>
                        <p class="text-3xl font-bold">{{ $stats['total_action_items'] }}</p>
                    </div>
                    <i class="fas fa-tasks text-3xl text-green-200"></i>
                </div>
            </div>

            <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl p-4 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-orange-100 text-sm">Completion Rate</p>
                        <p class="text-3xl font-bold">
                            {{ $stats['total_action_items'] > 0 ? round(($stats['completed_action_items'] / $stats['total_action_items']) * 100) : 0 }}%
                        </p>
                    </div>
                    <i class="fas fa-chart-line text-3xl text-orange-200"></i>
                </div>
            </div>
        </div>

        <!-- Recent AI Summaries -->
        @if (isset($recentSummaries) && $recentSummaries->count() > 0)
            <div class="bg-white rounded-lg shadow-sm mt-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-file-alt text-indigo-600 mr-2"></i> Recent AI Summaries
                    </h3>
                </div>
                <div class="divide-y divide-gray-200">
                    @foreach ($recentSummaries as $summary)
                        <div class="p-4 hover:bg-gray-50 transition">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-800">{{ $summary['title'] }}</h4>
                                    <p class="text-sm text-gray-500 mt-1">
                                        <i class="far fa-calendar-alt mr-1"></i> {{ $summary['date'] }}
                                    </p>
                                    <p class="text-sm text-gray-600 mt-2 line-clamp-2">
                                        {{ Str::limit($summary['summary'], 150) }}
                                    </p>
                                    <div class="flex items-center mt-2 space-x-3">
                                        <span class="text-xs text-indigo-600">
                                            <i class="fas fa-tasks mr-1"></i> {{ $summary['action_items_count'] }} action
                                            items
                                        </span>
                                    </div>
                                </div>
                                <a href="{{ route('meetings.show', $summary['meeting']) }}"
                                    class="text-indigo-600 hover:text-indigo-800 text-sm ml-4">
                                    View Details →
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
