<?php

namespace Luinuxscl\AiPosts\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Luinuxscl\AiPosts\Events\AiPostStateChanged;
use Luinuxscl\AiPosts\Events\AiPostReadyToPublish;
use Luinuxscl\AiPosts\Events\AiPostMarkedAsExported;
use Luinuxscl\AiPosts\Listeners\LogAiPostStateChanged;
use Luinuxscl\AiPosts\Listeners\NotifyWhenPostReadyToPublish;
use Luinuxscl\AiPosts\Listeners\TrackExportedPosts;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        AiPostStateChanged::class => [
            LogAiPostStateChanged::class,
        ],
        AiPostReadyToPublish::class => [
            NotifyWhenPostReadyToPublish::class,
        ],
        AiPostMarkedAsExported::class => [
            TrackExportedPosts::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
