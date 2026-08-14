<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('public-messages', function () {
    return true;
});

Broadcast::channel('room.{roomId}', function ($user, $roomId) {
    return true;
});

Broadcast::channel('chat-room.{roomId}', function ($user, $roomId) {
    return true;
});