<?php

namespace App\Listeners;

use App\Events\FacebookDeletePostEvent;
use App\Jobs\DeletePostJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class FacebookDeletePostListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\FacebookDeletePostEvent  $event
     * @return void
     */
    public function handle(FacebookDeletePostEvent $event)
    {
        if(!empty(auth()->user()->token))
        {
            DeletePostJob::dispatch($event);
        }
    }
}
