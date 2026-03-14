<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageSeen extends Model
{
    use HasFactory;

    protected $fillable = ['group_message_id', 'group_user_id', 'user_id'];

    /**
     * The group message this seen record belongs to.
     */
    public function groupMessage()
    {
        return $this->belongsTo(GroupMessages::class, 'group_message_id');
    }

    /**
     * The group-user (pivot) this seen record belongs to.
     */
    public function groupUser()
    {
        return $this->belongsTo(GroupUsers::class, 'group_user_id');
    }
}
