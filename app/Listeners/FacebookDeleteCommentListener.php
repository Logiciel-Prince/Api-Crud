<?php

namespace App\Listeners;

use App\Events\FacebookDeleteCommentEvent;
use App\Jobs\DeleteCommentJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class FacebookDeleteCommentListener
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
     * @param  \App\Events\FacebookDeleteCommentEvent  $event
     * @return void
     */
    public function handle(FacebookDeleteCommentEvent $event)
    {
        DeleteCommentJob::dispatch($event);
    }
}
