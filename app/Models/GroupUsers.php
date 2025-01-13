<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupUsers extends Model
{
    use HasFactory;
    public function groups()
    {
        return $this->hasMany(Group::class, 'id','group_id');
    }
    public function seenMessages()
{
    return $this->hasMany(MessageSeen::class, 'group_user_id','user_id');
}

}

