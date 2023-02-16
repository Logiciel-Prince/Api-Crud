<?php

namespace App\Listeners;

use App\Events\FacebookPostEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class FacebookPostListener
{
    Public $access_token;
    /**
     * Create the event listener.
     *
     * @return void
     */
     //-----------------------------This Constructor get the facebook page access token---------------------------//
    public function __construct()
    {
            if(!Auth::check()) 
            {
                return response()->json([
                    'message' => 'Please Login first '
                ]);
            }
            
            if(!empty(auth()->user()->token))
            {
                $request = Http::get('https://graph.facebook.com/v16.0/me/accounts?access_token='.auth()->user()->token);
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
     * Handle the event.
     *
     * @param  \App\Events\FacebookPostEvent  $event
     * @return void
     */
    public function handle(FacebookPostEvent $event)
    {
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
