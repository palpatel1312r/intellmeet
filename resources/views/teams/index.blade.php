@extends('layouts.app')

@section('title', 'Teams')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Teams</h1>
                <p class="text-gray-500 mt-1">Collaborate with your team members</p>
            </div>

            <!-- Only show Create Team button for Admin users -->
            @if (auth()->user()->role === 'admin')
                <a href="{{ route('teams.create') }}"
                    class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition flex items-center">
                    <i class="fas fa-plus mr-2"></i>Create Team
                </a>
            @endif
        </div>

        <!-- Owned Teams -->
        @if ($ownedTeams->count() > 0)
            <div>
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Teams You Own</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($ownedTeams as $team)
                        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden">
                            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 h-24"></div>
                            <div class="p-6 -mt-12">
                                <div class="flex justify-center mb-4">
                                    <div
                                        class="w-20 h-20 rounded-full bg-white border-4 border-indigo-600 flex items-center justify-center">
                                        @if ($team->avatar_url)
                                            <img src="{{ Storage::url($team->avatar_url) }}"
                                                class="w-full h-full rounded-full object-cover">
                                        @else
                                            <i class="fas fa-users text-3xl text-indigo-600"></i>
                                        @endif
                                    </div>
                                </div>
                                <h3 class="text-xl font-bold text-center text-gray-800 mb-2">{{ $team->name }}</h3>
                                <p class="text-gray-500 text-center text-sm mb-4">
                                    {{ Str::limit($team->description ?? 'No description', 80) }}</p>
                                <div class="flex justify-around mb-4 text-center">
                                    <div>
                                        <p class="text-2xl font-bold text-gray-800">{{ $team->members_count }}</p>
                                        <p class="text-xs text-gray-500">Members</p>
                                    </div>
                                    <div>
                                        <p class="text-2xl font-bold text-gray-800">{{ $team->meetings_count }}</p>
                                        <p class="text-xs text-gray-500">Meetings</p>
                                    </div>
                                    <div>
                                        <p class="text-2xl font-bold text-gray-800">{{ $team->tasks_count }}</p>
                                        <p class="text-xs text-gray-500">Tasks</p>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <a href="{{ route('teams.show', $team) }}"
                                        class="flex-1 bg-indigo-600 text-white text-center px-4 py-2 rounded-lg hover:bg-indigo-700">
                                        View Team
                                    </a>
                                    <a href="{{ route('teams.edit', $team) }}"
                                        class="bg-gray-200 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-300"
                                        title="Edit Team">
                                        <i class="fas fa-cog"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Member Teams -->
        @if ($teams->count() > 0)
            <div>
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Teams You're In</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($teams as $team)
                        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden">
                            <div class="bg-gradient-to-r from-green-600 to-teal-600 h-24"></div>
                            <div class="p-6 -mt-12">
                                <div class="flex justify-center mb-4">
                                    <div
                                        class="w-20 h-20 rounded-full bg-white border-4 border-green-600 flex items-center justify-center">
                                        @if ($team->avatar_url)
                                            <img src="{{ Storage::url($team->avatar_url) }}"
                                                class="w-full h-full rounded-full object-cover">
                                        @else
                                            <i class="fas fa-users text-3xl text-green-600"></i>
                                        @endif
                                    </div>
                                </div>
                                <h3 class="text-xl font-bold text-center text-gray-800 mb-2">{{ $team->name }}</h3>
                                <p class="text-gray-500 text-center text-sm mb-4">
                                    {{ Str::limit($team->description ?? 'No description', 80) }}</p>
                                <div class="flex justify-around mb-4 text-center">
                                    <div>
                                        <p class="text-2xl font-bold text-gray-800">{{ $team->members_count }}</p>
                                        <p class="text-xs text-gray-500">Members</p>
                                    </div>
                                    <div>
                                        <p class="text-2xl font-bold text-gray-800">{{ $team->meetings_count }}</p>
                                        <p class="text-xs text-gray-500">Meetings</p>
                                    </div>
                                    <div>
                                        <p class="text-2xl font-bold text-gray-800">{{ $team->tasks_count }}</p>
                                        <p class="text-xs text-gray-500">Tasks</p>
                                    </div>
                                </div>
                                <a href="{{ route('teams.show', $team) }}"
                                    class="block bg-gray-600 text-white text-center px-4 py-2 rounded-lg hover:bg-gray-700">
                                    View Team
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($teams->isEmpty() && $ownedTeams->isEmpty())
            <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                <i class="fas fa-users-slash text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No teams yet</h3>
                <p class="text-gray-500 mb-4">Create a team to start collaborating</p>
                @if (auth()->user()->role === 'admin')
                    <a href="{{ route('teams.create') }}"
                        class="bg-indigo-600 text-white px-6 py-2 rounded-lg inline-block">
                        Create Your First Team
                    </a>
                @else
                    <p class="text-gray-400 text-sm">Contact an administrator to be added to a team.</p>
                @endif
            </div>
        @endif
    </div>
@endsection
