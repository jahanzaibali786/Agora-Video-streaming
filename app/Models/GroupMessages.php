<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupMessages extends Model
{
    use HasFactory;
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    // GroupMessages.php
    public function user()
    {
        return $this->belongsTo(User::class, 'from_id');
    }

    public function seenByUsers()
    {
        return $this->hasMany(MessageSeen::class, 'group_message_id');
    }

}
