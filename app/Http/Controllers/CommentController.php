<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Transformers\CommentTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    //-----------------------------This Constructor get the facebook page access token---------------------------//
    public $access_token;

    public function __construct()
    {

        $this->middleware(function ($reques, $next) {
            if (!Auth::check()) {
                return response()->json([
                    'message' => 'Please Login first '
                ]);
            } else {
                if (empty(auth()->user()->token)) {
                    return $next($reques);
                } else {
                    $request = Http::get('https://graph.facebook.com/v15.0/me/accounts?access_token=' . auth()->user()->token);
                    if (array_key_exists('error', $request->json())) {
                        return response()->json([
                            'message' => 'Invalid access_token or Your access_token may be expired',
                        ], 401);
                    } else {
                        $this->access_token = $request['data'][1]['access_token'];
                    }
                }
            }
            return $next($reques);
        });
    }

    //-----------------------------This function get all the comment ---------------------------//

    public function index()
    {
        $data = Comment::with('post')->get();
        // return response()->json([
        //     'Data' => $data
        // ]);
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
            if (!empty($this->access_token)) {
                $post = Post::where('id', $request->post_id)->get();
                $post_id = $post[0]['postfb_id'];
                $response =  Http::get('https://graph.facebook.com/v16.0/' . $post_id . '?access_token=' . $this->access_token);
                $postid = Post::where('postfb_id', $response->json()['id'])->first();
                $response =  Http::post('https://graph.facebook.com/v16.0/' . $postid->toArray()['postfb_id'] . '/comments?message=' . $request->message . '&access_token=' . $this->access_token);
                return response()->json([
                    'message' => 'Comment Posted Successful',
                    'comment' => $data->orderBy('id', 'desc')->first(),
                    'status' => $response->json()
                ]);
            } 

            return response()->json([
                    'message' => 'Comment Posted Successful',
                    'comment' => $data->orderBy('id', 'desc')->first(),
                ]);
            }
}
