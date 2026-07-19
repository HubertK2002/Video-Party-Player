<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('public-messages', function () {
    return true;
});
