<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Support\Facades\Redis;

class RoomStreamController extends Controller
{
    public function watch(Room $room)
    {
        $user = auth()->user();
        $isOwner = $user && $room->owner_id === $user->id;

        $watchStatus = Redis::get('room:' . $room->id . ':watch_status');
        if ($watchStatus !== 'active') {
            return redirect()->route('rooms.show', $room->id)->with('error', 'Transmisja jeszcze nie została rozpoczęta.');
        }

        return view('rooms.watch', compact('room', 'isOwner', 'watchStatus'));
    }

    public function start(Room $room)
    {
        $user = auth()->user();
        if ($room->owner_id !== $user->id) {
            return redirect()->route('rooms.show', $room->id)->with('error', 'Nie masz uprawnień do rozpoczęcia transmisji w tym pokoju.');
        }

        // Set the watch status in Redis to true
        Redis::set('room:' . $room->id . ':watch_status', "active");

        return redirect()->route('rooms.show', $room->id)->with('success', 'Transmisja została rozpoczęta pomyślnie!');
    }

    public function stop(Room $room)
    {
        $user = auth()->user();
        if ($room->owner_id !== $user->id) {
            return redirect()->route('rooms.show', $room->id)->with('error', 'Nie masz uprawnień do zakończenia transmisji w tym pokoju.');
        }

        // Set the watch status in Redis to false
        Redis::set('room:' . $room->id . ':watch_status', false);

        return redirect()->route('rooms.show', $room->id)->with('success', 'Transmisja została zakończona pomyślnie!');
    }
}
