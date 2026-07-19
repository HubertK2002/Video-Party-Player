<?php

use Illuminate\Support\Facades\Route;
use App\Events\MessageSent;

Route::get('/', function () {
    return view('welcome');
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
