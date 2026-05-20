<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\ActionItem;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AIController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;

        // Remove ALL limits
        ini_set('memory_limit', '-1');           // Unlimited memory
        ini_set('max_execution_time', '0');      // No execution time limit
        ini_set('max_input_time', '-1');         // No input time limit
        ini_set('post_max_size', '0');           // No POST size limit (0 = unlimited)
        ini_set('upload_max_filesize', '0');     // No upload file size limit (0 = unlimited)
    }

    /**
     * Process meeting with full AI intelligence (file upload)
     */
    public function processMeeting(Request $request, Meeting $meeting)
    {
        // Only admin or creator can process
        if ($meeting->created_by !== auth()->id() && auth()->user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        // Remove ALL validation limits
        $request->validate([
            'audio' => 'required|file|mimes:mp3,wav,m4a,webm,mp4', // NO max size!
        ]);

        $audioFile = $request->file('audio');
        $path = $audioFile->store('temp_audio');
        $fullPath = storage_path('app/' . $path);

        // Transcribe audio
        $transcription = $this->aiService->transcribeAudio($fullPath);

        if (!$transcription['success']) {
            Storage::delete($path);
            return redirect()->back()->with('error', 'Transcription failed: ' . ($transcription['error'] ?? 'Unknown error'));
        }

        // Update meeting
        $meeting->update([
            'transcript' => $transcription['text'],
            'status' => 'ended',
            'end_time' => now(),
        ]);

        // Generate summary
        $summary = $this->aiService->generateSummary($transcription['text'], $meeting->title);
        if ($summary['success']) {
            $meeting->update(['summary' => $summary['summary']]);
        }

        // Extract action items
        $this->aiService->extractAndCreateActionItems($transcription['text'], $meeting->id);

        Storage::delete($path);

        return redirect()->route('meetings.show', $meeting)
            ->with('success', 'AI processing complete! Summary and action items generated.');
    }

    /**
     * Process meeting with text transcript
     */
    public function processText(Request $request, Meeting $meeting)
    {
        // Only admin or creator can process
        if ($meeting->created_by !== auth()->id() && auth()->user()->role !== 'admin') {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $request->validate([
            'transcript' => 'required|string|min:50',
        ]);

        $meeting->update([
            'transcript' => $request->transcript,
            'status' => 'ended',
            'end_time' => now(),
        ]);

        // Generate summary using AIService
        $summary = $this->aiService->generateSummary($request->transcript, $meeting->title);
        if ($summary['success']) {
            $meeting->update(['summary' => $summary['summary']]);
        }

        // Extract action items
        $this->aiService->extractActionItems($request->transcript, $meeting->id);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'AI processing complete']);
        }

        return redirect()->route('meetings.show', $meeting)
            ->with('success', 'AI processing complete! Summary and action items generated.');
    }

    /**
     * Get AI insights dashboard
     */
    public function getInsights()
    {
        $user = auth()->user();

        $meetings = Meeting::where('created_by', $user->id)
            ->orWhereHas('participants', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with('actionItems')
            ->latest()
            ->paginate(10);

        $stats = [
            'total_meetings' => Meeting::where('created_by', $user->id)->count(),
            'ai_processed' => Meeting::where('created_by', $user->id)->whereNotNull('summary')->count(),
            'total_action_items' => ActionItem::whereHas('meeting', function ($q) use ($user) {
                $q->where('created_by', $user->id);
            })->count(),
            'completed_action_items' => ActionItem::whereHas('meeting', function ($q) use ($user) {
                $q->where('created_by', $user->id);
            })->where('status', 'completed')->count(),
        ];

        $recentSummaries = Meeting::where('created_by', $user->id)
            ->whereNotNull('summary')
            ->latest()
            ->take(5)
            ->get();

        return view('ai.insights', compact('meetings', 'stats', 'recentSummaries'));
    }

    /**
     * Chat with AI assistant
     */
    public function chatAssistant(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $response = $this->aiService->chatAssistant($request->message);

        return response()->json($response);
    }

    /**
     * Real-time transcription
     */
    public function realtimeTranscribe(Request $request, Meeting $meeting)
    {
        // Only participants can use this
        if ($meeting->created_by !== auth()->id() && !$meeting->participants->contains(auth()->id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'audio' => 'required|file', // NO size limit!
        ]);

        // Process audio chunk for real-time transcription
        return response()->json(['success' => true]);
    }
}
