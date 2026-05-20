<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Meeting;
use App\Events\NewChatMessage;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function sendMessage(Request $request, Meeting $meeting)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $message = ChatMessage::create([
            'meeting_id' => $meeting->id,
            'user_id' => $request->user()->id,
            'message' => $request->message,
        ]);

        broadcast(new NewChatMessage($message))->toOthers();

        return response()->json($message->load('user'), 201);
    }

    public function getMessages(Meeting $meeting)
    {
        $messages = ChatMessage::where('meeting_id', $meeting->id)
            ->with('user')
            ->latest()
            ->limit(100)
            ->get()
            ->reverse()
            ->values();

        return response()->json($messages);
    }
}
