<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transformers\PostTransformer;
use Illuminate\Support\Facades\Validator;
use App\Models\{
    Post,Category
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
        $user = auth()->user()->id;
        $data = Post::where('user_id',$user)
                    ->find($id);
        if($data != null)
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
            if($request->hasFile('image'))
            {
                $imageName = time().'.'.$request->image->extension();
                $request->image->storeAs('public/images/', $imageName);
                unlink(public_path('storage/images/'.$data->image));
                event(new FacebookUpdatePostEvent([
                        'data'=>$data->toArray(),
                        'message' => $request->only('title','desc'),
                        'imageName' => $imageName,
                    ])
                );
                $post = [
                    'title' => $request->title,
                    'desc' => $request->desc,
                    'image' => $imageName,
                ];
                $d=$data->update($post);
                if(!empty($d)){
                    return response()->json([
                        'message'=>'Post Updated Successful'
                    ],201);
                }
            }
            else
            {
                $post = [
                    'title' => $request->title,
                    'desc' => $request->desc,
                ];
                if(!empty(auth()->user()->token))
                {
                    $response = event(new FacebookUpdatePostEvent([
                            'data'=>$data->toArray(),
                            'message' => $request->all(),   
                        ])
                    );
                }
                $d=$data->update($post);
                return response()->json([
                    'message'=>'Post Updated Successful'
                ],201);
            }
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
    
    if($request->hasFile('image'))
    {
        $imageName = time().'.'.$request->image->extension();
        $request->image->storeAs('public/images/', $imageName);
        $post = Post::create([
            'user_id' => auth()->user()->id,
            'title' => $request->title,
            'desc' => $request->desc,
            'image' => $imageName,
            'category_id' => $category->id,
        ]);
        if(!empty(auth()->user()->token))
        {
            $response  = event (new FacebookPostEvent(['data' => $request->only(['title','desc','category','pagename']),'imageName' => $imageName]));
            return response()->json([
                'message' => 'Image Posted Successful',
                'Post' => $post->orderBy('id', 'desc')->first(),
                'Response From Facebook' => $response
            ],201);
        }
        return response()->json([
            'message' =>'Post Created Successfully',
            'Post' => $post->orderBy('id','desc')->first(),
            'response from facebook' => 'User Not Connected With Facebook'
        ],201);            
           
    }
    $post = Post::create([
        'user_id' => auth()->user()->id,
        'title' => $request->title,
        'desc' => $request->desc,
        'category_id' => $category->id,
    ]);
    if(!empty(auth()->user()->token))
    {
        $response = event(new FacebookPostEvent(['data'=>$request->all()]));
        return response()->json([
            'message' => 'Image Posted Successful',
            'Post' => $post->orderBy('id', 'desc')->first(),
            'Response From Facebook' => $response
        ],200);
    }
    return response()->json([
        'message' =>'Post Created Successfully',
        'Post' => $post->orderBy('id','desc')->first(),
        'response from facebook' => 'User Not Connected With Facebook'
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
        if($data != null)
        {
            if($data->image != null)
            {
                event(new FacebookDeletePostEvent(['data'=>$data]));
                unlink(public_path('storage/images/'.$data->image));
            }
            $data -> delete();
            return response()->json([
                'message'=>'Post Deleted Successful in Your Account'
            ],404);
        }
        else{
            return response()->json([
                'message'=>'Post not Available in Your Account'
            ],404);
        }
    }
}
