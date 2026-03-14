<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallCancelled implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $recipientId;
    public string $channelName;

    public function __construct(int $recipientId, string $channelName)
    {
        $this->recipientId = $recipientId;
        $this->channelName = $channelName;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('user.' . $this->recipientId);
    }

    public function broadcastWith(): array
    {
        return ['channel_name' => $this->channelName];
    }

    public function broadcastAs(): string
    {
        return 'call-cancelled';
    }
}