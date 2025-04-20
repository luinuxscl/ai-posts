# Mecanismo de Exportación

El package `luinuxscl/ai-posts` proporciona un sistema completo para exportar posts una vez que están listos para ser publicados. Esta funcionalidad permite integrar fácilmente con sistemas externos como CMS (WordPress, Drupal, etc.), generadores de sitios estáticos, u otras plataformas.

## Conceptos básicos

### Estados de exportación

Un post pasa por los siguientes estados hasta estar listo para exportar:

1. `draft` → `title_created` → `content_created` → `summary_created` → `image_prompt_created` → `ready_to_publish`

Cuando un post alcanza el estado `ready_to_publish`, está listo para ser exportado.

### Marcado de exportación

El sistema mantiene un seguimiento de qué posts han sido exportados mediante:

- Un campo `exported_at` que almacena la fecha/hora de exportación
- Métodos y endpoints para marcar posts como exportados

## Métodos de exportación

### Exportación desde el modelo

Puedes exportar un post individual utilizando los siguientes métodos del modelo `AiPost`:

```php
// Obtener un array estructurado con los datos del post
$dataArray = $post->toExportArray(includeMetadata: true);

// Obtener una cadena JSON con los datos del post
$jsonString = $post->toJson(includeMetadata: true, options: JSON_PRETTY_PRINT);

// Marcar el post como exportado
$post->markAsExported();
```

### Exportación mediante el servicio

Para operaciones más avanzadas, puedes utilizar el servicio de exportación:

```php
// Obtener una instancia del servicio de exportación
$exportService = app('ai-posts.export');

// Obtener todos los posts listos para exportar
$posts = $exportService->getPostsReadyToExport(onlyUnexported: true);

// Exportar posts a formato JSON
$json = $exportService->exportToJson(
    $posts,
    includeMetadata: true,
    prettyPrint: true
);

// Marcar múltiples posts como exportados
$count = $exportService->markBatchAsExported($posts);

// Exportar directamente a un archivo
$result = $exportService->exportBatchToFile(
    filePath: storage_path('exports/posts-'.date('Y-m-d').'.json'),
    onlyUnexported: true,
    includeMetadata: true,
    prettyPrint: true
);
```

## Formato de exportación

### Formato JSON estándar

El formato de exportación JSON estándar para un post individual es:

```json
{
    "id": 1,
    "title": "Título del post",
    "content": "Contenido completo del post...",
    "summary": "Resumen conciso del post...",
    "image_prompt": "Prompt para generar imagen relacionada...",
    "featured_image": "https://ejemplo.com/imagen.jpg",
    "created_at": "2025-04-19T10:00:00-04:00",
    "updated_at": "2025-04-19T15:30:00-04:00",
    "exported_at": null,
    "metadata": {
        "tags": ["ejemplo", "documentación"],
        "categories": [1, 3],
        "custom_field": "valor personalizado"
    }
}
```

### Exportación por lotes

La exportación de múltiples posts tiene el siguiente formato:

```json
{
    "posts": [
        {
            "id": 1,
            "title": "Primer post",
            // Resto de los campos...
        },
        {
            "id": 2,
            "title": "Segundo post",
            // Resto de los campos...
        }
    ]
}
```

## API REST para exportación

El package incluye endpoints API específicos para la exportación:

### Obtener posts listos para exportar

```
GET /api/ai-posts/export/ready?only_unexported=true
```

Devuelve una colección de posts que están en estado `ready_to_publish`.

### Exportar posts como JSON

```
GET /api/ai-posts/export/json?only_unexported=true&mark_as_exported=false&pretty_print=true
```

Devuelve directamente el JSON con los posts listos para exportar.

### Marcar lote como exportado

```
POST /api/ai-posts/export/mark-batch
```

Con el cuerpo:
```json
{
    "post_ids": [1, 2, 3]
}
```

Marca múltiples posts como exportados en una sola operación.

## Filtrado de posts

El endpoint de filtrado permite búsquedas avanzadas:

```
GET /api/ai-posts/filter?status=ready_to_publish&unexported=true&created_after=2025-04-01
```

Parámetros disponibles:
- `status`: Filtrar por estado
- `exported`: Si es `true`, solo posts exportados
- `unexported`: Si es `true`, solo posts no exportados
- `created_after`: Fecha ISO
- `created_before`: Fecha ISO
- `search`: Buscar en título y contenido
- `sort_by`: Campo para ordenar
- `sort_dir`: Dirección (`asc` o `desc`)

## Ejemplos prácticos

### 1. Exportación programada con Laravel Task Scheduling

Puedes configurar una tarea programada para exportar posts regularmente:

```php
// En App\Console\Kernel.php

protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        $exportService = app('ai-posts.export');
        $result = $exportService->exportBatchToFile(
            storage_path('exports/posts-'.date('Y-m-d').'.json')
        );
        
        if ($result['success']) {
            Log::info('Exportación exitosa', $result);
        } else {
            Log::error('Error en exportación', $result);
        }
    })->dailyAt('02:00');
}
```

### 2. Exportación a WordPress mediante API

```php
$exportService = app('ai-posts.export');
$posts = $exportService->getPostsReadyToExport();

foreach ($posts as $post) {
    $client = new \GuzzleHttp\Client();
    
    $response = $client->post('https://tu-wordpress.com/wp-json/wp/v2/posts', [
        'headers' => [
            'Authorization' => 'Basic ' . base64_encode('usuario:contraseña'),
            'Content-Type' => 'application/json',
        ],
        'json' => [
            'title' => $post->title,
            'content' => $post->content,
            'excerpt' => $post->summary,
            'status' => 'publish',
        ],
    ]);
    
    if ($response->getStatusCode() === 201) {
        $post->markAsExported();
    }
}
```

### 3. Exportación a archivos Markdown para sitio estático

```php
$exportService = app('ai-posts.export');
$posts = $exportService->getPostsReadyToExport();

foreach ($posts as $post) {
    $frontMatter = [
        'title' => $post->title,
        'date' => now()->toIso8601String(),
        'summary' => $post->summary,
        'tags' => $post->metadata['tags'] ?? [],
    ];
    
    $content = "---\n" . json_encode($frontMatter, JSON_PRETTY_PRINT) . "\n---\n\n" . $post->content;
    
    $filename = date('Y-m-d') . '-' . Str::slug($post->title) . '.md';
    File::put(storage_path('posts/' . $filename), $content);
    
    $post->markAsExported();
}
```

## Optimización y rendimiento

Para un mejor rendimiento al exportar grandes volúmenes de posts:

1. **Procesamiento por lotes**: Utiliza el servicio `markBatchAsExported()` en lugar de marcar posts individualmente.

2. **Exportación incremental**: Utiliza el parámetro `onlyUnexported: true` para procesar solo los posts nuevos.

3. **Procesamiento en segundo plano**: Para exportaciones grandes, considera utilizar jobs de Laravel:

```php
// Crear un job
class ExportReadyPosts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function handle()
    {
        $exportService = app('ai-posts.export');
        $exportService->exportBatchToFile(
            storage_path('exports/posts-'.date('Y-m-d').'.json')
        );
    }
}

// Disparar el job
ExportReadyPosts::dispatch();
```

## Extendiendo la funcionalidad de exportación

Puedes extender el sistema de exportación para formatos personalizados o destinos adicionales:

```php
class CustomExporter
{
    protected $exportService;
    
    public function __construct()
    {
        $this->exportService = app('ai-posts.export');
    }
    
    public function exportToCustomFormat()
    {
        $posts = $this->exportService->getPostsReadyToExport();
        
        // Transformar a formato personalizado
        $customData = $this->transformToCustomFormat($posts);
        
        // Guardar o enviar a sistema externo
        $this->sendToExternalSystem($customData);
        
        // Marcar como exportados
        $this->exportService->markBatchAsExported($posts);
    }
    
    // Métodos de implementación...
}
```
