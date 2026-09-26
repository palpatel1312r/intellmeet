<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActionItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'meeting_id' => $this->meeting_id,
            'task_id' => $this->task_id,
            'title' => $this->title ?? $this->description,
            'assigned_to' => $this->assigned_to,
            'due_date' => $this->due_date,
            'completed' => (bool) ($this->completed ?? false),
            'created_at' => $this->created_at,
        ];
    }
}
