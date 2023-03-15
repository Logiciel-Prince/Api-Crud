<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Folder extends Model
{
    use HasFactory , SoftDeletes;

    public $fillable = [
        'name',
        'parent_id',
        'path'
    ];


    public function children()
    {
        $collect=$this->hasMany(Folder::class, 'parent_id')
        ->with('children');
        return $collect;
    }
}
