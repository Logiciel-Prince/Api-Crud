<?php

namespace App\Jobs;

use App\Helpers\GetAccessToken;
use App\Http\Controllers\Controller;
use App\Models\FacebookPage;
use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class DeleteCommentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $access_token;

    public $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {

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

            dd($event);

            $token = (new GetAccessToken)->getPageAccessToken($event->data);

            $response = Http::delete(env('GRAPH_API_URL').$event->data['data']['commentfbid'].'?access_token='.$token);
            
            $postid = Post::where('id',$event->data['data']['post_id'])->first();

            if(array_key_exists('error',$response->json()) && $response->json()['error']['code'] == 190){

                (new Controller)->refreshPageToken($postid['page_id']); 

                $token = (new GetAccessToken)->getPageAccessToken($event->data['message']['pagename']);

                $response = Http::delete(env('GRAPH_API_URL').$event->data['data']['commentfbid'].'?access_token='.$token);

            }

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
