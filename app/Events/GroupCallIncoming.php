<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupCallIncoming implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $groupId;
    public $channelName;
    public $callerName;
    public $callerId;
    public $mediaType;
    public $groupName;
    public $groupImage;

    public function __construct($groupId, $channelName, $callerName, $callerId, $mediaType, $groupName, $groupImage)
    {
        $this->groupId = $groupId;
        $this->channelName = $channelName;
        $this->callerName = $callerName;
        $this->callerId = $callerId;
        $this->mediaType = $mediaType;
        $this->groupName = $groupName;
        $this->groupImage = $groupImage;
    }

    public function broadcastOn()
    {
        return new Channel('group-chat.' . $this->groupId);
    }

    public function broadcastAs()
    {
        return 'incoming-group-call';
    }

    public function broadcastWith()
    {
        return [
            'group_id' => $this->groupId,
            'channel_name' => $this->channelName,
            'caller_name' => $this->callerName,
            'caller_id' => $this->callerId,
            'media_type' => $this->mediaType,
            'group_name' => $this->groupName,
            'group_image' => $this->groupImage,
        ];
    }
}
