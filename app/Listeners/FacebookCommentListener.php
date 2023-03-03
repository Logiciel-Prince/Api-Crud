<?php

namespace App\Listeners;

use App\Events\FacebookCommentEvent;
use App\Jobs\FacebookComment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class FacebookCommentListener
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
     * @param  \App\Events\FacebookCommentEvent  $event
     * @return void
     */
    public function handle(FacebookCommentEvent $event)
    {
        if(!empty(auth()->user()->token))
        {
            FacebookComment::dispatch($event);
        }
    }
}
