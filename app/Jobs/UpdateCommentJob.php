<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class UpdateCommentJob implements ShouldQueue
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
       
        $request = Http::get(env('GRAPH_API_URL').'me/accounts?access_token='.auth()->user()->token);
        
        if(array_key_exists('error',$request->json()))
        {
            return response()->json([
                'message' => 'Invalid access_token or Your access_token may be expired',
            ],401);
        }
        if(array_key_exists('pagename',$this->data->data['message']))
        {
            $pageName = $this->data->data['message']['pagename'];
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
        try {
            $event = $this->data;
            Http::post(env('GRAPH_API_URL').$event->data['data']['commentfbid'].'?access_token='.$this->access_token.'&message='.$event->data['message']['message']);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
