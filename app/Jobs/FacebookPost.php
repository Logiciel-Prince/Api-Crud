<?php

namespace App\Jobs;

use App\Helpers\GetAccessToken;
use App\Http\Controllers\Controller;
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
     * @return 
     */
    public function handle()
    {
        try {
            $event = $this->data;

            $token = (new GetAccessToken)->getPageAccessToken($event->data['data']['pagename']);

            if ($event->data['imageName']) {
                $imageName = $event->data['imageName'];

                $image = public_path('storage/images/' . $imageName);

                $response = Http::attach('attachment', file_get_contents($image), $imageName)
                    ->post(env('GRAPH_API_URL') . 'me/photos?access_token=' . $token . '&message=' . $event->data['data']['desc']);

                if (array_key_exists('error', $response->json()) && $response->json()['error']['code'] == 190) {
                    $response = $this->errorCatchImage($response, $event, $image, $imageName);
                }

                $this->storeFbPostId($event, $response->json('post_id'));

                return true;
            }

            $response =  Http::post(env('GRAPH_API_URL') . 'me/feed?access_token=' . $token . '&message=' . $event->data['data']['desc']);

            if (array_key_exists('error', $response->json()) && $response->json()['error']['code'] == 190) {
                $this->errorCatchPost($response, $event);
            }

            $this->storeFbPostId($event, $response->json('id'));

            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private function errorCatchImage($response, $event, $image, $imageName)
    {
        (new Controller)->refreshPageToken($event->data['data']['page_id']);

        $token = (new GetAccessToken)->getPageAccessToken($event->data['data']['pagename']);

        $response = Http::attach('attachment', file_get_contents($image), $imageName)
            ->post(env('GRAPH_API_URL') . 'me/photos?access_token=' . $token . '&message=' . $event->data['data']['desc']);

        return $response;
    }

    private function errorCatchPost($response, $event)
    {
        (new Controller)->refreshPageToken($event->data['data']['page_id']);

        $token = (new GetAccessToken)->getPageAccessToken($event->data['data']['pagename']);

        $response =  Http::post(env('GRAPH_API_URL') . 'me/feed?access_token=' . $token . '&message=' . $event->data['data']['desc']);

        return $response;
    }

    private function storeFbPostId($event, $id)
    {
        Post::where('id', $event->data['post']['id'])
            ->update(['postfbid' => $id]);
    }
}
