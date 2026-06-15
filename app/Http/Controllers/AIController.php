<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\ActionItem;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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
        try {
            // Only admin or creator can process
            if ($meeting->created_by !== auth()->id() && auth()->user()->role !== 'admin') {
                Log::warning('Unauthorized AI processing attempt', [
                    'meeting_id' => $meeting->id,
                    'user_id' => auth()->id(),
                    'user_role' => auth()->user()->role ?? 'none'
                ]);
                return redirect()->back()->with('error', 'Unauthorized: You don\'t have permission to process this meeting.');
            }

            Log::info('Starting AI processing for meeting', [
                'meeting_id' => $meeting->id,
                'meeting_title' => $meeting->title,
                'user_id' => auth()->id()
            ]);

            // Validate the request
            $request->validate([
                'audio' => 'required|file|mimes:mp3,wav,m4a,webm,mp4',
            ]);

            // Get the uploaded file
            $audioFile = $request->file('audio');

            Log::info('Audio file received', [
                'original_name' => $audioFile->getClientOriginalName(),
                'size' => $audioFile->getSize(),
                'mime_type' => $audioFile->getMimeType()
            ]);

            // Store the file temporarily
            $path = $audioFile->store('temp_audio', 'local');
            $fullPath = storage_path('app/' . $path);

            Log::info('Audio file stored', ['path' => $fullPath, 'size' => filesize($fullPath)]);

            // Check if file exists and is readable
            if (!file_exists($fullPath)) {
                throw new \Exception('Failed to store audio file');
            }

            // Transcribe audio using AI service
            Log::info('Starting transcription...');
            $transcription = $this->aiService->transcribeAudio($fullPath);

            if (!$transcription['success']) {
                Log::error('Transcription failed', ['error' => $transcription['error'] ?? 'Unknown error']);
                Storage::delete($path);
                return redirect()->back()->with('error', 'Transcription failed: ' . ($transcription['error'] ?? 'Unknown error'));
            }

            Log::info('Transcription completed', [
                'text_length' => strlen($transcription['text']),
                'language' => $transcription['language'] ?? 'unknown'
            ]);

            // Update meeting with transcript
            $meeting->update([
                'transcript' => $transcription['text'],
                'status' => 'ended',
                'end_time' => now(),
            ]);

            // Generate summary
            Log::info('Generating summary...');
            $summary = $this->aiService->generateSummary($transcription['text'], $meeting->title);

            if ($summary['success']) {
                $meeting->update(['summary' => $summary['summary']]);
                Log::info('Summary generated successfully', ['summary_length' => strlen($summary['summary'])]);
            } else {
                Log::warning('Summary generation failed', ['error' => $summary['error'] ?? 'Unknown error']);
            }

            // Extract and create action items
            Log::info('Extracting action items...');
            $actionItemsCount = $this->aiService->extractAndCreateActionItems($transcription['text'], $meeting->id);
            Log::info('Action items extracted', ['count' => $actionItemsCount]);

            // Clean up temp file
            Storage::delete($path);
            Log::info('Temp file deleted', ['path' => $path]);

            // Redirect to meeting show page with success message
            return redirect()->route('meetings.show', $meeting)
                ->with('success', 'AI processing complete! Transcript, summary, and ' . $actionItemsCount . ' action items have been generated.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('AI Processing failed', [
                'meeting_id' => $meeting->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Clean up temp file if it exists
            if (isset($path) && Storage::exists($path)) {
                Storage::delete($path);
            }

            return redirect()->back()->with('error', 'AI processing failed: ' . $e->getMessage());
        }
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
        // Get stats
        $stats = [
            'total_meetings' => Meeting::where('created_by', auth()->id())->count(),
            'ai_processed' => Meeting::where('created_by', auth()->id())
                ->whereNotNull('summary')
                ->count(),
            'total_action_items' => ActionItem::whereHas('meeting', function($q) {
                $q->where('created_by', auth()->id());
            })->count(),
            'completed_action_items' => ActionItem::whereHas('meeting', function($q) {
                $q->where('created_by', auth()->id());
            })->where('status', 'completed')->count(),
        ];
        
        // Get the most recent meeting for AI processing section
        $meeting = Meeting::where('created_by', auth()->id())
            ->latest()
            ->first();
        
        // If no meeting exists, create an empty meeting object
        if (!$meeting) {
            $meeting = new Meeting();
            $meeting->transcript = null;
            $meeting->summary = null;
            $meeting->created_by = auth()->id();
            $meeting->actionItems = collect();
        }
        
        // Get recent summaries (if you want to uncomment that section)
        $recentSummaries = Meeting::where('created_by', auth()->id())
            ->whereNotNull('summary')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('ai.insights', compact('stats', 'meeting', 'recentSummaries'));
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

    public function showMeetingInsights(Meeting $meeting)
    {
        // Authorize: only admin or meeting creator can view
        if ($meeting->created_by !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        // Load relationships
        $meeting->load('actionItems.assignee');

        return view('ai.meeting-insights', compact('meeting'));
    }
}
