<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'profile_image', 'creatorId', 'limit'];

    /**
     * The creator of the group.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creatorId');
    }

    /**
     * All users who are members of this group (via pivot table).
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'group_users', 'group_id', 'user_id')
                    ->withTimestamps();
    }

    /**
     * GroupUsers pivot records for this group.
     */
    public function participants()
    {
        return $this->hasMany(GroupUsers::class, 'group_id');
    }

    /**
     * All messages in this group.
     */
    public function messages()
    {
        return $this->hasMany(GroupMessages::class, 'group_id');
    }

    /**
     * All files shared in this group.
     */
    public function files()
    {
        return $this->hasMany(GroupFile::class, 'group_id');
    }
}
