<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transformers\PostTransformer;
use Illuminate\Support\Facades\Validator;
use App\Models\{
    Post,
    Category
};
use App\Events\{
    FacebookDeletePostEvent,
    FacebookPostEvent,
    FacebookUpdatePostEvent
};

class PostController extends Controller
{
       
//* <-----------------------This Route update Post from database------------------------------>

    public function updatePost(Request $request,$id)
    {   
        $validate = Validator::make($request->all(), [
                'title' => 'required|min:3',
                'desc' => 'required',
                'image' => 'mimes:png,jpg',
            ]);
        if($validate->fails()){
            return response()->json([
                'message' =>$validate->errors(),
            ],412);
        }
        $user_id = auth()->user()->id;
        $data = Post::where('user_id',$user_id)
                    ->find($id);
        if($data)
        {
            $post = [
                'title' => $request->title,
                'desc' => $request->desc,
            ];
            $data->update($post);
            event(new FacebookUpdatePostEvent([
                    'data'=>$data,
                    'message' => $request->all()
                ])
            );

            return response()->json([
                'message'=>'Post Updated Successful'
            ],201);
        }
        else{
            return response()->json([
                'message'=>'Post not Available in Your Account'
            ],404);
        }

    }
    //* <-----------------------This Route Search Post from database------------------------------>

    public function search(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'title' => 'required',
        ]);
        if($validate->fails()){
            return response()->json([
                'message' =>$validate->errors(),
            ],412);
        }
        $id = auth()->user()->id;
        if(auth()->user()->role == 'SuperAdmin')
        {
            $post = Post::where('title','like','%'.$request->title.'%')->get();
        }
        else{
            $post = Post::where('user_id',$id)
                    ->where('title','like','%'.$request->title.'%')
                    ->get(['id','title','desc','image']);
        }

        if($post != null)
        {
            return response()->json([
                'message'=>'Success',
                'post' => $post
            ],200);
        }
        return response()->json([
            'message'=>'Post Not Found',
        ],404);

    }
    //* <-----------------------This Route Upload the Post on database------------------------------>

public function upload(Request $request){
    
    $validate = Validator::make($request->all(), [
        'title' => 'required|unique:posts|min:3',
        'desc' => 'required',
        'image' => 'mimes:png,jpg|image|max:2048',
        'category' => 'required'
    ]);
    if($validate->fails()){
        return response()->json([
            'message' =>$validate->errors(),
        ],412);
    }
    $category = Category::where('title','like','%'.$request->category.'%')->first('id');
    if (empty($category))
    {
        return response()->json([
            'message' => 'Please Select available Category or create a new Category'
        ],404);
    }
    $imageName = null;
    if($request->hasFile('image'))
    {
        $imageName = time().'.'.$request->image->extension();
        $request->image->storeAs('public/images/', $imageName);
    }
    $post = Post::create([
        'user_id' => auth()->user()->id,
        'title' => $request->title,
        'desc' => $request->desc,
        'image' => $imageName,
        'category_id' => $category->id,
    ]);
    event (new FacebookPostEvent(['data' => $request->only(['title','desc','category','pagename']),'imageName' => $imageName]));
    return response()->json([
        'message' => 'Post Uploaded Successful',
        'Post' => $post->orderBy('id', 'desc')->first(),
    ],201);         
   
}

//* <-----------------------This Route get all the Post of user from database------------------------------>

    public function getUpload()
    {
        if(auth()->user()->role == 'SuperAdmin')
        {
            $data =Post::get();
        }
        else{
            $post = Post::where('user_id',auth()->user()->id)->with('comments')->with('category')->get();
            return fractal($post,new PostTransformer());
        }
        return response()->json([
            'message' =>'Success',
            'user' => $data
        ],200);

    }

//* <-----------------------This Route delete the selected Post of user from database------------------------------>

    public function deletePost($id){
        $data = Post::where('user_id',auth()->user()->id)
                ->find($id);
        if($data)
        {
            if($data->image != null)
            {
                unlink(public_path('storage/images/'.$data->image));
            }
            $data -> delete();
            event(new FacebookDeletePostEvent(['data'=>$data]));
            return response()->json([
                'message'=>'Post Deleted Successful in Your Account'
            ],200);
        }
        else{
            return response()->json([
                'message'=>'Post not Available in Your Account'
            ],404);
        }
    }
}
