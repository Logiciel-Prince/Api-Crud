<?php

namespace App\Jobs;

use App\Helpers\GetAccessToken;
use App\Http\Controllers\Controller;
use App\Models\FacebookPage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class UpdateCommentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;

    public $tries;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->tries = config('queue.tries');
        $this->data = $data;
       
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

            $pageName = FacebookPage::where('id',$event->data['data']['page_id'])->first();
            
            $token = (new GetAccessToken)->getPageAccessToken($pageName['page_name']);

            $response = Http::post(env('GRAPH_API_URL').$event->data['data']['commentfbid'].'?access_token='.$token.'&message='.$event->data['message']['message']);

            if(array_key_exists('error',$response->json()) && $response->json()['error']['code'] == 190){
                $this->errorCatch($event,$pageName);
            }
            
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function errorCatch($event,$pageName){
        
        (new Controller)->refreshPageToken($event->data['data']['page_id']); 
        
        $token = (new GetAccessToken)->getPageAccessToken($pageName['page_name']);
        
        $response = Http::post(env('GRAPH_API_URL').$event->data['data']['commentfbid'].'?access_token='.$token.'&message='.$event->data['message']['message']);
                            
    }
}
