<?php

namespace App\Listeners;

use App\Events\FacebookUpdatePostEvent;
use App\Jobs\UpdatePostJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class FacebookUpdatePostListener
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
     * @param  \App\Events\FacebookUpdatePostEvent  $event
     * @return void
     */
    public function handle(FacebookUpdatePostEvent $event)
    {
        if(!empty(auth()->user()->token))
        {
            UpdatePostJob::dispatch($event);
        }
    }
}
