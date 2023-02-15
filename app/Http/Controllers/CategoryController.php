<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Http\Resources\CategoryResource;
use Illuminate\Support\Facades\Validator;
use App\Transformers\CategoryTransformer;

class CategoryController extends Controller
{
    //---------------------This function show the Categories In Hierarchical form--------------------------//
    public function manageCategory()
    {
        $categories = Category::where('parent_id', '=', 1)->with('children')->get();
        // return CategoryResource::collection($categories);
       return fractal($categories,new CategoryTransformer());
    }

    //---------------------This function add The new Categories Inside root or other Categories--------------------------//
    public function addCategory(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'title' => 'required',
        ]);

        if($validate->fails()){
            return response()->json([
                'message' =>$validate->errors(),
            ],412);
        }

        $input = $request->all();
        $input['parent_id'] = empty($input['parent_id']) ? 1 : $input['parent_id'];

        $data = Category::create($input);
        return response()->json([
            'message' => 'Category Added Successful',
            'category' => $data->orderBy('id','desc')->first()
        ],201);

    }

    //---------------------This function delete The selected Category--------------------------//
    Public Function deleteCategory($id){
        $ob = Category::where('id',$id)->with('children')->get();
        if(empty($ob[0]->children->toArray()))
        {
            Category::where('id',$id)->delete();
            return response()->json([
                'message' => 'Category Deleted Successful'
            ],202);
        }
        else{
            return response()->json([
                'message' => 'Selected Category cannot be deleted'
            ],400);
        }
    }

    //---------------------This function update The existing Categories --------------------------//
    Public Function updateCategory(Request $request,$id){
        $ob = Category::where('id',$id);
        if(!empty($ob))
        {
            $data = $ob -> update(['title' => $request->title]);
            return response()->json([
                'message' => 'Category updated Successfull',
            ],202);
        }
    }
}
