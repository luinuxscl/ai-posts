<?php

namespace Luinuxscl\AiPosts\Listeners;

use Illuminate\Support\Facades\Cache;
use Luinuxscl\AiPosts\Events\AiPostMarkedAsExported;

class TrackExportedPosts
{
    /**
     * Handle the event.
     *
     * @param  \Luinuxscl\AiPosts\Events\AiPostMarkedAsExported  $event
     * @return void
     */
    public function handle(AiPostMarkedAsExported $event)
    {
        // Ejemplo de cómo se podría llevar un registro de los posts exportados
        // utilizando el sistema de caché de Laravel
        
        $exportedToday = Cache::get('ai_posts_exported_today', 0);
        Cache::put('ai_posts_exported_today', $exportedToday + 1, now()->endOfDay());
        
        // También se podría llevar un historial más completo:
        $exportHistory = Cache::get('ai_posts_export_history', []);
        $exportHistory[] = [
            'post_id' => $event->post->id,
            'title' => $event->post->title,
            'exported_at' => now()->toDateTimeString(),
        ];
        
        // Guardar solo los últimos 50 registros para no sobrecargar la caché
        if (count($exportHistory) > 50) {
            $exportHistory = array_slice($exportHistory, -50);
        }
        
        Cache::put('ai_posts_export_history', $exportHistory, now()->addDays(30));
    }
}
