<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Groups this user belongs to (via group_users pivot).
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_users', 'user_id', 'group_id')
                    ->withTimestamps();
    }

    /**
     * Groups this user has created.
     */
    public function createdGroups()
    {
        return $this->hasMany(Group::class, 'creatorId');
    }

    /**
     * Messages this user has sent in groups.
     */
    public function groupMessages()
    {
        return $this->hasMany(GroupMessages::class, 'from_id');
    }
}
