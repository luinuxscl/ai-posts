<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Post States Configuration
    |--------------------------------------------------------------------------
    |
    | Definición de los estados por los que pasa un post
    |
    */
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
        'content_created' => [
            'name' => 'Contenido creado',
            'description' => 'El post tiene contenido definido',
            'next' => 'summary_created',
        ],
        'summary_created' => [
            'name' => 'Resumen creado',
            'description' => 'El post tiene un resumen o extracto definido',
            'next' => 'image_prompt_created',
        ],
        'image_prompt_created' => [
            'name' => 'Prompt de imagen creado',
            'description' => 'Se ha definido un prompt para generar una imagen',
            'next' => 'ready_to_publish',
        ],
        'ready_to_publish' => [
            'name' => 'Listo para publicar',
            'description' => 'El post está completamente preparado y listo para ser exportado',
            'next' => null,
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para la API REST
    |
    */
    'api' => [
        'prefix' => 'api',
        'middleware' => ['api', 'auth:sanctum'],
        'abilities' => ['ai-posts:manage'],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    |
    | Personalización de los nombres de las tablas utilizadas por el package
    |
    */
    'table_names' => [
        'posts' => 'ai_posts',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Auto-generation Settings
    |--------------------------------------------------------------------------
    |
    | Configuración para la generación automática de contenido
    |
    */
    'auto_generation' => [
        'summary' => [
            'enabled' => true,
            'max_length' => 150,
        ],
    ],
];
