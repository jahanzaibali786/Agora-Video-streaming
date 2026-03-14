<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // ← changed
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IncomingCall implements ShouldBroadcastNow // ← changed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $caller;
    public $recipientId;
    public $channelName;
    public $callType;

    public function __construct($caller, $recipientId, $channelName, $callType = 'audio')
    {
        $this->caller      = $caller;
        $this->recipientId = $recipientId;
        $this->channelName = $channelName;
        $this->callType    = $callType;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->recipientId);
    }

    public function broadcastWith()
    {
        return [
            'caller' => [
                'id'   => $this->caller->id,
                'name' => $this->caller->name,
            ],
            'channel_name' => $this->channelName,
            'callType'     => $this->callType,
        ];
    }

    public function broadcastAs()
    {
        return 'incoming-call';
    }
}