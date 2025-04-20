# Sistema de Eventos

El package `luinuxscl/ai-posts` implementa un sistema de eventos robusto que permite a los desarrolladores extender la funcionalidad del package sin modificar su código base. Esto sigue el principio de "Open/Closed" de SOLID: abierto para extensión, cerrado para modificación.

## Eventos Disponibles

El package dispara los siguientes eventos:

### 1. Evento genérico de cambio de estado

```php
Luinuxscl\AiPosts\Events\AiPostStateChanged
```

Este evento se dispara en cualquier transición de estado. Incluye:
- La instancia del post (`$event->post`)
- El estado anterior (`$event->oldState`)
- El nuevo estado (`$event->newState`)

### 2. Eventos específicos de estado

Estos eventos se disparan cuando un post alcanza un estado específico:

```php
Luinuxscl\AiPosts\Events\AiPostTitleCreated        // Cuando se completa el título
Luinuxscl\AiPosts\Events\AiPostContentCreated      // Cuando se completa el contenido
Luinuxscl\AiPosts\Events\AiPostSummaryCreated      // Cuando se completa el resumen
Luinuxscl\AiPosts\Events\AiPostImagePromptCreated  // Cuando se crea el prompt de imagen
Luinuxscl\AiPosts\Events\AiPostReadyToPublish      // Cuando está listo para publicar
```

Cada uno de estos eventos incluye la instancia del post (`$event->post`).

### 3. Evento de marcado como exportado

```php
Luinuxscl\AiPosts\Events\AiPostMarkedAsExported
```

Este evento se dispara cuando un post es marcado como exportado. Incluye la instancia del post (`$event->post`).

## Escuchando eventos

Puedes escuchar estos eventos en el archivo `EventServiceProvider.php` de tu aplicación Laravel:

```php
<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Luinuxscl\AiPosts\Events\AiPostStateChanged;
use Luinuxscl\AiPosts\Events\AiPostReadyToPublish;
use App\Listeners\LogPostStateChange;
use App\Listeners\NotifyEditorAboutReadyPost;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        AiPostStateChanged::class => [
            LogPostStateChange::class,
        ],
        AiPostReadyToPublish::class => [
            NotifyEditorAboutReadyPost::class,
        ],
    ];
}
```

## Ejemplos de listeners

### Ejemplo 1: Registrar cambios de estado

```php
<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Luinuxscl\AiPosts\Events\AiPostStateChanged;

class LogPostStateChange
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
```

### Ejemplo 2: Notificar a un editor cuando un post está listo

```php
<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Notification;
use App\Notifications\PostReadyForPublishing;
use App\Models\User;
use Luinuxscl\AiPosts\Events\AiPostReadyToPublish;

class NotifyEditorAboutReadyPost
{
    /**
     * Handle the event.
     *
     * @param  \Luinuxscl\AiPosts\Events\AiPostReadyToPublish  $event
     * @return void
     */
    public function handle(AiPostReadyToPublish $event)
    {
        // Encuentra editores para notificar
        $editors = User::role('editor')->get();
        
        // Notifica a todos los editores
        Notification::send($editors, new PostReadyForPublishing($event->post));
    }
}
```

### Ejemplo 3: Publicar automáticamente en WordPress

```php
<?php

namespace App\Listeners;

use App\Services\WordPressClient;
use Luinuxscl\AiPosts\Events\AiPostReadyToPublish;

class PublishPostToWordPress
{
    protected $wordPressClient;
    
    /**
     * Create the event listener.
     *
     * @param  \App\Services\WordPressClient  $wordPressClient
     * @return void
     */
    public function __construct(WordPressClient $wordPressClient)
    {
        $this->wordPressClient = $wordPressClient;
    }
    
    /**
     * Handle the event.
     *
     * @param  \Luinuxscl\AiPosts\Events\AiPostReadyToPublish  $event
     * @return void
     */
    public function handle(AiPostReadyToPublish $event)
    {
        // Publicar en WordPress cuando un post está listo
        $post = $event->post;
        
        $wordpressPostId = $this->wordPressClient->createPost([
            'title' => $post->title,
            'content' => $post->content,
            'excerpt' => $post->summary,
            'status' => 'publish',
            'featured_media' => $this->uploadFeaturedImage($post),
            'meta' => $post->metadata ?? [],
        ]);
        
        // Opcionalmente, guarda la referencia al ID de WordPress
        $post->metadata = array_merge($post->metadata ?? [], [
            'wordpress_post_id' => $wordpressPostId
        ]);
        $post->save();
        
        // Marca el post como exportado
        $post->markAsExported();
    }
    
    /**
     * Subir imagen destacada a WordPress si está disponible.
     *
     * @param  \Luinuxscl\AiPosts\Models\AiPost  $post
     * @return int|null
     */
    protected function uploadFeaturedImage($post)
    {
        if (!empty($post->featured_image)) {
            return $this->wordPressClient->uploadMedia($post->featured_image);
        }
        
        return null;
    }
}
```

## Eventos de cola (Queue)

Los eventos pueden ser procesados en segundo plano implementando la interfaz `ShouldQueue`:

```php
<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Luinuxscl\AiPosts\Events\AiPostReadyToPublish;

class GenerateAndUploadFeaturedImage implements ShouldQueue
{
    use InteractsWithQueue;
    
    /**
     * Handle the event.
     *
     * @param  \Luinuxscl\AiPosts\Events\AiPostReadyToPublish  $event
     * @return void
     */
    public function handle(AiPostReadyToPublish $event)
    {
        // Código para generar imagen con IA y cargarla
        // Este proceso se ejecutará en segundo plano
    }
}
```

## Listeners incluidos en el package

El package incluye algunos listeners de ejemplo que puedes utilizar como punto de partida:

1. `Luinuxscl\AiPosts\Listeners\LogAiPostStateChanged`: Registra cada cambio de estado en el log.
2. `Luinuxscl\AiPosts\Listeners\NotifyWhenPostReadyToPublish`: Ejemplo básico de notificación.
3. `Luinuxscl\AiPosts\Listeners\TrackExportedPosts`: Mantiene un registro de posts exportados en caché.

Estos listeners están registrados en `Luinuxscl\AiPosts\Providers\EventServiceProvider`, que es registrado automáticamente por el package.

## Mejores prácticas

1. **Tareas ligeras**: Los listeners sincronizados deben realizar tareas rápidas para no bloquear la respuesta HTTP.
2. **Usa colas**: Para tareas pesadas (como generar imágenes con IA), implementa `ShouldQueue`.
3. **Manejador único**: Cada listener debe tener una única responsabilidad.
4. **Manejo de errores**: Implementa manejo robusto de errores en tus listeners para evitar problemas en cascada.
5. **Desacoplamiento**: Mantén tus listeners desacoplados del package principal para facilitar el mantenimiento.
