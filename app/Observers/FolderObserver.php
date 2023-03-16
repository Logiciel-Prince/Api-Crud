<?php

namespace App\Observers;

use App\Models\Folder;
use Illuminate\Support\Facades\Log;

class FolderObserver
{
    /**
     * Handle the Folder "created" event.
     *
     * @param  \App\Models\Folder  $folder
     * @return void
     */
    public function created(Folder $folder)
    {
        //
    }

    /**
     * Handle the Folder "updated" event.
     *
     * @param  \App\Models\Folder  $folder
     * @return void
     */
    public function updated(Folder $folder)
    {
        $folder->children()->each(function ($childModel) use ($folder) {
            $childModel->update([
                'path' => $folder->path.$childModel->name.'/'
            ]);
        });
    }

    /**
     * Handle the Folder "deleted" event.
     *
     * @param  \App\Models\Folder  $folder
     * @return void
     */
    public function deleted(Folder $folder)
    {
         $folder->children()->each(function ($childModel) use ($folder) {
            $childModel->delete();
        });
    }

    /**
     * Handle the Folder "restored" event.
     *
     * @param  \App\Models\Folder  $folder
     * @return void
     */
    public function restored(Folder $folder)
    {
        //
    }

    /**
     * Handle the Folder "force deleted" event.
     *
     * @param  \App\Models\Folder  $folder
     * @return void
     */
    public function forceDeleted(Folder $folder)
    {
        //
    }
}
