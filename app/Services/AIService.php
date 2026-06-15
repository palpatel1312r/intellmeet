<?php

namespace App\Services;

use App\Models\ActionItem;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class AIService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY');
    }

    /**
     * Transcribe audio using Whisper API
     */
    public function transcribeAudio($audioFile)
    {
        try {
            Log::info('Starting transcription for file: ' . $audioFile);

            $response = OpenAI::audio()->transcribe([
                'model' => 'whisper-1',
                'file' => fopen($audioFile, 'r'),
                'response_format' => 'verbose_json',
                'language' => 'en',
            ]);

            Log::info('Transcription completed successfully');

            return [
                'success' => true,
                'text' => $response->text,
                'segments' => $response->segments ?? [],
                'language' => $response->language ?? 'en',
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
            Log::info('Generating summary for meeting: ' . $meetingTitle);

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

            Log::info('Summary generated successfully');

            return [
                'success' => true,
                'summary' => $response->choices[0]->message->content,
            ];
        } catch (\Exception $e) {
            Log::error('Summary generation failed: ' . $e->getMessage());

            // Fallback to GPT-3.5 if GPT-4 fails
            try {
                $response = OpenAI::chat()->create([
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a professional meeting summarizer. Be concise and actionable.'],
                        ['role' => 'user', 'content' => "Summarize this meeting transcript in 3-5 sentences:\n\n{$transcript}"],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 1000,
                ]);

                return [
                    'success' => true,
                    'summary' => $response->choices[0]->message->content,
                ];
            } catch (\Exception $e2) {
                return ['success' => false, 'error' => $e2->getMessage()];
            }
        }
    }

    /**
     * Extract action items from transcript
     */
    public function extractActionItems($transcript, $meetingId)
    {
        try {
            Log::info('Extracting action items for meeting: ' . $meetingId);

            $prompt = "Extract all action items from this meeting transcript. For each action item, identify:
- Task description
- Assigned person (extract full name from context, if not specified use null)
- Due date if mentioned (format as YYYY-MM-DD if possible)
- Priority (High/Medium/Low based on urgency)

Return as JSON array with keys: task, assigned_to, due_date, priority

Example output format:
{
  \"action_items\": [
    {
      \"task\": \"Send the quarterly report to clients\",
      \"assigned_to\": \"John Doe\",
      \"due_date\": \"2024-12-31\",
      \"priority\": \"High\"
    }
  ]
}

Transcript:
{$transcript}";

            $response = OpenAI::chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'Extract action items as JSON only. No additional text. If no action items found, return {"action_items": []}'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.3,
            ]);

            $content = $response->choices[0]->message->content;

            // Clean the response to ensure valid JSON
            $content = preg_replace('/```json\s*|\s*```/', '', $content);
            $result = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('JSON parse error: ' . json_last_error_msg());
                return ['success' => true, 'count' => 0, 'items' => []];
            }

            $actionItems = $result['action_items'] ?? $result['items'] ?? [];

            $createdItems = [];
            foreach ($actionItems as $item) {
                // Find user by name
                $assignedUser = null;
                if (!empty($item['assigned_to']) && $item['assigned_to'] !== 'null' && $item['assigned_to'] !== 'None') {
                    $assignedUser = User::where('name', 'LIKE', "%{$item['assigned_to']}%")->first();
                }

                $priority = match (strtolower($item['priority'] ?? 'medium')) {
                    'high', 'h' => 5,
                    'medium', 'm' => 3,
                    'low', 'l' => 1,
                    default => 3,
                };

                $dueDate = null;
                if (!empty($item['due_date']) && $item['due_date'] !== 'null') {
                    $dueDate = $item['due_date'];
                }

                $actionItem = ActionItem::create([
                    'meeting_id' => $meetingId,
                    'title' => $item['task'] ?? $item['title'] ?? 'Untitled Task',
                    'description' => $item['task'] ?? null,
                    'assigned_to' => $assignedUser ? $assignedUser->id : null,
                    'assigned_by' => auth()->id(),
                    'due_date' => $dueDate,
                    'priority' => $priority,
                    'status' => 'pending',
                ]);
                $createdItems[] = $actionItem;
            }

            Log::info('Extracted ' . count($createdItems) . ' action items');

            return [
                'success' => true,
                'count' => count($createdItems),
                'items' => $createdItems,
            ];
        } catch (\Exception $e) {
            Log::error('Action item extraction failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage(), 'count' => 0];
        }
    }

    /**
     * Analyze meeting sentiment and engagement
     */
    public function analyzeSentiment($transcript)
    {
        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'Analyze the sentiment of this meeting transcript. Return JSON only with keys: sentiment, engagement_score, emotions, effectiveness.'],
                    ['role' => 'user', 'content' => "Analyze sentiment (positive/neutral/negative), engagement level (1-10), key emotions detected, and overall meeting effectiveness (1-10). Transcript: {$transcript}"],
                ],
                'temperature' => 0.3,
            ]);

            $content = $response->choices[0]->message->content;

            // Try to parse as JSON
            $content = preg_replace('/```json\s*|\s*```/', '', $content);
            $analysis = json_decode($content, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return ['success' => true, 'analysis' => $analysis];
            }

            // Fallback: extract sentiment from text
            return [
                'success' => true,
                'analysis' => [
                    'sentiment' => $this->extractSentiment($content),
                    'engagement_score' => 5,
                    'emotions' => ['neutral'],
                    'effectiveness' => 5,
                    'raw_content' => $content,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Sentiment analysis failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function extractSentiment($text)
    {
        $text = strtolower($text);
        if (strpos($text, 'positive') !== false || strpos($text, 'good') !== false || strpos($text, 'great') !== false) {
            return 'positive';
        }
        if (strpos($text, 'negative') !== false || strpos($text, 'bad') !== false || strpos($text, 'poor') !== false) {
            return 'negative';
        }
        return 'neutral';
    }

    /**
     * Real-time transcription for live meetings
     */
    public function realtimeTranscribe($audioChunk)
    {
        // Ensure temp directory exists
        $tempDir = storage_path('temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        // Store chunk temporarily
        $tempFile = $tempDir . '/audio_' . time() . '_' . uniqid() . '.webm';
        file_put_contents($tempFile, $audioChunk);

        $result = $this->transcribeAudio($tempFile);

        // Clean up
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }

        return $result;
    }

    /**
     * Generate productivity insights
     */
    public function generateProductivityInsights($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return ['error' => 'User not found'];
        }

        $meetings = Meeting::where('created_by', $userId)
            ->whereNotNull('transcript')
            ->get();

        $totalTasks = ActionItem::whereHas('meeting', function ($q) use ($userId) {
            $q->where('created_by', $userId);
        })->count();

        $completedTasks = ActionItem::whereHas('meeting', function ($q) use ($userId) {
            $q->where('created_by', $userId);
        })->where('status', 'completed')->count();

        $insights = [
            'total_meetings' => $meetings->count(),
            'meetings_with_ai' => $meetings->whereNotNull('summary')->count(),
            'total_action_items' => $totalTasks,
            'completion_rate' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0,
            'time_saved' => $meetings->count() * 30, // Estimated 30 min saved per AI-processed meeting
            'top_keywords' => $this->extractKeywords($meetings->pluck('transcript')->implode(' ')),
        ];

        return $insights;
    }

    private function extractKeywords($text)
    {
        // Simple keyword extraction
        $words = str_word_count(strtolower($text), 1, '0123456789');
        $stopWords = ['the', 'and', 'of', 'to', 'in', 'for', 'on', 'with', 'by', 'at', 'from', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'having', 'do', 'does', 'did', 'doing', 'a', 'an', 'this', 'that', 'these', 'those', 'i', 'you', 'he', 'she', 'it', 'we', 'they', 'me', 'him', 'her', 'us', 'them'];

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
                    ['role' => 'system', 'content' => 'You are a helpful meeting assistant. Answer questions about meetings, action items, and productivity. Keep responses concise and practical.'],
                    ['role' => 'user', 'content' => $message],
                ],
                'temperature' => 0.7,
                'max_tokens' => 500,
            ]);

            return [
                'success' => true,
                'response' => $response->choices[0]->message->content,
            ];
        } catch (\Exception $e) {
            Log::error('Chat assistant error: ' . $e->getMessage());
            return [
                'success' => false,
                'response' => 'Sorry, I encountered an error. Please try again later.',
            ];
        }
    }
}
