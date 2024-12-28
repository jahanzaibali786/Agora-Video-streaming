<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StreamStopped
{
    use InteractsWithSockets, SerializesModels;

    public $streamKey;

    public function __construct($streamKey)
    {
        $this->streamKey = $streamKey;
    }

    public function broadcastOn()
    {
        return new Channel('stream');
    }

    public function broadcastWith()
    {
        return ['streamKey' => $this->streamKey];
    }
}
