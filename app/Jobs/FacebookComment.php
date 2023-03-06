<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\{
    Comment,
    Post
};
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
            $message = $event->data['data']['message'];
            $postid = Post::where('id',$event->data['data']['post_id'])->first('postfbid');
            $response =  Http::post(env('GRAPH_API_URL') . $postid['postfbid'] . '/comments?message=' . $message . '&access_token=' . $event->data['pagetoken']);
            Comment::where('id',$event->data['message']['id'])->update([
                'commentfbid' => $response->json('id'),
            ]);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
