<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class DeletePostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    Public $access_token;

    Public $data;

    public $tries = 2;
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
        if(array_key_exists('pagename',$this->data->data['data']->toArray()))
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

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $event = $this->data;
        $response = Http::delete(env('GRAPH_API_URL').$event->data['data']['postfbid'].'?access_token='.$this->access_token);
        return $response;
    }
}