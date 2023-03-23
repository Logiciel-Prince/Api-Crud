<?php

namespace Modules\Directory\Traits;

use Miladimos\FileManager\Models\File;

trait HasFile
{
    public function files()
    {
        return $this->morphMany(File::class);
    }
}
