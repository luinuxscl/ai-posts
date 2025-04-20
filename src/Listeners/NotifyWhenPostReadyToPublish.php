<?php

namespace Luinuxscl\AiPosts\Listeners;

use Illuminate\Support\Facades\Log;
use Luinuxscl\AiPosts\Events\AiPostReadyToPublish;

class NotifyWhenPostReadyToPublish
{
    /**
     * Handle the event.
     *
     * @param  \Luinuxscl\AiPosts\Events\AiPostReadyToPublish  $event
     * @return void
     */
    public function handle(AiPostReadyToPublish $event)
    {
        // Aquí podrías implementar una notificación por email, Slack, etc.
        // Este es solo un ejemplo que registra en el log
        
        Log::info('Post listo para publicar', [
            'post_id' => $event->post->id,
            'title' => $event->post->title,
            'timestamp' => now()->toDateTimeString(),
        ]);
        
        // Ejemplo de cómo un desarrollador podría hacer algo más complejo:
        // Notification::route('mail', 'editor@ejemplo.com')
        //     ->notify(new PostReadyForReviewNotification($event->post));
    }
}
