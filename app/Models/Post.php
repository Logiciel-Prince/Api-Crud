<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\{Category,Comment};

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'desc',
        'image',
        'category_id',
        'postfbid'
    ];
    public function category()
    {
        $collect=$this->belongsTo(Category::class,'category_id');
        return $collect;
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

}
