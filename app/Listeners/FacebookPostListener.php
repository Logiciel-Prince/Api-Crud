<?php

namespace App\Listeners;

use App\Events\FacebookPostEvent;
use App\Jobs\FacebookPost;

class FacebookPostListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
     //-----------------------------This Constructor get the facebook page access token---------------------------//
    public function __construct()
    {
        
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\FacebookPostEvent  $event
     * @return void
     */
    public function handle(FacebookPostEvent $event)
    {
        FacebookPost::dispatch($event);
    }
}
