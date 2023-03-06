<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class DeletePostJob implements ShouldQueue
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
            Http::delete(env('GRAPH_API_URL').$event->data['data']['postfbid'].'?access_token='.$event->data['data']->pages['access_token']);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
