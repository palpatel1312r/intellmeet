<?php

namespace App\Jobs;

use App\Models\Meeting;
use App\Services\AIService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMeetingWithAI implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    protected $meeting;

    public function __construct(Meeting $meeting)
    {
        $this->meeting = $meeting;
    }

    public function handle(AIService $aiService)
    {
        if (!$this->meeting->transcript) {
            return;
        }

        // Generate summary
        $summary = $aiService->generateSummary($this->meeting->transcript);
        if ($summary) {
            $this->meeting->summary = $summary;
        }

        // Extract action items
        $actionItems = $aiService->extractActionItems($this->meeting->transcript);

        $this->meeting->save();

        foreach ($actionItems as $item) {
            $this->meeting->actionItems()->create([
                'title' => $item['title'] ?? 'Action Item',
                'description' => $item['description'] ?? null,
                'assigned_by' => $this->meeting->created_by,
            ]);
        }
    }
}
