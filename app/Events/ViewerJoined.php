<?php

namespace App\Events;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ViewerJoined implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $streamKey,
        public string $uid,
        public string $name,
        public int    $userId
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('stream'); // public channel — all viewers + streamer see it
    }

    public function broadcastWith(): array
    {
        return [
            'stream_key' => $this->streamKey,
            'uid'        => $this->uid,
            'name'       => $this->name,
            'user_id'    => $this->userId,
        ];
    }

    public function broadcastAs(): string { return 'ViewerJoined'; }
}
