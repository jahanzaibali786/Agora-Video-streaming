<?php

namespace App\Events;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessage implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $streamKey,
        public string $name,
        public int    $userId,
        public string $message
    ) {}

    public function broadcastOn(): Channel { return new Channel('stream'); }

    public function broadcastWith(): array
    {
        return [
            'stream_key' => $this->streamKey,
            'name'       => $this->name,
            'user_id'    => $this->userId,
            'message'    => $this->message,
        ];
    }

    public function broadcastAs(): string { return 'ChatMessage'; }
}