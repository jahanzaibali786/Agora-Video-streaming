<?php

namespace App\Events;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ViewerLeft implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public string $streamKey, public string $uid) {}

    public function broadcastOn(): Channel { return new Channel('stream'); }

    public function broadcastWith(): array
    {
        return ['stream_key' => $this->streamKey, 'uid' => $this->uid];
    }

    public function broadcastAs(): string { return 'ViewerLeft'; }
}

