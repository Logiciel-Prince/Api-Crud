<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    public $fillable = [
        'title',
        'parent_id'
    ];


    public function children()
    {
        $collect=$this->hasMany(Category::class, 'parent_id')
        ->with('children');
        return $collect;
    }

}
