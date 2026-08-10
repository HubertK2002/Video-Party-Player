<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('public-messages', function () {
    return true;
});

Broadcast::channel('room-test.{roomId}', function ($user, $roomId) {
    return true;
});