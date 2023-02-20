<?php

namespace App\Http\Controllers;

use App\Events\FacebookCommentEvent;
use App\Models\Comment;
use App\Models\Post;
use App\Transformers\CommentTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{ 

    //-----------------------------This function get all the comment ---------------------------//

    public function index()
    {
        $data = Comment::with('post')
                ->get();
        return fractal($data,new CommentTransformer());
    }

    //-----------------------------This function create and post the comment on facebook posts---------------------------//

    public function create(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'post_id' => 'required',
            'message' => 'required',
        ]);
        
        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors(),
            ], 412);
        }

        $data = Comment::create([
                'user_id' => auth()->user()->id,
                'post_id' => $request->post_id,
                'message' => $request->message
            ]);
        $postid = Post::where('id', $request->id)->first();
        if(auth()->user()->token)
        {
            event (new FacebookCommentEvent(['data' => $request->all()]));
                return response()->json([
                'message' => 'Comment Posted Successful',
                'comment' => $data
                            ->orderBy('id', 'desc')
                            ->first(),
            ]);
        }
        return response()->json([
                'message' => 'Comment Posted Successful',
                'comment' => $data
                            ->orderBy('id', 'desc')
                            ->first(),
            ]);
        }
}
