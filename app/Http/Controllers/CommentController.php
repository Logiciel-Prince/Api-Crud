<?php

namespace App\Http\Controllers;

use App\Events\FacebookCommentEvent;
use App\Events\FacebookDeleteCommentEvent;
use App\Events\FacebookUpdateCommentEvent;
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

    //-----------------------------This function Create the comment on facebook posts---------------------------//

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
        if(auth()->user()->token)
        {
            event (new FacebookCommentEvent(['data' => $request->all(),'message'=>$data]));
        }
                return response()->json([
                'message' => 'Comment Posted Successful',
                'comment' => $data
                            ->orderBy('id', 'desc')
                            ->first(),
            ]);
    }

    //-----------------------------This function Create the comment on facebook posts---------------------------//

    public function update(Request $request,$id){
        $validate = Validator::make($request->all(), [
            'message' => 'required',
        ]);
        
        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors(),
            ], 412);
        }

        $data = Comment::where('user_id',auth()->user()->id)
                ->find($id);
        if(!empty($data))
        {
            $comment = [
                'message' => $request->message
            ];
            event(new FacebookUpdateCommentEvent(['data' => $data,'message' => $comment]));
            $data -> update($comment);
            return response()->json([
                'message'=>'Comment Updated Successful'
            ],201);

        }
        return response()->json([
            'message'=>'Comment Not Found in Your Account'
        ],404);
    }

    //-----------------------------This function Delete the comment on facebook posts---------------------------//

    public function destroy($id){
        $data = Comment::where('user_id',auth()->user()->id)
                ->find($id);
        if(!empty($data))
        {
            $response = event(new FacebookDeleteCommentEvent(['data'=>$data]));
            $data -> delete();
            return response()->json([
                'message'=>'Comment Deleted Successful'
            ],202);
        }
        return response()->json([
            'message'=>'Comment Not Found in Your Account'
        ],404);
    }
}
