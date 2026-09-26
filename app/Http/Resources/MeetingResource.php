<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeetingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'meeting_code' => $this->meeting_code,
            'join_link' => $this->join_link,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'status' => $this->status,
            'recording_url' => $this->recording_url,
            'transcript' => $this->transcript,
            'summary' => $this->summary,
            'settings' => $this->settings,
            'team_id' => $this->team_id,
            'created_by' => $this->created_by,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'participants' => UserResource::collection($this->whenLoaded('participants')),
            'participants_count' => $this->whenCounted('participants'),
            'action_items' => ActionItemResource::collection($this->whenLoaded('actionItems')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
