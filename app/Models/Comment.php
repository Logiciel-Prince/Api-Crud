<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\{User,Post};

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_id',
        'message',
    ];

    Public Function user(){
        return $this->belongsTo(User::class);
    }

    Public Function post(){
        return $this->belongsTo(Post::class);
    }
}
