<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Post,Category};
use App\Transformers\Posttransformer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    //-----------------------------This Constructor get the facebook page access token---------------------------//

    Public $access_token;

    Public Function __construct(){

        $this->middleware(function ($reques, $next) {
            if(!Auth::check()) 
            {
                return response()->json([
                    'message' => 'Please Login first '
                ]);
            }
            
            if(empty(auth()->user()->token))
            {
                return $next($reques);
            }
            $request = Http::get('https://graph.facebook.com/v15.0/me/accounts?access_token='.auth()->user()->token);
            if(array_key_exists('error',$request->json()))
            {
                return response()->json([
                    'message' => 'Invalid access_token or Your access_token may be expired',
                ],401);
            }
            $this->access_token = $request['data'][1]['access_token'];
             
            return $next($reques);

        });
    }
//* <-----------------------This Route update Post from database------------------------------>

    Public Function updatePost(Request $request,$id)
    {   
        $user = auth()->user()->id;
        $data = Post::where('user_id',$user)
                    ->find($id);

        if($data != null)
        {

            $validate = Validator::make($request->all(), [
                    'title' => 'required|unique:posts|min:3',
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

                unlink(public_path('storage/images/'.$data->image));
                $imageName = time().'.'.$request->image->extension();
                // dd($imageName);
                $request->image->storeAs('public/images/', $imageName);
                $post = [
                    'title' => $request->title,
                    'desc' => $request->desc,
                    'image' => $imageName
                ];
                if(!empty($data)){
                    $d=$data->update($post);
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

    Public Function search(Request $request)
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
        
        if(!empty($this->access_token))
        {
            $response = Http::attach('attachment',file_get_contents($request->image),$imageName)->post('https://graph.facebook.com/v15.0/me/photos?access_token='.$this->access_token.'&message='.$request->desc);
            $post = Post::create([
                    'user_id' => auth()->user()->id,
                    'title' => $request->title,
                    'desc' => $request->desc,
                    'image' => $imageName,
                    'category_id' => $category->id,
                    'postfbid' => $response->json()['id']
                ]);
            return response()->json([
                'message' =>'Post Created Successfully',
                'user' => $post->orderBy('id','desc')->first(),
                'response from facebook' => $response->json()
            ],201);
        }    
        $post = Post::create([
            'user_id' => auth()->user()->id,
            'title' => $request->title,
            'desc' => $request->desc,
            'image' => $imageName,
            'category_id' => $category->id,
        ]);
        return response()->json([
            'message' =>'Post Created Successfully',
            'user' => $post->orderBy('id','desc')->first(),
            'response from facebook' => 'User Not Connected With Facebook'
        ],201);            
           
    }
    $post = Post::create([
        'user_id' => auth()->user()->id,
        'title' => $request->title,
        'desc' => $request->desc,
    ]);
    if(!empty($this->access_token))
    {
        $response =  Http::post('https://graph.facebook.com/v15.0/me/feed?access_token='.$this->access_token.'&message='.$request->desc);
        return response()->json([
            'message' =>'Post Created Successfully',
            'user' => $post->orderBy('id','desc')->first(),
            'response from facebook' => $response->json()
        ],201);
    }   
    return response()->json([
        'message' =>'Post Created Successfully',
        'user' => $post->orderBy('id','desc')->first(),
        'response from facebook' => 'User Not Connected With Facebook'
    ],201);  
}

//* <-----------------------This Route get all the Post of user from database------------------------------>

    Public Function getUpload()
    {
        if(auth()->user()->role == 'SuperAdmin')
        {
            $data =Post::get();
        }
        else{
            $post = Post::where('user_id',auth()->user()->id)->with('comments')->with('category')->get();
            return fractal($post,new Posttransformer());
        }
        return response()->json([
            'message' =>'Success',
            'user' => $data
        ],200);

    }
}
