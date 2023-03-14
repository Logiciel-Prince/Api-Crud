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
        'page_id',
        'post_id',
        'message',
    ];

    public Function user(){
        return $this->belongsTo(User::class);
    }

    public Function post(){
        return $this->belongsTo(Post::class);
    }
}
