<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class VideoControll implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $cmd;

    /**
     * Create a new event instance.
     */
    public function __construct($cmd)
    {
        $this->cmd = $cmd;
        Log::info('Broadcast: ' . json_encode($this->cmd));
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        
        return [
            new Channel('room.' . $this->cmd['roomId']),
        ];
    }

        public function broadcastAs()
    {
        return 'video.control';
    }
}
