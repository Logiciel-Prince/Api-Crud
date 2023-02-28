<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\{
    FacebookPostEvent,
    FacebookUpdatePostEvent,
    FacebookDeletePostEvent,
    FacebookCommentEvent,
    FacebookUpdateCommentEvent,
    FacebookDeleteCommentEvent
};
use App\Listeners\{
    FacebookPostListener,
    FacebookUpdatePostListener,
    FacebookDeletePostListener,
    FacebookCommentListener,
    FacebookUpdateCommentListener,
    FacebookDeleteCommentListener
};

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        FacebookPostEvent::class => [
            FacebookPostListener::class
        ],
        FacebookUpdatePostEvent::class => [
            FacebookUpdatePostListener::class
        ],
        FacebookDeletePostEvent::class => [
            FacebookDeletePostListener::class
        ],
        FacebookCommentEvent::class => [
            FacebookCommentListener::class
        ],
        FacebookUpdateCommentEvent::class => [
            FacebookUpdateCommentListener::class
        ],
        FacebookDeleteCommentEvent::class => [
            FacebookDeleteCommentListener::class
        ]

    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
