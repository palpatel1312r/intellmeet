@extends('layouts.app')

@section('title', 'Analytics')

@section('content')
    <div class="space-y-6">
        <h1 class="text-3xl font-bold text-gray-800">Analytics Dashboard</h1>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Meetings</p>
                        <p class="text-2xl font-bold">{{ $stats['total_meetings'] }}</p>
                    </div>
                    <i class="fas fa-video text-indigo-500 text-2xl"></i>
                </div>
                <div class="mt-2">
                    <span class="text-green-600 text-sm">↑ {{ $stats['meeting_growth'] }}%</span>
                    <span class="text-gray-500 text-sm ml-1">from last month</span>
                </div>
            </div>

            <div class="bg-white rounded-lg p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">AI Processed</p>
                        <p class="text-2xl font-bold">{{ $stats['ai_processed'] }}</p>
                    </div>
                    <i class="fas fa-robot text-purple-500 text-2xl"></i>
                </div>
                <div class="mt-2">
                    <span
                        class="text-green-600 text-sm">{{ round(($stats['ai_processed'] / max($stats['total_meetings'], 1)) * 100) }}%</span>
                    <span class="text-gray-500 text-sm ml-1">of meetings</span>
                </div>
            </div>

            <div class="bg-white rounded-lg p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Action Items</p>
                        <p class="text-2xl font-bold">{{ $stats['total_action_items'] }}</p>
                    </div>
                    <i class="fas fa-tasks text-green-500 text-2xl"></i>
                </div>
                <div class="mt-2">
                    <span class="text-green-600 text-sm">{{ $stats['completed_items'] }} completed</span>
                </div>
            </div>

            <div class="bg-white rounded-lg p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Hours Saved</p>
                        <p class="text-2xl font-bold">{{ $stats['hours_saved'] }}</p>
                    </div>
                    <i class="fas fa-clock text-yellow-500 text-2xl"></i>
                </div>
                <div class="mt-2">
                    <span class="text-green-600 text-sm">by AI automation</span>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg p-6 shadow-sm">
                <h3 class="font-semibold text-gray-800 mb-4">Meeting Trends</h3>
                <canvas id="meetingChart"></canvas>
            </div>

            <div class="bg-white rounded-lg p-6 shadow-sm">
                <h3 class="font-semibold text-gray-800 mb-4">Productivity Score</h3>
                <canvas id="productivityChart"></canvas>
            </div>
        </div>

        <!-- Top Keywords -->
        <div class="bg-white rounded-lg p-6 shadow-sm">
            <h3 class="font-semibold text-gray-800 mb-4">Top Keywords from Meetings</h3>
            <div class="flex flex-wrap gap-2">
                @foreach ($insights['top_keywords'] as $keyword)
                    <span class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-sm">
                        {{ $keyword }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Meeting Trends Chart
        const meetingCtx = document.getElementById('meetingChart').getContext('2d');
        new Chart(meetingCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [{
                    label: 'Meetings',
                    data: {!! json_encode($chartData) !!},
                    borderColor: 'rgb(99, 102, 241)',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });

        // Productivity Chart
        const prodCtx = document.getElementById('productivityChart').getContext('2d');
        new Chart(prodCtx, {
            type: 'doughnut',
            data: {
                labels: ['Completed Tasks', 'Pending Tasks'],
                datasets: [{
                    data: [{{ $stats['completed_tasks'] }}, {{ $stats['pending_tasks'] }}],
                    backgroundColor: ['#22c55e', '#eab308']
                }]
            }
        });
    </script>
@endsection
