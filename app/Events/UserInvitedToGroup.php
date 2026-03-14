<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserInvitedToGroup implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user_id;
    public $group_id;
    public $group_name;
    public $group_image;

    public function __construct($user_id, $group_id, $group_name, $group_image)
    {
        $this->user_id     = $user_id;
        $this->group_id    = $group_id;
        $this->group_name  = $group_name;
        $this->group_image = $group_image;
    }

    public function broadcastOn()
    {
        // Using a public channel for simplicity in this dev environment
        return new Channel('user-invitations.' . $this->user_id);
    }

    public function broadcastAs()
    {
        return 'user-invited';
    }

    public function broadcastWith()
    {
        return [
            'group_id'    => $this->group_id,
            'group_name'  => $this->group_name,
            'group_image' => $this->group_image,
        ];
    }
}
