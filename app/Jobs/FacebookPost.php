<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class FacebookPost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    Public $access_token;

    Public $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;

        if(!Auth::check()) 
        {
            return response()->json([
                'message' => 'Please Login first '
            ]);
        }
        
        if(!empty(auth()->user()->token))
        {
            $request = Http::get(env('GRAPH_API_URL').'me/accounts?access_token='.auth()->user()->token);
        }
        if(array_key_exists('error',$request->json()))
        {
            return response()->json([
                'message' => 'Invalid access_token or Your access_token may be expired',
            ],401);
        }
        $this->access_token = $request['data'][1]['access_token'];  
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $event = $this->data;
        if(array_key_exists('0',$event->data))
        {
            $image = $event->data[0]['image'];
            $imageName = $event->data[1];
            $response = Http::attach('attachment',file_get_contents($image),$imageName)->post(env('GRAPH_API_URL').'me/photos?access_token='.$this->access_token.'&message='.$event->data[0]['desc']);
            return $response;
        }
        else{
            $response =  Http::post(env('GRAPH_API_URL').'me/feed?access_token='.$this->access_token.'&message='.$event->data['desc']);
            return $response;
        }
    }
}
