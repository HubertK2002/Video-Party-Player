<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\ChatRoomMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatMessageController extends Controller
{
    public function store(Request $request)
    {
        // Sending a simple object instead of a model
        $messageContent = $request->input('message');
        $messageData = [
            'user' => auth()->user(),
            'message' => $messageContent,
            'room_id' => $request->input('room_id'),
            'sent_at' => $request->input('sent_at', now()->toDateTimeString()),
        ];

        Log::info('Video control command received: ' . json_encode($messageData));
        // Broadcast the message to the chatroom channel
        broadcast(new MessageSent($messageData))->toOthers();
        $chatroomMessage = new ChatRoomMessage();
        $chatroomMessage->room_id = $request->input('room_id');
        $chatroomMessage->user_id = auth()->id();
        $chatroomMessage->message = $messageContent;
        $chatroomMessage->save();

        return response()->json(['status' => 'Message broadcasted!', 'success' => true]);
    }
}
