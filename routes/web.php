<?php

use Illuminate\Support\Facades\Route;
use App\Events\MessageSent;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordResetController;
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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redis;
use App\Events\VideoControll;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
use App\Events\test;

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

     Log::info('Video control command received: ' . json_encode($messageData));
    // Broadcast the message to the chatroom channel
    broadcast(new MessageSent($messageData))->toOthers();
    $chatroomMessage = new ChatRoomMessage();
    $chatroomMessage->room_id = $request->input('room_id');
    $chatroomMessage->user_id = auth()->id();
    $chatroomMessage->message = $messageContent;
    $chatroomMessage->save();

    return response()->json(['status' => 'Message broadcasted!', 'success' => true]);
})->name('rooms.sendMessage');

Route::post('/video-control', function (Request $request) {
    $cmd = [
        'cmd' => $request->input('cmd'),
        'roomId' => $request->input('room_id'),
    ];
    $cmd['request_time'] = now()->toDateTimeString();
    Log::info('Video control command received: ' . json_encode($cmd));
    broadcast(new VideoControll($cmd))->toOthers();
    return response()->json(['status' => 'Command broadcasted!', 'success' => true]);
})->name('rooms.videoControl');

Route::get('/messages', function () {
    return view('messages');
});

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::post('/register', function (\Illuminate\Http\Request $request) {
    // Validate the request data
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
    ]);

    // Create the user — konto jest nieaktywne do czasu potwierdzenia kodem z maila
    $user = \App\Models\User::create([
        'name' => $validatedData['name'],
        'email' => $validatedData['email'],
        'password' => bcrypt($validatedData['password']),
    ]);

    $user->sendEmailVerificationCode();

    $request->session()->put(EmailVerificationController::SESSION_KEY, $user->id);

    return redirect()->route('verification.notice')
        ->with('success', 'Wysłaliśmy kod weryfikacyjny na ' . $user->email . '.');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (\Illuminate\Http\Request $request) {
    // Validate the request data
    $credentials = $request->validate([
        'email' => 'required|string|email',
        'password' => 'required|string',
    ]);

    // Attempt to log the user in
    if (auth()->attempt($credentials)) {
        $user = auth()->user();

        // Konto bez potwierdzonego maila wraca na ekran z kodem
        if (! $user->hasVerifiedEmail()) {
            auth()->logout();
            $request->session()->regenerate();
            $request->session()->put(EmailVerificationController::SESSION_KEY, $user->id);
            $user->sendEmailVerificationCode();

            return redirect()->route('verification.notice')
                ->with('info', 'Najpierw potwierdź swój email — wysłaliśmy nowy kod na ' . $user->email . '.');
        }

        // Authentication passed...
        $request->session()->regenerate();

        return redirect('/')->with('success', 'Zalogowano pomyślnie!');
    }

    // Authentication failed...
    return back()->withErrors([
        'email' => 'Wprowadzone dane logowania są nieprawidłowe.',
    ]);
});

Route::get('/verify-email', [EmailVerificationController::class, 'show'])->name('verification.notice');
Route::post('/verify-email', [EmailVerificationController::class, 'verify'])->name('verification.verify');
Route::post('/verify-email/resend', [EmailVerificationController::class, 'resend'])->name('verification.resend');

Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');

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

    $watchStatus = null;

    if (Storage::disk('public')->exists($room->id)) {
        $fileExists = true;
        $watchStatus = Redis::get('room:' . $room->id . ':watch_status');
    } else {
        $fileExists = false;
    }

    return view('rooms.show', compact('room', 'users', 'messages', 'fileExists', 'watchStatus'));
})->name('rooms.show');

Route::post('rooms/uploadVideo/{room}', function (\Illuminate\Http\Request $request, \App\Models\Room $room) {
    $user = auth()->user();
    if ($room->owner_id !== $user->id) {
        return redirect()->route('rooms.show', $room->id)->with('error', 'Nie masz uprawnień do przesyłania wideo do tego pokoju.');
    }

    $request->validate([
        'video' => 'required|file|mimetypes:video/mp4', // 2GB limit
    ]);

    $videoFile = $request->file('video');

    $path = Storage::disk('public')->putFileAs(
        null,
        $videoFile,
        $room->id,
    );

    return redirect()->route('rooms.show', $room->id)->with('success', 'Wideo zostało przesłane pomyślnie!');
})->name('rooms.uploadVideo');

Route::get('rooms/watch/{room}', function (\App\Models\Room $room) {
    $user = auth()->user();
    $isOwner = $user &&$room->owner_id === $user->id;

    $watchStatus = Redis::get('room:' . $room->id . ':watch_status');
    if($watchStatus !== 'active') {
        return redirect()->route('rooms.show', $room->id)->with('error', 'Transmisja jeszcze nie została rozpoczęta.');
    }

    return view('rooms.watch', compact('room', 'isOwner', 'watchStatus'));
})->name('rooms.watch');

Route::get('rooms/startStream/{room}', function (\App\Models\Room $room) {
    $user = auth()->user();
    if ($room->owner_id !== $user->id) {
        return redirect()->route('rooms.show', $room->id)->with('error', 'Nie masz uprawnień do rozpoczęcia transmisji w tym pokoju.');
    }

    // Set the watch status in Redis to true
    Redis::set('room:' . $room->id . ':watch_status', "active");

    return redirect()->route('rooms.show', $room->id)->with('success', 'Transmisja została rozpoczęta pomyślnie!');
})->name('rooms.startStream');

Route::get('rooms/stopStream/{room}', function (\App\Models\Room $room) {
    $user = auth()->user();
    if ($room->owner_id !== $user->id) {
        return redirect()->route('rooms.show', $room->id)->with('error', 'Nie masz uprawnień do zakończenia transmisji w tym pokoju.');
    }

    // Set the watch status in Redis to false
    Redis::set('room:' . $room->id . ':watch_status', false);

    return redirect()->route('rooms.show', $room->id)->with('success', 'Transmisja została zakończona pomyślnie!');
})->name('rooms.stopStream');

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