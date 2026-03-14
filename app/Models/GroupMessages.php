<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupMessages extends Model
{
    use HasFactory, HasUuids;

    // UUID primary key
    protected $keyType    = 'string';
    public    $incrementing = false;

    protected $fillable = [
        'group_id',
        'from_id',
        'to_id',
        'body',
        'attachment',
        'seen',
    ];

    /**
     * The group this message belongs to.
     */
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    /**
     * The user who sent this message.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'from_id');
    }

    /**
     * Users who have seen this message.
     */
    public function seenByUsers()
    {
        return $this->hasMany(MessageSeen::class, 'group_message_id');
    }
}
