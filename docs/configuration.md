# Configuración

El package `luinuxscl/ai-posts` está diseñado para funcionar con una configuración mínima, siguiendo el principio de "convención sobre configuración". Sin embargo, ofrece varias opciones de personalización para adaptarse a diferentes necesidades.

## Archivo de configuración

Al publicar los assets del package, se creará el archivo `config/ai-posts.php` que contiene todas las opciones configurables:

```bash
php artisan vendor:publish --provider="Luinuxscl\AiPosts\AiPostsServiceProvider"
```

## Opciones disponibles

### Estados y transiciones

El núcleo del package es su máquina de estados. Puedes personalizar los nombres, descripciones y secuencia de los estados:

```php
'states' => [
    'draft' => [
        'name' => 'Borrador',
        'description' => 'Estado inicial de un post',
        'next' => 'title_created',
    ],
    'title_created' => [
        'name' => 'Título creado',
        'description' => 'El post tiene un título definido',
        'next' => 'content_created',
    ],
    // ... otros estados
],
```

Para cada estado, se define:
- `name`: Nombre legible para humanos
- `description`: Descripción detallada del estado
- `next`: El siguiente estado en la secuencia (o `null` para el estado final)

> **IMPORTANTE**: Modificar la secuencia de estados en una aplicación existente puede causar inconsistencias en los datos. Se recomienda establecer la configuración antes de comenzar a utilizar el package.

### Configuración de la API

Puedes personalizar cómo se expone la API REST:

```php
'api' => [
    'prefix' => 'api',
    'middleware' => ['api', 'auth:sanctum'],
    'abilities' => ['ai-posts:manage'],
],
```

- `prefix`: Prefijo para todas las rutas API (por defecto: `'api'`)
- `middleware`: Middleware aplicado a todas las rutas API
- `abilities`: Capacidades (abilities) requeridas para los tokens de Sanctum

### Nombres de tablas

Si necesitas personalizar el nombre de la tabla en la base de datos:

```php
'table_names' => [
    'posts' => 'ai_posts',
],
```

Por defecto, el package utiliza la tabla `ai_posts`, pero puedes cambiarla si tienes restricciones de nomenclatura o conflictos con otras tablas.

### Generación automática

Configuración para las funcionalidades de generación automática:

```php
'auto_generation' => [
    'summary' => [
        'enabled' => true,
        'max_length' => 150,
    ],
],
```

Esto controla el comportamiento del método `generateSummary()`, que automáticamente crea un resumen basado en el contenido del post.

## Uso avanzado

### Publicando solo el archivo de configuración

Si solo necesitas el archivo de configuración sin las migraciones:

```bash
php artisan vendor:publish --provider="Luinuxscl\AiPosts\AiPostsServiceProvider" --tag=ai-posts-config
```

### Publicando solo las migraciones

Si solo necesitas las migraciones:

```bash
php artisan vendor:publish --provider="Luinuxscl\AiPosts\AiPostsServiceProvider" --tag=ai-posts-migrations
```

### Configuración en tiempo de ejecución

También puedes modificar la configuración en tiempo de ejecución:

```php
// Cambiar la longitud máxima del resumen generado automáticamente
config(['ai-posts.auto_generation.summary.max_length' => 200]);
```

### Variables de entorno

Puedes utilizar variables de entorno en tu archivo `.env` para configurar aspectos del package:

```dotenv
# Longitud máxima para los resúmenes generados automáticamente
AI_POSTS_SUMMARY_MAX_LENGTH=200
```

Y luego en tu archivo `config/ai-posts.php`:

```php
'auto_generation' => [
    'summary' => [
        'enabled' => true,
        'max_length' => env('AI_POSTS_SUMMARY_MAX_LENGTH', 150),
    ],
],
```

## Extensión con proveedores de servicios personalizados

Para una personalización más avanzada, puedes crear tu propio proveedor de servicios que extienda el comportamiento del package:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Luinuxscl\AiPosts\AiPosts;
use App\Services\CustomSummaryGenerator;

class AiPostsExtensionServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Reemplazar el servicio de generación de resúmenes con uno personalizado
        $this->app->singleton('ai-posts.summary-generator', function ($app) {
            return new CustomSummaryGenerator();
        });
    }
    
    public function boot()
    {
        // Extender la clase AiPost con funcionalidades adicionales
        AiPost::macro('customFunction', function () {
            // Implementación personalizada
        });
    }
}
```

Luego, registra este proveedor en tu archivo `config/app.php`.

## Recomendaciones de configuración

### Entorno de desarrollo

Para desarrollo, es recomendable mantener la configuración por defecto, pero puedes ajustar la generación automática para facilitar las pruebas:

```php
'auto_generation' => [
    'summary' => [
        'enabled' => true,
        'max_length' => 50, // Más corto para pruebas
    ],
],
```

### Entorno de producción

Para producción, considera ajustar la configuración para optimizar el rendimiento:

```php
'auto_generation' => [
    'summary' => [
        'enabled' => true,
        'max_length' => 150,
    ],
],
```

También podrías ajustar el middleware para añadir limitación de tasa (rate limiting) en producción:

```php
'api' => [
    'prefix' => 'api',
    'middleware' => ['api', 'auth:sanctum', 'throttle:60,1'],
    'abilities' => ['ai-posts:manage'],
],
```
