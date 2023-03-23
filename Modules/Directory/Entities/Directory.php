<?php

namespace Modules\Directory\Entities;

use Modules\Directory\Traits\HasUUID;
use Illuminate\Database\Eloquent\Model;
use Modules\Directory\Traits\RouteKeyNameUUID;

class Directory extends Model
{
    use HasUUID,
        RouteKeyNameUUID;

    // protected $table = 'filemanager_directories';
    protected $table = 'filemanager_directories';

    // protected $fillable = ['name', 'uuid'];

    protected $guarded = [];

    public function parent()
    {
        $data = $this->hasMany(Directory::class,'parent_id')->with('parent');
        return $data;
    }

    // who created ?
    public function user()
    {
        return $this->belongsTo(config('filemanager.database.user_model'), 'user_id');
    }
}
