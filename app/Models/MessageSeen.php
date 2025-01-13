<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageSeen extends Model
{
    use HasFactory;
    public function groupMessage()
    {
        return $this->belongsTo(GroupMessages::class, 'id');
    }

    public function groupUser()
    {
        return $this->belongsTo(GroupUsers::class, 'user_id');
    }

}
