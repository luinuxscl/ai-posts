<?php

namespace Luinuxscl\AiPosts\Listeners;

use Illuminate\Support\Facades\Log;
use Luinuxscl\AiPosts\Events\AiPostStateChanged;

class LogAiPostStateChanged
{
    /**
     * Handle the event.
     *
     * @param  \Luinuxscl\AiPosts\Events\AiPostStateChanged  $event
     * @return void
     */
    public function handle(AiPostStateChanged $event)
    {
        Log::info('Post cambió de estado', [
            'post_id' => $event->post->id,
            'title' => $event->post->title,
            'old_state' => $event->oldState,
            'new_state' => $event->newState,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
