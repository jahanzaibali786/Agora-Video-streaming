<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IncomingCall implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $caller;
    public $recipientId;
    public $channelName;

    public function __construct($caller, $recipientId, $channelName)
    {
        $this->caller = $caller;
        $this->recipientId = $recipientId;
        $this->channelName = $channelName;
    }

    public function broadcastOn()
    {
        return new Channel('user.' . $this->recipientId); 
    }

    // public function broadcastWith()
    // {
    //     return [
    //         'caller' => $this->caller,
    //         'channelName' => $this->channelName,
    //     ];
    // }
    public function broadcastWith()
    {
        return [
            'caller' => [
                'id' => $this->caller->id,
                'name' => $this->caller->name,
            ],
            'channel_name' => $this->channelName,
        ];
    }
    public function broadcastAs()
    {
        return 'incoming-call';
    }
}
