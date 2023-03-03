<?php

namespace App\Listeners;

use App\Events\FacebookUpdateCommentEvent;
use App\Jobs\UpdateCommentJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class FacebookUpdateCommentListener
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
     * @param  \App\Events\FacebookUpdateCommentEvent  $event
     * @return void
     */
    public function handle(FacebookUpdateCommentEvent $event)
    {
        if(!empty(auth()->user()->token))
        {
            UpdateCommentJob::dispatch($event);
        }
    }
}
