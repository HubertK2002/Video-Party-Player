<?php

use Illuminate\Support\Facades\Route;
use App\Events\MessageSent;
use App\Models\Room;
use App\Models\RoomInvitation;
use App\Models\RoomUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;
use App\Models\User;
use App\Models\ChatRoomMessage;

Route::get('/', function () {
    return view('mainpage');
})->name('home');

Route::post('/send-message', function (Request $request) {
    // Sending a simple object instead of a model
    $messageContent = $request->input('message');
    $messageData = [
        'user' => auth()->user(),
        'message' => $messageContent,
        'room_id' => $request->input('room_id'),
        'sent_at' => $request->input('sent_at', now()->toDateTimeString()),
    ];

    // Broadcast the message to the chatroom channel
    broadcast(new MessageSent($messageData))->toOthers();
    $chatroomMessage = new ChatRoomMessage();
    $chatroomMessage->room_id = $request->input('room_id');
    $chatroomMessage->user_id = auth()->id();
    $chatroomMessage->message = $messageContent;
    $chatroomMessage->save();

    return response()->json(['status' => 'Message broadcasted!', 'success' => true]);
})->name('rooms.sendMessage');

Route::get('/messages', function () {
    return view('messages');
});

Route::get('/register', function () {
    return view('auth.register');
});

Route::post('/register', function (\Illuminate\Http\Request $request) {
    // Validate the request data
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
    ]);

    // Create the user
    $user = \App\Models\User::create([
        'name' => $validatedData['name'],
        'email' => $validatedData['email'],
        'password' => bcrypt($validatedData['password']),
    ]);

    // Log the user in
    auth()->login($user);

    // Redirect to a desired page after registration
    return redirect('/')->with('success', 'Konto utworzone pomyślnie!');
});

Route::get('/login', function () {
    return view('auth.login');
});

Route::post('/login', function (\Illuminate\Http\Request $request) {
    // Validate the request data
    $credentials = $request->validate([
        'email' => 'required|string|email',
        'password' => 'required|string',
    ]);

    // Attempt to log the user in
    if (auth()->attempt($credentials)) {
        // Authentication passed...
        return redirect('/')->with('success', 'Zalogowano pomyślnie!');
    }

    // Authentication failed...
    return back()->withErrors([
        'email' => 'Wprowadzone dane logowania są nieprawidłowe.',
    ]);
});

Route::get('/logout', function () {
    auth()->logout();
    return redirect('/')->with('success', 'Wylogowano pomyślnie!');
});

Route::get('rooms', function () {
    $rooms = \App\Models\Room::all();
    return view('rooms.index', compact('rooms'));
})->name('rooms.index');

Route::get('rooms/owned', function () {
    $user = auth()->user();
    $ownedRooms = $user->rooms()->where('owner_id', $user->id)->get();
    return view('rooms.owned', compact('ownedRooms', 'user'));
})->name('rooms.owned');

Route::get('rooms/create', function () {
    return view('rooms.create');
})->name('rooms.create');

Route::post('rooms/store', function (\Illuminate\Http\Request $request) {
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'is_public' => 'required|boolean',
    ]);

    $room = new \App\Models\Room();
    $room->name = $validatedData['name'];
    $room->is_public = $validatedData['is_public'];
    $user = auth()->user();
    $room->owner_id = $user->id;
    $room->save();

    $roomUser = new \App\Models\RoomUser();
    $roomUser->room_id = $room->id;
    $roomUser->user_id = $user->id;
    $roomUser->save();

    return redirect()->route('rooms.owned')->with('success', 'Pokój został utworzony pomyślnie!');
})->name('rooms.store');

Route::get('rooms/show/{room}', function (\App\Models\Room $room) {
    $users = $room->users()->get();
    $messages = $room->messages()->with('user')->orderBy('sent_at', 'asc')->get();
    return view('rooms.show', compact('room', 'users', 'messages'));
})->name('rooms.show');


Route::get('rooms/generate-invite-link/{room}', function (\App\Models\Room $room) {
    $user = auth()->user();
    if ($room->owner_id !== $user->id) {
        return redirect()->route('rooms.show', $room->id)->with('error', 'Nie masz uprawnień do wygenerowania linku do zaproszenia.');
    }

    // Generate a unique invite link
    $inviteLink = route('rooms.join', ['room' => $room->id, 'token' => \Illuminate\Support\Str::random(32)]);
    $invitation = new \App\Models\RoomInvitation();
    $invitation->room_id = $room->id;
    $invitation->invitation_code = \Illuminate\Support\Str::random(32);
    $invitation->save();

    return redirect()->route('rooms.show', $room->id)->with('success', 'Link do zaproszenia został wygenerowany pomyślnie!');
})->name('rooms.generateInviteLink');

Route::get('rooms/join/{room}/{token}', function (\App\Models\Room $room, $token) {
    // Here you can implement logic to validate the token if needed
    if($room->invitations()->where('invitation_code', $token)->doesntExist()) {
        return redirect()->route('rooms.index')->with('error', 'Nieprawidłowy token zaproszenia.');
    }
    
    $user = auth()->user();
    if (!$user) {
        return redirect()->route('login')->with('error', 'Musisz być zalogowany, aby dołączyć do pokoju.');
    }

    // Check if the user is already a member of the room
    if ($room->users()->where('user_id', $user->id)->exists()) {
        return redirect()->route('rooms.show', $room->id)->with('info', 'Jesteś już członkiem tego pokoju.');
    }

    // Add the user to the room
    $roomUser = new \App\Models\RoomUser();
    $roomUser->room_id = $room->id;
    $roomUser->user_id = $user->id;
    $roomUser->save();

    return redirect()->route('rooms.show', $room->id)->with('success', 'Dołączyłeś do pokoju pomyślnie!');
})->name('rooms.join');

Route::get('rooms/invitations/{room}', function (\App\Models\Room $room) {
    $user = auth()->user();
    if ($room->owner_id !== $user->id) {
        return redirect()->route('rooms.show', $room->id)->with('error', 'Nie masz uprawnień do przeglądania zaproszeń do tego pokoju.');
    }

    $invitations = $room->invitations()->get();
    return view('rooms.invitations', compact('room', 'invitations'));
})->name('rooms.invitations');

Route::post('rooms/generateInvitation/{room}', function (\App\Models\Room $room) {
    $user = auth()->user();

    if ($room->owner_id !== $user->id) {
        return error()->json(['error' => 'Nie masz uprawnień do generowania zaproszeń do tego pokoju.'], 403);
    }

    $user = auth()->user();

    if ($room->owner_id !== $user->id) {
        return error()->json(['error' => 'Nie masz uprawnień do generowania zaproszeń do tego pokoju.'], 403);
    }

    // Generate a unique invitation code
    $invitationCode = \Illuminate\Support\Str::random(32);

    // Create a new RoomInvitation
    $invitation = new \App\Models\RoomInvitation();
    $invitation->room_id = $room->id;
    $invitation->invitation_code = $invitationCode;
    $invitation->save();

    return json_encode(['success' => true, 'invitation_code' => $invitationCode]);
})->name('rooms.generateInvitation');

Route::delete('rooms/deleteInvitation/{room}/{invitation}', function (\App\Models\Room $room, \App\Models\RoomInvitation $invitation) {
    $user = auth()->user();

    if ($room->owner_id !== $user->id) {
        return redirect()->route('rooms.show', $room->id)->with('error', 'Nie masz uprawnień do usuwania zaproszeń do tego pokoju.');
    }

    // Check if the invitation belongs to the room
    if ($invitation->room_id !== $room->id) {
        return redirect()->route('rooms.show', $room->id)->with('error', 'To zaproszenie nie należy do tego pokoju.');
    }

    // Delete the invitation
    $invitation->delete();

    return redirect()->route('rooms.invitations', $room->id)->with('success', 'Zaproszenie zostało usunięte pomyślnie!');
})->name('rooms.deleteInvitation');