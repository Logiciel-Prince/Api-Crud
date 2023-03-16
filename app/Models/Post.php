<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\{Category,Comment};
// use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'desc',
        'image',
        'category_id',
        'folder_id',
        'page_id'
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
    public function pages()
    {
        return $this->belongsTo(FacebookPage::class,'page_id');
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

}
