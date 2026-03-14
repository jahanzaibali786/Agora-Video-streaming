<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast a new group message over Pusher.
 * Channel: group-chat.{group_id}   Event name: group-chat
 * (matches what the blade JS already subscribes to)
 */
class GroupMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int    $group_id;
    public int    $from_id;
    public string $message;
    public string $user_name;
    public string $message_id;

    public function __construct(int $group_id, int $from_id, string $message, string $user_name, string $message_id)
    {
        $this->group_id   = $group_id;
        $this->from_id    = $from_id;
        $this->message    = $message;
        $this->user_name  = $user_name;
        $this->message_id = $message_id;
    }

    public function broadcastOn()
    {
        return new Channel('group-chat.' . $this->group_id);
    }

    public function broadcastAs()
    {
        return 'group-chat';
    }

    public function broadcastWith()
    {
        return [
            'group_id'   => $this->group_id,
            'from_id'    => $this->from_id,
            'message'    => $this->message,
            'user_name'  => $this->user_name,
            'message_id' => $this->message_id,
        ];
    }
}
