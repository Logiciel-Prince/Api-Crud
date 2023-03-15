<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Transformers\FolderTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FolderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $folder = Folder::where('parent_id', 1)->with('children')->get();

        return fractal($folder,new FolderTransformer());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
         
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $validate = Validator::make($input, [
            'name' => 'required|unique:folders|min:3',
        ]);

        if($validate->fails()){
            return response()->json([
                'message' =>$validate->errors(),
            ],412);
        }
        $input['parent_id'] = empty($input['parent_id']) ? 1 : $input['parent_id'];

        $path = Folder::where('id',$input['parent_id'])->first();
        
        if(!$path){
            $path = Folder::first();
            if(!$path)
            {
                $path = collect([
                    'path' => public_path('/storage/images/')
                ]);
                $path = json_decode (json_encode ($path), FALSE);
                $input['parent_id'] = null;
            }
        }

        File::makeDirectory($path->path.$request->name,$mode = 0777, true, true);

        $folder = Folder::create([
            'name' => $request->name,
            'parent_id' => $input['parent_id'],
            'path' => $path->path.$request->name.'/'
        ]);

        return response()->json([
            'message' => 'Folder Created Successful',
            'Folder' => $folder->orderBy('id','desc')->first()
        ],201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required|unique:folders|min:3',
            'parent_id' => 'required'
        ]);

        if($validate->fails()){
            return response()->json([
                'message' =>$validate->errors(),
            ],412);
        }
        $folder = Folder::where('id',$id)->first();
        $parent_path = Folder::where('id',$request->parent_id)->first();
        if($folder)
        {
            File::copyDirectory($folder->path,$parent_path->path.$request->name);
            File::deleteDirectory($folder->path);
            $folder->update([
                'name' => $request->name,
                'path' => $parent_path->path.$request->name.'/'
            ]);
            return response()->json([
                'message' => 'Folder Updated Successful',
            ],201);
        }
        return response()->json([
            'message' => 'Folder Not Found ',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $folder = Folder::where('id',$id)->first();

        if($folder)
        {
            File::deleteDirectory($folder->path);
            $folder->delete();
            return response()->json([
                'message' => 'Folder Deleted Successful',
            ],202);
        }
        return response()->json([
            'message' => 'Folder Not Found',
        ],201);
    }

     /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        $folder = Folder::onlyTrashed()->where('id',$id)->restore();

        if($folder == 1)
        {
            $path = Folder::where('id',$id)->first();
            File::makeDirectory($path->path,$mode = 0777, true, true);
            return response()->json([
                'message' => 'Folder Restored Successful',
            ],202);
        }
        return response()->json([
            'message' => 'Folder Not Found',
        ],201);
    }
}
