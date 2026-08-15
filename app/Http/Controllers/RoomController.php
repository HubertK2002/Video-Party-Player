<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::all();
        return view('rooms.index', compact('rooms'));
    }

    public function owned()
    {
        $user = auth()->user();
        $ownedRooms = $user->rooms()->where('owner_id', $user->id)->get();
        return view('rooms.owned', compact('ownedRooms', 'user'));
    }

    public function create()
    {
        return view('rooms.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'is_public' => 'required|boolean',
        ]);

        $room = new Room();
        $room->name = $validatedData['name'];
        $room->is_public = $validatedData['is_public'];
        $user = auth()->user();
        $room->owner_id = $user->id;
        $room->save();

        $roomUser = new RoomUser();
        $roomUser->room_id = $room->id;
        $roomUser->user_id = $user->id;
        $roomUser->save();

        return redirect()->route('rooms.owned')->with('success', 'Pokój został utworzony pomyślnie!');
    }

    public function show(Room $room)
    {
        $users = $room->users()->get();
        $messages = $room->messages()->with('user')->orderBy('sent_at', 'asc')->get();

        $watchStatus = null;

        if (Storage::disk('public')->exists($room->id)) {
            $fileExists = true;
            $watchStatus = Redis::get('room:' . $room->id . ':watch_status');
        } else {
            $fileExists = false;
        }

        return view('rooms.show', compact('room', 'users', 'messages', 'fileExists', 'watchStatus'));
    }
}
