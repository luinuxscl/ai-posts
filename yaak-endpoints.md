# Guía de Endpoints para Yaak

Este documento proporciona una lista detallada de todos los endpoints disponibles en el package `luinuxscl/ai-posts`, específicamente formatados para ser utilizados con Yaak (una herramienta similar a Postman). La URL base para pruebas locales es `http://localhost:8000/`.

## Autenticación

Todos los endpoints requieren autenticación mediante tokens de Sanctum/Jetstream. Para configurar la autenticación en Yaak:

1. Crea una variable de entorno para tu token en la sección "Environments"
2. Para cada petición, incluye el encabezado:
   ```
   Authorization: Bearer {{token}}
   ```

## Flujo Secuencial de Creación de Posts

A continuación se presentan los endpoints organizados según el flujo secuencial de creación de posts, desde su creación inicial hasta que está listo para publicar.

### 1. Crear un Nuevo Post

**Endpoint:** `POST http://localhost:8000/api/ai-posts`

**Descripción:** Crea un nuevo post en estado borrador (draft).

**Cuerpo de la solicitud:**
```json
{
    "title": "Borrador inicial",
    "metadata": {
        "tags": ["ejemplo", "inicial"]
    }
}
```

**Respuesta esperada:**
```json
{
    "data": {
        "id": 1,
        "title": "Borrador inicial",
        "content": null,
        "summary": null,
        "image_prompt": null,
        "featured_image": null,
        "status": "draft",
        "metadata": {
            "tags": ["ejemplo", "inicial"]
        },
        "created_at": "2025-04-19T23:35:00-04:00",
        "updated_at": "2025-04-19T23:35:00-04:00",
        "exported_at": null
    }
}
```

### 2. Establecer Título y Avanzar de Estado

**Endpoint para establecer título:** `POST http://localhost:8000/api/ai-posts/{id}/set-title`

**Descripción:** Establece o actualiza el título del post.

**Cuerpo de la solicitud:**
```json
{
    "title": "Título definitivo del post"
}
```

**Endpoint para avanzar estado:** `POST http://localhost:8000/api/ai-posts/{id}/advance`

**Descripción:** Avanza el post del estado 'draft' a 'title_created'.

**Respuesta esperada después de avanzar:**
```json
{
    "data": {
        "id": 1,
        "title": "Título definitivo del post",
        "status": "title_created",
        "previous_status": "draft",
        "available_transitions": [
            {
                "action": "setContent",
                "name": "Establecer contenido"
            },
            {
                "action": "advance",
                "target_state": "content_created",
                "name": "Avanzar a Contenido creado"
            }
        ]
    }
}
```

### 3. Establecer Contenido y Avanzar

**Endpoint para establecer contenido:** `POST http://localhost:8000/api/ai-posts/{id}/set-content`

**Descripción:** Establece o actualiza el contenido principal del post.

**Cuerpo de la solicitud:**
```json
{
    "content": "Este es el contenido completo del post. Aquí va todo el texto principal con sus párrafos, subtítulos y elementos necesarios para un post completo."
}
```

**Endpoint para avanzar estado:** `POST http://localhost:8000/api/ai-posts/{id}/advance`

**Descripción:** Avanza el post del estado 'title_created' a 'content_created'.

### 4. Establecer Resumen y Avanzar

**Endpoint para establecer resumen:** `POST http://localhost:8000/api/ai-posts/{id}/set-summary`

**Descripción:** Establece o actualiza el resumen o extracto del post.

**Cuerpo de la solicitud:**
```json
{
    "summary": "Este es un breve resumen del post que sirve como introducción o extracto para mostrar en listados o previsualizaciones."
}
```

**Endpoint para avanzar estado:** `POST http://localhost:8000/api/ai-posts/{id}/advance`

**Descripción:** Avanza el post del estado 'content_created' a 'summary_created'.

### 5. Establecer Prompt para Imagen y Avanzar

**Endpoint para establecer prompt de imagen:** `POST http://localhost:8000/api/ai-posts/{id}/set-image-prompt`

**Descripción:** Establece o actualiza el prompt para generar la imagen destacada con IA.

**Cuerpo de la solicitud:**
```json
{
    "image_prompt": "Una imagen minimalista que representa [tema del post], con tonos azules y blancos, estilo moderno, alta resolución, sin texto"
}
```

**Endpoint para avanzar estado:** `POST http://localhost:8000/api/ai-posts/{id}/advance`

**Descripción:** Avanza el post del estado 'summary_created' a 'image_prompt_created' y luego automáticamente a 'ready_to_publish'.

### 6. Marcar Post como Exportado

**Endpoint:** `POST http://localhost:8000/api/ai-posts/{id}/mark-as-exported`

**Descripción:** Marca el post como exportado, registrando la fecha y hora de exportación.

**Respuesta esperada:**
```json
{
    "data": {
        "id": 1,
        "status": "ready_to_publish",
        "exported_at": "2025-04-19T23:45:00-04:00"
    }
}
```

## Endpoints de Exportación

### Exportar Posts Listos como JSON

**Endpoint:** `GET http://localhost:8000/api/ai-posts/export/json`

**Descripción:** Obtiene todos los posts en estado 'ready_to_publish' en formato JSON.

**Parámetros opcionales de consulta:**
- `only_unexported=true`: Solo devuelve posts no exportados previamente
- `include_metadata=true`: Incluye metadatos en la exportación
- `pretty_print=true`: Formato JSON legible con indentación

### Marcar Lote como Exportado

**Endpoint:** `POST http://localhost:8000/api/ai-posts/export/mark-batch`

**Descripción:** Marca múltiples posts como exportados en una sola operación.

**Cuerpo de la solicitud:**
```json
{
    "post_ids": [1, 2, 3, 5]
}
```

## Consulta de Estado y Transiciones

### Ver Transiciones Disponibles

**Endpoint:** `GET http://localhost:8000/api/ai-posts/{id}/transitions`

**Descripción:** Muestra todas las transiciones y acciones disponibles para el post en su estado actual.

### Obtener Detalles de un Post

**Endpoint:** `GET http://localhost:8000/api/ai-posts/{id}`

**Descripción:** Obtiene información detallada sobre un post específico, incluyendo su estado actual.

## Endpoints de Filtrado

### Filtrar Posts por Criterios

**Endpoint:** `GET http://localhost:8000/api/ai-posts/filter`

**Descripción:** Obtiene posts que coincidan con los criterios de filtrado especificados.

**Parámetros de consulta disponibles:**
- `status=ready_to_publish`: Filtra por estado
- `exported=false`: Solo posts no exportados (`true` para exportados)
- `from_date=2025-04-01`: Posts creados desde esta fecha
- `to_date=2025-04-19`: Posts creados hasta esta fecha
- `tag=ejemplo`: Posts que contengan esta etiqueta en metadata

---

## Notas para el Uso con Yaak

1. **Organización sugerida**: Crea una colección "AI Posts" con carpetas para cada fase (Creación, Edición, Exportación)
2. **Variables de entorno**:
   - `base_url` = http://localhost:8000
   - `token` = tu-token-de-api
3. **Guardado de IDs**: Configura Yaak para extraer y guardar el ID del post de las respuestas
4. **Prueba del flujo completo**: Crea una cadena de peticiones que sigan el flujo secuencial completo
