<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('public-messages', function () {
    return true;
});

Broadcast::channel('room.{roomId}', function ($user, $roomId) {
    // Check if the user is part of the room
    return $user->rooms()->where('rooms.id', $roomId)->exists();
});
