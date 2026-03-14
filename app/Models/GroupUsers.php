<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupUsers extends Model
{
    use HasFactory;

    protected $table    = 'group_users';
    protected $fillable = ['group_id', 'user_id', 'role', 'status'];

    /**
     * The group this record belongs to.
     */
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    /**
     * Alias used in existing blade (groupuser->groups is iterated).
     * Returns a collection-like HasMany so the blade @foreach still works.
     */
    public function groups()
    {
        return $this->hasMany(Group::class, 'id', 'group_id');
    }

    /**
     * The user this pivot record belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Messages this user has seen (used for unread badge count).
     */
    public function seenMessages()
    {
        return $this->hasMany(MessageSeen::class, 'group_user_id', 'user_id');
    }
}
