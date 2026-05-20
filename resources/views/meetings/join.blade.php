@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>Join Meeting: {{ $meeting->meeting_code }}</h3>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <h4>{{ $meeting->title }}</h4>
                        <p>Host: {{ $meeting->creator->name ?? 'Unknown' }}</p>
                        
                        @php
                            $participantsCount = \App\Models\MeetingParticipant::where('meeting_id', $meeting->id)
                                ->whereNull('left_at')
                                ->count();
                        @endphp
                        
                        <p>Current Participants: {{ $participantsCount }}</p>
                        
                        <form action="{{ route('meeting.join.submit', $meeting->meeting_code) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-lg">
                                Join Meeting
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection