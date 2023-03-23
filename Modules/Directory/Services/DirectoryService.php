<?php


namespace Modules\Directory\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Directory\Entities\Directory;

// all of about directories
class DirectoryService extends Service
{

    // Directory Model
    private $model;

    public function __construct()
    {
        parent::__construct();

        $this->model = new Directory();
        
    }

    public function listDirectories(Directory $directory, $recursive = false)
    {
        if ($recursive) {
            $dirs = collect($this->disk->allDirectories($directory->path));
        }

        $dirs = collect($this->disk->directories($directory->path));

        return $dirs;
    }

    public function createDirectory(array $data)
    {
        // dd(Storage::allDirectories($this->base_directory));
        if($data['parent_id']){
            $path = Directory::where('id',$data['parent_id'])->first();
            $this->base_directory = $path->path ;
            
        }
        $path = $this->base_directory . $this->ds . $data['name'];
        if (!checkPath($path, $this->disk_name)) {
            if ($this->disk->makeDirectory($path)) {
                DB::transaction(function () use ($data, $path) {
                    $this->model->create([
                        //                'user_id' => user()->id,
                        'name' => $data['name'],
                        'description' => $data['description'],
                        'path' => $path,
                        'parent_id' => $data['parent_id'],
                        'disk' => $this->disk_name,
                    ]);
                });
                return true;
            } else {

                //                $this->error('Directory "' . $directory . '" already exists.');
                //                $this->error('Can not create directory.');
                return false;
            }
        }

        return false;
    }

    public function renameDirectory(Directory $directory, $newName)
    {
        if ($directory->name == $newName) return false;


        $path = $this->base_directory . $this->ds . $directory->name;
        if ($this->disk->exists($path)) {
            DB::transaction(function () use ($directory, $newName) {
                $directory->update([
                    'name' => $newName
                ]);
            });

            if ($this->disk->move($directory->name, $newName)) return true;
        };
        return false;
    }

}
