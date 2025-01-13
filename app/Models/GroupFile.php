<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupFile extends Model
{
    use HasFactory;
    protected $fillable = ['group_id', 'file_path','user_id'];

    // GroupFiles.php
public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}

}
