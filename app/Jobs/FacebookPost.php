<?php

namespace App\Jobs;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class FacebookPost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $access_token;

    public $userToken;

    public $data;

    public $tries;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->tries = config('queue.connections.database.tries');
        $this->data = $data;
        $this->userToken = auth()->user()->token;
        
    }

    /**
     * Execute the job.
     *
     * @return 
     */
    public function handle()
    {
        $this->getAccessToken($this->userToken);
        try {
            $event = $this->data;
            if($event->data['imageName'])
            {
                $imageName = $event->data['imageName'];
                $image = public_path('storage/images/'.$imageName);
                $response = Http::attach('attachment',file_get_contents($image),$imageName)->post(env('GRAPH_API_URL').'me/photos?access_token='.$this->access_token.'&message='.$event->data['data']['desc']);
                Post::where('image',$imageName)
                        ->update(['postfbid' => $response->json('post_id')]);
                return true;
            }
            $response =  Http::post(env('GRAPH_API_URL').'me/feed?access_token='.$this->access_token.'&message='.$event->data['data']['desc']);
            Post::where('title',$event->data['data']['title'])
                 ->update(['postfbid' => $response->json('id')]);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private function getAccessToken($token){
        $request = Http::get(env('GRAPH_API_URL').'me/accounts?access_token='.$token);
        if(array_key_exists('error',$request->json()))
        {
            return response()->json([
                'message' => 'Invalid access_token or Your access_token may be expired',
            ],401);
        }
        if(array_key_exists('pagename',$this->data->data['data']))
        {
            $pageName = $this->data->data['data']['pagename'];
        }
        $name = empty($pageName) ? 'Api test' : $pageName;
        foreach($request['data'] as $d)
        {
            if($d['name'] == $name)
            {
                $this->access_token = $d['access_token'];  
            }
        }
    }
}
