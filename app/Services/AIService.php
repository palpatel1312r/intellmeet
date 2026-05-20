<?php

namespace App\Services;

use App\Models\ActionItem;
use App\Models\Meeting;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;
use Symfony\Component\HttpFoundation\Request;
// use Illuminate\Support\Facades\Storage;

class AIService
{
    /**
     * Transcribe audio using Whisper API
     */
    public function transcribeAudio($audioFile)
    {
        try {
            $response = OpenAI::audio()->transcribe([
                'model' => 'whisper-1',
                'file' => fopen($audioFile, 'r'),
                'response_format' => 'verbose_json',
                'language' => 'en',
            ]);

            return [
                'success' => true,
                'text' => $response->text,
                'segments' => $response->segments,
                'language' => $response->language,
                'duration' => $response->duration ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Transcription failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Generate meeting summary using GPT-4
     */
    public function generateSummary($transcript, $meetingTitle)
    {
        try {
            $prompt = "You are an AI meeting assistant. Analyze the following meeting transcript and provide a comprehensive summary.

Meeting Title: {$meetingTitle}

Transcript:
{$transcript}

Please provide:
1. **Executive Summary** (2-3 sentences)
2. **Key Decisions Made** (bullet points)
3. **Main Discussion Points** (bullet points)
4. **Action Items** (with assignees if mentioned)
5. **Next Steps**
6. **Sentiment Analysis** (positive/neutral/negative)

Format your response in a clean, professional manner.";

            $response = OpenAI::chat()->create([
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a professional meeting summarizer. Be concise, accurate, and actionable.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.7,
                'max_tokens' => 2000,
            ]);

            return [
                'success' => true,
                'summary' => $response->choices[0]->message->content,
            ];
        } catch (\Exception $e) {
            Log::error('Summary generation failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Extract action items from transcript
     */
    public function extractActionItems($transcript, $meetingId)
    {
        try {
            $prompt = "Extract all action items from this meeting transcript. For each action item, identify:
- Task description
- Assigned person (extract full name from context)
- Due date if mentioned
- Priority (High/Medium/Low based on urgency)

Return as JSON array with keys: task, assigned_to, due_date, priority

Transcript:
{$transcript}";

            $response = OpenAI::chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'Extract action items as JSON only. No additional text.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'response_format' => ['type' => 'json_object'],
            ]);

            $result = json_decode($response->choices[0]->message->content, true);
            $actionItems = $result['action_items'] ?? $result['items'] ?? [];

            $createdItems = [];
            foreach ($actionItems as $item) {
                // Find user by name
                $assignedUser = null;
                if (!empty($item['assigned_to'])) {
                    $assignedUser = \App\Models\User::where('name', 'LIKE', "%{$item['assigned_to']}%")->first();
                }

                $priority = match (strtolower($item['priority'] ?? 'medium')) {
                    'high' => 5,
                    'medium' => 3,
                    'low' => 1,
                    default => 3,
                };

                $actionItem = ActionItem::create([
                    'meeting_id' => $meetingId,
                    'title' => $item['task'] ?? 'Untitled Task',
                    'description' => $item['task'] ?? null,
                    'assigned_to' => $assignedUser ? $assignedUser->id : null,
                    'assigned_by' => auth()->id(),
                    'due_date' => $item['due_date'] ?? null,
                    'priority' => $priority,
                    'status' => 'pending',
                ]);
                $createdItems[] = $actionItem;
            }

            return [
                'success' => true,
                'count' => count($createdItems),
                'items' => $createdItems,
            ];
        } catch (\Exception $e) {
            Log::error('Action item extraction failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Analyze meeting sentiment and engagement
     */
    public function analyzeSentiment($transcript)
    {
        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'system', 'content' => 'Analyze the sentiment of this meeting transcript. Return JSON only.'],
                    ['role' => 'user', 'content' => "Analyze sentiment (positive/neutral/negative), engagement level (1-10), key emotions detected, and overall meeting effectiveness. Transcript: {$transcript}"],
                ],
            ]);

            $content = $response->choices[0]->message->content;
            // Try to parse as JSON, if not, extract structured info
            if (json_decode($content)) {
                return ['success' => true, 'analysis' => json_decode($content, true)];
            }

            return [
                'success' => true,
                'analysis' => [
                    'sentiment' => $this->extractSentiment($content),
                    'content' => $content,
                ],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function extractSentiment($text)
    {
        if (stripos($text, 'positive') !== false) return 'positive';
        if (stripos($text, 'negative') !== false) return 'negative';
        return 'neutral';
    }

    /**
     * Real-time transcription for live meetings
     */
    public function realtimeTranscribe($audioChunk)
    {
        // Store chunk temporarily
        $tempFile = storage_path('temp/audio_' . time() . '.webm');
        file_put_contents($tempFile, $audioChunk);

        $result = $this->transcribeAudio($tempFile);
        unlink($tempFile);

        return $result;
    }

    /**
     * Generate productivity insights
     */
    public function generateProductivityInsights($userId)
    {
        $user = \App\Models\User::find($userId);
        $meetings = $user->meetings()->whereNotNull('transcript')->get();

        $insights = [
            'total_meetings' => $meetings->count(),
            'meetings_with_ai' => $meetings->whereNotNull('summary')->count(),
            'total_action_items' => $user->actionItems()->count(),
            'completion_rate' => $user->tasks()->where('status', 'done')->count() / max($user->tasks()->count(), 1) * 100,
            'time_saved' => $meetings->count() * 30, // Estimated 30 min saved per AI-processed meeting
            'top_keywords' => $this->extractKeywords($meetings->pluck('transcript')->implode(' ')),
        ];

        return $insights;
    }

    private function extractKeywords($text)
    {
        // Simple keyword extraction (can be enhanced)
        $words = str_word_count(strtolower($text), 1);
        $stopWords = ['the', 'and', 'of', 'to', 'in', 'for', 'on', 'with', 'by', 'at', 'from'];
        $filtered = array_diff($words, $stopWords);
        $counts = array_count_values($filtered);
        arsort($counts);

        return array_slice(array_keys($counts), 0, 10);
    }

    /**
     * Extract and create action items from transcript
     */
    public function extractAndCreateActionItems($transcript, $meetingId)
    {
        $result = $this->extractActionItems($transcript, $meetingId);
        return $result['count'] ?? 0;
    }

    /**
     * Chat with AI assistant
     */
    public function chatAssistant($message)
    {
        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful meeting assistant. Answer questions about meetings, action items, and productivity.'],
                    ['role' => 'user', 'content' => $message],
                ],
            ]);

            return [
                'success' => true,
                'response' => $response->choices[0]->message->content,
            ];
        } catch (\Exception $e) {
            Log::error('Chat assistant error: ' . $e->getMessage());
            return [
                'success' => false,
                'response' => 'Sorry, I encountered an error. Please try again.',
            ];
        }
    }
    /**
     * Process meeting without file upload (using text)
     */
    public function processText(Request $request, Meeting $meeting)
    {
        // Check authorization
        if ($meeting->created_by !== auth()->id() && auth()->user()->role !== 'admin') {
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

        // Generate summary
        $summary = $this->aiService->generateSummary($request->transcript, $meeting->title);
        if ($summary['success']) {
            $meeting->update(['summary' => $summary['summary']]);
        }

        // Extract action items
        $this->aiService->extractAndCreateActionItems($request->transcript, $meeting->id);

        return redirect()->route('meetings.show', $meeting)
            ->with('success', 'AI processing complete! Summary and action items generated.');
    }
}
