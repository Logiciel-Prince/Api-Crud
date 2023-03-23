<?php

namespace Modules\Directory\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Directory\Entities\Directory;
use Modules\Directory\Services\DirectoryService;
use Modules\Directory\Traits\ApiResponder;
use Modules\Directory\Transformers\DirectoryTransformer;

class DirectoryController extends Controller
{

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ApiResponder;
    
    private $directoryService;

    public function __construct(DirectoryService $directoryService)
    {
        $this->directoryService = $directoryService;
    }

    public function createDirectory(Request $request)
    {
        $data = [
            'name' => $request->get('name'),
            'description' => $request->has('description') ? $request->input('description') : null,
            'parent_id' => $request->has('parent_id') ? $request->input('parent_id') : null,
        ];

        if ($this->directoryService->createDirectory($data)) {
            $msg = trans('filemanager::messages.directory_created');
            return $this->responseSuccess($msg, 201, "Created");
        }

        return $this->responseError("Error in Directory create", 500);
    }

    public function deleteDirectories(Request $request)
    {
        if (is_array($request->get('directories'))) {
            foreach ($request->get('directories', []) as $key => $directory) {
                if (!$this->directoryService->deleteDirectory($directory)) {
                    return $this->responseError("Error in Directory Delete", 500);
                }
            }
        }
        
        if (!$this->directoryService->deleteDirectory($request->get('directories'))) {
            return $this->responseError("Error in Directory Delete", 500);
        }

        return $this->responseSuccess("Directories Deleted");
    }

    public function renameDirectory(Directory $directory, Request $request)
    {
        $name = $request->input('name');

        if (checkInstanceOf($directory, Directory::class)) {
            $this->directoryService->renameDirectory($directory,$name);
        }

        return response()->json(['msg' => 'Directory renamed.', 'status' => '200'], 200);
    }

    public function getUserDirectorys(Request $request)
    {
        $folder = $request->input('folder');

        if ($folder == 0) {
            // get the parent folders
            $folders = Directory::where('parent_folder', '0')->where('user_id', Auth::id())->orderBy('folder_name', 'asc')->get();
        } else {
            $folders = Directory::where('parent_folder', $folder)->where('user_id', Auth::id())->orderBy('folder_name', 'asc')->get();
        }

        return $folders->toJson();
    }

    public function getParentDirectory(Directory $directory)
    {
        return $directory->parent();
    }

    public function allDirectory(){
        $directory = Directory::where('parent_id',null)->with('parent')->get();
        return fractal($directory,new DirectoryTransformer());
    }
}
