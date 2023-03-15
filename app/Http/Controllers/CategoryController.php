<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use App\Transformers\CategoryTransformer;

class CategoryController extends Controller
{
    //--------------------This function show the Categories In Hierarchical form--------------------------//

    public function manageCategory()
    {
        $categories = Category::where('parent_id', '=', 1)
                                ->with('children')
                                ->get();

       return fractal($categories,new CategoryTransformer());
    }

    //---------------------This function add The new Categories Inside root or other Categories--------------------------//
    
    public function addCategory(Request $request)
    {
        $input = $request->all();
        $validate = Validator::make($input, [
            'title' => 'required|unique:categories',
        ]);

        if($validate->fails()){
            return response()->json(
                ['message' =>$validate->errors()],
                412
            );
        }
        
        $input['parent_id'] = empty($input['parent_id']) ? 1 : $input['parent_id'];

        $path = Category::where('id',$input['parent_id'])->first();
        
        if(!$path){
            $path = Category::first();
            if(!$path)
            {
                $input['parent_id'] = null;
            }
        }

        $data = Category::create($input);
        return response()->json([
            'message' => 'Category Added Successful',
            'category' => $data->orderBy('id','desc')->first()
        ],201);

    }

    //---------------------This function delete The selected Category--------------------------//

    public function deleteCategory($id)
    {
        $ob = Category::where('id',$id)
                        ->with('children')
                        ->get();
        if(empty($ob[0]->children->toArray()))
        {
            Category::where('id',$id)->delete();
            return response()->json([
                'message' => 'Category Deleted Successful'
            ],202);
        }
        return response()->json([
            'message' => 'Selected Category cannot be deleted'
        ],400);
    }

    //---------------------This function update The existing Categories --------------------------//

    public function updateCategory(Request $request,$id)
    {
        $ob = Category::where('id',$id);
        if(!empty($ob))
        {
            $ob -> update(['title' => $request->title]);
            return response()->json([
                'message' => 'Category updated Successfull',
            ],202);
        }
    }
}
