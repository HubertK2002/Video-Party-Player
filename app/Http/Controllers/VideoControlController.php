<?php

namespace App\Http\Controllers;

use App\Events\VideoControll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VideoControlController extends Controller
{
    public function store(Request $request)
    {
        $cmd = [
            'cmd' => $request->input('cmd'),
            'roomId' => $request->input('room_id'),
        ];
        $cmd['request_time'] = now()->toDateTimeString();
        Log::info('Video control command received: ' . json_encode($cmd));
        broadcast(new VideoControll($cmd))->toOthers();
        return response()->json(['status' => 'Command broadcasted!', 'success' => true]);
    }
}
