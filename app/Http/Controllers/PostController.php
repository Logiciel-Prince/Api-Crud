<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transformers\PostTransformer;
use Illuminate\Support\Facades\Validator;
use App\Models\{
    Post,
    Category,
    FacebookPage,
    Folder
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
                    ->where('id',$id)
                    ->with('pages')
                    ->first();
        if($data)
        {
            $post = [
                'title' => $request->title,
                'desc' => $request->desc,
            ];
            event(new FacebookUpdatePostEvent([
                    'data'=>$data,
                    'message' => $request->all()
                ])
            );
            $data->update($post);

            return response()->json([
                'message'=>'Post Updated Successful'
            ],201);
        }
        return response()->json([
            'message'=>'Post not Available in Your Account'
        ],404);

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

        if(auth()->user()->role == 'SuperAdmin'){
            $post = Post::where('title','like','%'.$request->title.'%')->get();
        }
        else{
            $post = Post::where('user_id',auth()->user()->id)
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
        'category' => 'required|exists:categories,title',
        'folder' => 'required|exists:folders,name',
        'pagename' => 'exists:facebook_pages,page_name'
    ]);
    if($validate->fails()){
        return response()->json([
            'message' =>$validate->errors(),
        ],412);
    }
    $page = FacebookPage::where('page_name',$request->pagename)
            ->where('user_id',auth()->user()->id)
            ->first();

    $category = Category::where('title','like','%'.$request->category.'%')->first('id');
    
    $imageName = null;
    
    if($request->hasFile('image'))
    {
        $folder = Folder::where('name','like','%'.$request->folder.'%')->first();

        $imageName = time().'.'.$request->image->extension();
        
        $request->image->move($folder->path, $imageName);
    }
    $post = Post::create([
        'user_id' => auth()->user()->id,
        'page_id' => $page->id,
        'title' => $request->title,
        'desc' => $request->desc,
        'image' => $imageName,
        'category_id' => $category->id,
        'folder_id' => $folder->id,
    ]);
    event (new FacebookPostEvent([
        'data' => $request->only(['title','desc','category','pagename']),
        'imageName' => $imageName,
        'post' => $post
    ]));
    
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
            $post = Post::where('user_id',auth()->user()->id)
                        ->with('comments')
                        ->with('category')
                        ->first();
            return [$post];
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
                ->where('id',$id)
                ->with('folder')
                ->first();
        if($data)
        {
            if($data->image != null)
            {
                unlink($data->folder->path.$data->image);
            }
            event(new FacebookDeletePostEvent([
                'data'=>$data
            ]));
            $data -> delete();
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
