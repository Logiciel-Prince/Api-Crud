<?php

namespace App\Jobs;

use App\Helpers\GetAccessToken;
use App\Http\Controllers\Controller;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\{
    Comment,
    FacebookPage,
    Post
};
use Illuminate\Support\Facades\{
    Http,
    Log,
};

class FacebookComment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    Public $data;

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

            $postid = Post::where('id',$event->data['data']['post_id'])->first();
            
            $token = (new GetAccessToken)->getPageAccessToken($event->data['data']['pagename']);

            Log::info($token);

            $response =  Http::post(env('GRAPH_API_URL') . $postid['postfbid'] . '/comments?message=' . $event->data['data']['message'] . '&access_token=' . $token);

            Log::info($response);
            
            if(array_key_exists('error',$response->json()) && $response->json()['error']['code'] == 190){

                (new Controller)->refreshPageToken($postid['page_id']); 

                $token = (new GetAccessToken)->getPageAccessToken($event->data['message']['pagename']);

                $response =  Http::post(env('GRAPH_API_URL') . $postid['postfbid'] . '/comments?message=' .$event->data['data']['message'] . '&access_token=' . $token);
            }
            Comment::where('id',$event->data['message']['id'])->update([
                'commentfbid' => $response->json('id'),
            ]);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

}
