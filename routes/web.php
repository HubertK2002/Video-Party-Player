<?php

use Illuminate\Support\Facades\Route;
use App\Events\MessageSent;
use App\Models\Room;

Route::get('/', function () {
    return view('mainpage');
});

Route::get('/send-message', function () {
    // Sending a simple object instead of a model
    $message = [
        'user' => 'John Doe',
        'text' => 'Hello from Laravel Reverb!',
        'timestamp' => now()->toDateTimeString(),
    ];

    // Fire the event
    broadcast(new MessageSent($message));

    return response()->json(['status' => 'Message broadcasted!']);
});

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
    return view('rooms.show', compact('room', 'users'));
})->name('rooms.show');
