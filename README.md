# AI Posts

Un package Laravel para la creación secuencial de posts mediante un flujo de trabajo definido por estados. Este package se enfoca en la generación y preparación de posts, dejando la publicación a herramientas externas.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/luinuxscl/ai-posts.svg?style=flat-square)](https://packagist.org/packages/luinuxscl/ai-posts)
[![Total Downloads](https://img.shields.io/packagist/dt/luinuxscl/ai-posts.svg?style=flat-square)](https://packagist.org/packages/luinuxscl/ai-posts)
[![License](https://img.shields.io/packagist/l/luinuxscl/ai-posts?style=flat-square)](https://packagist.org/packages/luinuxscl/ai-posts)

## Descripción

AI Posts es un package que estructura y facilita el proceso de creación de posts siguiendo un flujo secuencial basado en estados. Gestiona todo el proceso desde la creación del título hasta que el post está listo para publicar, con especial atención a la integración con herramientas de automatización como n8n.

## Características

- **Máquina de estados robusta** para gestionar el ciclo de vida de los posts
- **API RESTful completa** para integración con servicios externos
- **Sistema de eventos** para extender la funcionalidad fácilmente
- **Flujo secuencial**: título → contenido → resumen → prompt para imagen → listo para publicar
- **Exportación flexible** en formato JSON
- **Filtros avanzados** para buscar y gestionar posts

## Compatibilidad

Este package es compatible con:

- PHP 8.1 o superior
- Laravel 10.x, 11.x, 12.x

## Instalación

Puedes instalar el package vía composer:

```bash
composer require luinuxscl/ai-posts
```

Después de instalar el package, publica los archivos de configuración y migraciones:

```bash
php artisan vendor:publish --provider="Luinuxscl\AiPosts\AiPostsServiceProvider"
php artisan migrate
```

### Configuración

El package funciona con una configuración por defecto, pero puedes personalizarla editando el archivo `config/ai-posts.php` publicado:

```php
// config/ai-posts.php

return [
    // Estados de los posts y sus transiciones
    'states' => [
        'draft' => [/* ... */],
        // Personaliza los estados y sus transiciones
    ],
    
    // Configuración de la API
    'api' => [/* ... */],
    
    // Configuración de generación automática
    'auto_generation' => [/* ... */],
];
```

## Uso Básico

### Flujo de trabajo manual

```php
// Crear un nuevo post en estado borrador
$post = AiPosts::create(['title' => 'Borrador inicial']);

// Establecer título y avanzar al siguiente estado
$post->setTitle('Título definitivo')->advance();

// Establecer contenido y avanzar
$post->setContent('Contenido del post...')->advance();

// Generar resumen y avanzar
$post->generateSummary()->advance();

// Crear prompt para imagen y avanzar
$post->setImagePrompt('Un prompt descriptivo para IA')->advance();

// En este punto el post estará en estado 'ready_to_publish'
// Ahora podríamos exportarlo:
$jsonData = $post->toJson();

// O marcarlo como exportado
$post->markAsExported();
```

### Usando el servicio de exportación

```php
// Obtener el servicio de exportación
$exportService = app('ai-posts.export');

// Obtener todos los posts listos para exportar
$readyPosts = $exportService->getPostsReadyToExport();

// Exportar todos los posts listos a JSON
$json = $exportService->exportReadyPosts(
    onlyUnexported: true,  // solo los no exportados previamente
    includeMetadata: true,  // incluir metadatos
    prettyPrint: true       // formato JSON legible
);

// Exportar a un archivo
$result = $exportService->exportBatchToFile(
    storage_path('exports/posts-'.date('Y-m-d').'.json')
);
```

### Máquina de estados

El flujo del post sigue esta secuencia de estados:

1. `draft` - Estado inicial cuando se crea el post
2. `title_created` - Cuando se establece el título
3. `content_created` - Cuando se establece el contenido principal
4. `summary_created` - Cuando se crea el resumen o extracto
5. `image_prompt_created` - Cuando se define el prompt para generar imágenes
6. `ready_to_publish` - Estado final, el post está listo para ser exportado

## API REST

El package incluye una API RESTful completa. Los endpoints principales son:

```
# Operaciones CRUD básicas
GET    /api/ai-posts             # Listar posts
POST   /api/ai-posts             # Crear post
GET    /api/ai-posts/{id}        # Obtener post
PUT    /api/ai-posts/{id}        # Actualizar post
DELETE /api/ai-posts/{id}        # Eliminar post

# Transiciones de estado
POST   /api/ai-posts/{id}/advance           # Avanzar al siguiente estado
POST   /api/ai-posts/{id}/set-title         # Establecer título
POST   /api/ai-posts/{id}/set-content       # Establecer contenido
POST   /api/ai-posts/{id}/set-summary       # Establecer resumen
POST   /api/ai-posts/{id}/set-image-prompt  # Establecer prompt para imagen
POST   /api/ai-posts/{id}/mark-as-exported  # Marcar como exportado

# Exportación
GET    /api/ai-posts/export/ready      # Obtener posts listos para exportar
GET    /api/ai-posts/export/json       # Exportar posts como JSON
POST   /api/ai-posts/export/mark-batch # Marcar lote como exportado
GET    /api/ai-posts/filter            # Filtrar posts por criterios
```

Consulta la [documentación de la API](docs/api.md) para más detalles.

## Integración con n8n

Este package está especialmente diseñado para integrarse con n8n. Consulta los [ejemplos de integración con n8n](docs/n8n-integration.md) para implementaciones prácticas.

## Eventos

El package dispara eventos en cada transición de estado, permitiendo extender su funcionalidad:

```php
// En EventServiceProvider.php de tu aplicación
protected $listen = [
    // Evento genérico para cualquier cambio de estado
    \Luinuxscl\AiPosts\Events\AiPostStateChanged::class => [
        \App\Listeners\YourCustomListener::class,
    ],
    // Evento cuando un post está listo para publicar
    \Luinuxscl\AiPosts\Events\AiPostReadyToPublish::class => [
        \App\Listeners\NotifyEditor::class,
    ],
];
```

## Documentación

Consulta la [documentación completa](docs/README.md) para detalles sobre:

- [Configuración avanzada](docs/configuration.md)
- [API REST](docs/api.md)
- [Integración con n8n](docs/n8n-integration.md)
- [Eventos y extensibilidad](docs/events.md)
- [Exportación](docs/export.md)

## Changelog

Consulta el [CHANGELOG](CHANGELOG.md) para obtener información sobre las últimas actualizaciones.

## Licencia

MIT. Consulta el archivo [LICENSE](LICENSE.md) para más información.

## Autor

- [Luis Sepulveda](https://github.com/luinuxscl)
