# Documentación de la API REST

El package `luinuxscl/ai-posts` proporciona una API RESTful completa que permite interactuar con todas las funcionalidades. Esta API está diseñada para integrarse fácilmente con servicios externos como n8n.

## Autenticación

La API utiliza la autenticación mediante tokens de Sanctum/Jetstream. Para acceder a los endpoints, debes incluir un token API válido en el encabezado de autorización:

```
Authorization: Bearer your-api-token
```

Para crear un token con los permisos adecuados, puedes usar:

```php
$token = $user->createToken('nombre-de-tu-aplicacion', ['ai-posts:manage'])->plainTextToken;
```

## Endpoints Disponibles

### Posts (CRUD básico)

#### Listar todos los posts

```
GET /api/ai-posts
```

**Parámetros:**
- `page` (opcional): Número de página para paginación
- `per_page` (opcional): Elementos por página (por defecto: 15)

**Respuesta:**
```json
{
    "data": [
        {
            "id": 1,
            "title": "Ejemplo de título",
            "content": "Contenido del post...",
            "summary": "Resumen del post...",
            "image_prompt": "Prompt para generar imagen...",
            "featured_image": null,
            "status": "ready_to_publish",
            "metadata": { "tags": ["ejemplo", "tutorial"] },
            "created_at": "2025-04-19T10:30:00-04:00",
            "updated_at": "2025-04-19T11:45:00-04:00",
            "exported_at": null,
            "links": {
                "self": "http://tu-app.com/api/ai-posts/1",
                "advance": "http://tu-app.com/api/ai-posts/1/advance",
                "transitions": "http://tu-app.com/api/ai-posts/1/transitions"
            }
        },
        // Más posts...
    ],
    "links": {
        "first": "http://tu-app.com/api/ai-posts?page=1",
        "last": "http://tu-app.com/api/ai-posts?page=5",
        "prev": null,
        "next": "http://tu-app.com/api/ai-posts?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 5,
        "path": "http://tu-app.com/api/ai-posts",
        "per_page": 15,
        "to": 15,
        "total": 68
    }
}
```

#### Crear un nuevo post

```
POST /api/ai-posts
```

**Cuerpo de la solicitud:**
```json
{
    "title": "Nuevo post",
    "content": "Contenido opcional inicial",
    "summary": "Resumen opcional inicial",
    "image_prompt": "Prompt opcional inicial",
    "metadata": {
        "tags": ["ejemplo", "nuevo"],
        "custom_field": "valor personalizado"
    }
}
```

Todos los campos son opcionales. El post se creará en estado `draft` por defecto.

**Respuesta:**
```json
{
    "data": {
        "id": 5,
        "title": "Nuevo post",
        "status": "draft",
        // Otros campos...
    }
}
```

#### Obtener un post específico

```
GET /api/ai-posts/{id}
```

**Respuesta:**
```json
{
    "data": {
        "id": 5,
        "title": "Nuevo post",
        // Resto de campos...
    }
}
```

#### Actualizar un post

```
PUT /api/ai-posts/{id}
```

**Cuerpo de la solicitud:**
```json
{
    "title": "Título actualizado",
    "content": "Contenido actualizado",
    // Otros campos a actualizar...
}
```

**Respuesta:**
```json
{
    "data": {
        "id": 5,
        "title": "Título actualizado",
        "content": "Contenido actualizado",
        // Resto de campos...
    }
}
```

#### Eliminar un post

```
DELETE /api/ai-posts/{id}
```

**Respuesta:**
```json
{
    "message": "Post eliminado correctamente"
}
```

### Transiciones de Estado

#### Obtener transiciones disponibles

```
GET /api/ai-posts/{id}/transitions
```

**Respuesta:**
```json
{
    "data": [
        {
            "action": "advance",
            "target_state": "title_created",
            "name": "Avanzar a Título creado"
        },
        {
            "action": "setTitle",
            "name": "Establecer título"
        }
    ],
    "post": {
        "data": {
            "id": 5,
            "status": "draft",
            // Resto de campos...
        }
    }
}
```

#### Avanzar al siguiente estado

```
POST /api/ai-posts/{id}/advance
```

**Respuesta:**
```json
{
    "data": {
        "id": 5,
        "status": "title_created", // Estado actualizado
        // Resto de campos...
    }
}
```

#### Establecer título

```
POST /api/ai-posts/{id}/set-title
```

**Cuerpo de la solicitud:**
```json
{
    "title": "Nuevo título"
}
```

**Respuesta:**
```json
{
    "data": {
        "id": 5,
        "title": "Nuevo título",
        // Resto de campos...
    }
}
```

#### Establecer contenido

```
POST /api/ai-posts/{id}/set-content
```

**Cuerpo de la solicitud:**
```json
{
    "content": "Nuevo contenido detallado..."
}
```

#### Establecer resumen

```
POST /api/ai-posts/{id}/set-summary
```

**Cuerpo de la solicitud:**
```json
{
    "summary": "Resumen conciso del post..."
}
```

#### Establecer prompt para imagen

```
POST /api/ai-posts/{id}/set-image-prompt
```

**Cuerpo de la solicitud:**
```json
{
    "image_prompt": "Un prompt detallado para generar una imagen..."
}
```

#### Marcar como exportado

```
POST /api/ai-posts/{id}/mark-as-exported
```

### Exportación

#### Obtener posts listos para exportar

```
GET /api/ai-posts/export/ready
```

**Parámetros:**
- `only_unexported` (opcional): Si es `true`, solo incluirá posts no exportados (por defecto: `true`)

**Respuesta:**
```json
{
    "data": [
        {
            "id": 2,
            "title": "Post listo para exportar",
            "status": "ready_to_publish",
            // Resto de campos...
        },
        // Más posts...
    ]
}
```

#### Exportar posts como JSON

```
GET /api/ai-posts/export/json
```

**Parámetros:**
- `only_unexported` (opcional): Si es `true`, solo incluirá posts no exportados (por defecto: `true`)
- `include_metadata` (opcional): Si es `true`, incluirá metadatos (por defecto: `true`)
- `pretty_print` (opcional): Si es `true`, formatea el JSON legiblemente (por defecto: `true`)
- `mark_as_exported` (opcional): Si es `true`, marca los posts como exportados (por defecto: `false`)

**Respuesta:**
```json
{
    "posts": [
        {
            "id": 2,
            "title": "Post listo para exportar",
            "content": "Contenido completo...",
            "summary": "Resumen conciso...",
            "image_prompt": "Prompt para generar imagen...",
            "featured_image": null,
            "created_at": "2025-04-19T10:30:00-04:00",
            "updated_at": "2025-04-19T11:45:00-04:00",
            "exported_at": null,
            "metadata": {
                "tags": ["ejemplo", "tutorial"],
                "categories": [1, 3]
            }
        },
        // Más posts...
    ]
}
```

#### Marcar lote de posts como exportados

```
POST /api/ai-posts/export/mark-batch
```

**Cuerpo de la solicitud:**
```json
{
    "post_ids": [2, 3, 7]
}
```

**Respuesta:**
```json
{
    "success": true,
    "message": "Se marcaron 3 posts como exportados",
    "count": 3,
    "posts": {
        "data": [
            // Posts marcados como exportados...
        ]
    }
}
```

#### Filtrar posts

```
GET /api/ai-posts/filter
```

**Parámetros:**
- `status` (opcional): Filtrar por estado (ej: `ready_to_publish`)
- `exported` (opcional): Si es `true`, solo posts exportados
- `unexported` (opcional): Si es `true`, solo posts no exportados
- `created_after` (opcional): Fecha ISO (ej: `2025-04-15T00:00:00-04:00`)
- `created_before` (opcional): Fecha ISO
- `search` (opcional): Buscar en título y contenido
- `sort_by` (opcional): Campo para ordenar (`id`, `title`, `created_at`, etc.)
- `sort_dir` (opcional): Dirección (`asc` o `desc`, por defecto: `desc`)
- `per_page` (opcional): Elementos por página

**Respuesta:**
```json
{
    "data": [
        // Posts filtrados...
    ],
    "links": { /* Enlaces de paginación */ },
    "meta": { /* Metadatos de paginación */ }
}
```

## Códigos de Estado HTTP

- `200 OK`: La solicitud se completó correctamente
- `201 Created`: Recurso creado correctamente
- `400 Bad Request`: Solicitud incorrecta o datos inválidos
- `401 Unauthorized`: No autenticado o token inválido
- `403 Forbidden`: Sin permisos para el recurso
- `404 Not Found`: Recurso no encontrado
- `422 Unprocessable Entity`: Errores de validación o transición de estado inválida
- `500 Internal Server Error`: Error del servidor
