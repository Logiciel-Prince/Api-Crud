<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;


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
