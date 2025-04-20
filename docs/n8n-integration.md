# Integración con n8n

El package `luinuxscl/ai-posts` ha sido diseñado para integrarse perfectamente con [n8n](https://n8n.io/), una herramienta de automatización de flujos de trabajo. Esta guía muestra ejemplos prácticos de cómo utilizar la API REST del package en flujos de n8n.

## Requisitos previos

- Una instancia de Laravel con el package `luinuxscl/ai-posts` instalado y configurado
- Una instancia de n8n funcionando
- Un token API de Sanctum/Jetstream con los permisos adecuados (`ai-posts:manage`)

## Configuración de n8n

### Creación de credenciales

1. En n8n, ve a **Credentials** y crea una nueva credencial de tipo **HTTP Basic Authentication**
2. Completa los siguientes campos:
   - **Name**: Laravel AI Posts API
   - **User**: `token` (debe ser literalmente la palabra "token")
   - **Password**: Tu token API de Laravel/Sanctum completo

### Configuración de la URL base

En tus nodos HTTP, usarás la URL base de tu aplicación Laravel, por ejemplo:
```
https://tu-aplicacion-laravel.com/api
```

## Ejemplos de flujos de trabajo

### 1. Flujo básico de creación secuencial de post

Este flujo crea un post y lo lleva paso a paso hasta el estado `ready_to_publish`.

![n8n basic workflow](../images/n8n-basic-workflow.png)

#### Nodo 1: Inicio (Manual Trigger)

Este nodo inicia el flujo manualmente.

#### Nodo 2: Crear nuevo post (HTTP Request)

**Configuración:**
- **Method**: POST
- **URL**: `{{$node["Manual Trigger"].json["baseUrl"]}}/ai-posts`
- **Authentication**: Laravel AI Posts API
- **JSON/RAW Parameters**:
```json
{
  "title": "Borrador inicial"
}
```

#### Nodo 3: Establecer título (HTTP Request)

**Configuración:**
- **Method**: POST
- **URL**: `{{$node["Manual Trigger"].json["baseUrl"]}}/ai-posts/{{$node["Crear nuevo post"].json["data"]["id"]}}/set-title`
- **Authentication**: Laravel AI Posts API
- **JSON/RAW Parameters**:
```json
{
  "title": "Título definitivo del post"
}
```

#### Nodo 4: Avanzar a estado "title_created" (HTTP Request)

**Configuración:**
- **Method**: POST
- **URL**: `{{$node["Manual Trigger"].json["baseUrl"]}}/ai-posts/{{$node["Crear nuevo post"].json["data"]["id"]}}/advance`
- **Authentication**: Laravel AI Posts API

#### Nodo 5: Establecer contenido (HTTP Request)

**Configuración:**
- **Method**: POST
- **URL**: `{{$node["Manual Trigger"].json["baseUrl"]}}/ai-posts/{{$node["Crear nuevo post"].json["data"]["id"]}}/set-content`
- **Authentication**: Laravel AI Posts API
- **JSON/RAW Parameters**:
```json
{
  "content": "Este es el contenido completo del post. Aquí se desarrolla todo el artículo con sus secciones correspondientes..."
}
```

#### Nodo 6: Avanzar a estado "content_created" (HTTP Request)

**Configuración:**
- **Method**: POST
- **URL**: `{{$node["Manual Trigger"].json["baseUrl"]}}/ai-posts/{{$node["Crear nuevo post"].json["data"]["id"]}}/advance`
- **Authentication**: Laravel AI Posts API

#### Nodo 7: Generar resumen con IA (OpenAI)

Este nodo utiliza la API de OpenAI para generar un resumen del contenido.

**Configuración:**
- **Authentication**: Tu credencial de OpenAI
- **Operation**: Complete
- **Prompt**:
```
Genera un resumen conciso (máximo 150 palabras) del siguiente artículo:

{{$node["Establecer contenido"].json["data"]["content"]}}

Resumen:
```

#### Nodo 8: Establecer resumen (HTTP Request)

**Configuración:**
- **Method**: POST
- **URL**: `{{$node["Manual Trigger"].json["baseUrl"]}}/ai-posts/{{$node["Crear nuevo post"].json["data"]["id"]}}/set-summary`
- **Authentication**: Laravel AI Posts API
- **JSON/RAW Parameters**:
```json
{
  "summary": "{{$node["Generar resumen con IA"].json["text"]}}"
}
```

#### Nodo 9: Avanzar a estado "summary_created" (HTTP Request)

**Configuración:**
- **Method**: POST
- **URL**: `{{$node["Manual Trigger"].json["baseUrl"]}}/ai-posts/{{$node["Crear nuevo post"].json["data"]["id"]}}/advance`
- **Authentication**: Laravel AI Posts API

#### Nodo 10: Generar prompt para imagen (OpenAI)

**Configuración:**
- **Authentication**: Tu credencial de OpenAI
- **Operation**: Complete
- **Prompt**:
```
Basándote en el siguiente título y resumen de un artículo, genera un prompt detallado para crear una imagen con DALL-E o Midjourney que represente visualmente el contenido:

Título: {{$node["Establecer título"].json["data"]["title"]}}
Resumen: {{$node["Establecer resumen"].json["data"]["summary"]}}

El prompt debe ser descriptivo, detallado y adecuado para generar una imagen relevante. No uses comillas ni prefijos como "prompt:".
```

#### Nodo 11: Establecer prompt para imagen (HTTP Request)

**Configuración:**
- **Method**: POST
- **URL**: `{{$node["Manual Trigger"].json["baseUrl"]}}/ai-posts/{{$node["Crear nuevo post"].json["data"]["id"]}}/set-image-prompt`
- **Authentication**: Laravel AI Posts API
- **JSON/RAW Parameters**:
```json
{
  "image_prompt": "{{$node["Generar prompt para imagen"].json["text"]}}"
}
```

#### Nodo 12: Avanzar a estado "image_prompt_created" (HTTP Request)

**Configuración:**
- **Method**: POST
- **URL**: `{{$node["Manual Trigger"].json["baseUrl"]}}/ai-posts/{{$node["Crear nuevo post"].json["data"]["id"]}}/advance`
- **Authentication**: Laravel AI Posts API

#### Nodo 13: Avanzar a estado "ready_to_publish" (HTTP Request)

**Configuración:**
- **Method**: POST
- **URL**: `{{$node["Manual Trigger"].json["baseUrl"]}}/ai-posts/{{$node["Crear nuevo post"].json["data"]["id"]}}/advance`
- **Authentication**: Laravel AI Posts API

#### Nodo 14: Notificar por Slack (opcional)

**Configuración:**
- **Authentication**: Tu credencial de Slack
- **Channel**: El canal donde enviar la notificación
- **Text**:
```
🎉 Nuevo post listo para publicar: "{{$node["Establecer título"].json["data"]["title"]}}"

Resumen: {{$node["Establecer resumen"].json["data"]["summary"]}}

ID: {{$node["Crear nuevo post"].json["data"]["id"]}}
```

### 2. Flujo de exportación automática

Este flujo busca posts listos para publicar y los exporta a JSON.

![n8n export workflow](../images/n8n-export-workflow.png)

#### Nodo 1: Programación (Schedule Trigger)

Este nodo ejecuta el flujo según un cronograma (por ejemplo, diariamente a las 8:00 AM).

#### Nodo 2: Obtener posts listos (HTTP Request)

**Configuración:**
- **Method**: GET
- **URL**: `{{$node["Schedule Trigger"].json["baseUrl"]}}/ai-posts/export/ready?only_unexported=true`
- **Authentication**: Laravel AI Posts API

#### Nodo 3: If (hay posts listos)

Este nodo bifurca el flujo según si hay posts listos para exportar.

**Configuración:**
- **Condition**: `{{$node["Obtener posts listos"].json["data"].length > 0}}`

#### Nodo 4: Exportar a JSON (HTTP Request)

**Configuración:**
- **Method**: GET
- **URL**: `{{$node["Schedule Trigger"].json["baseUrl"]}}/ai-posts/export/json?only_unexported=true&mark_as_exported=true&pretty_print=true`
- **Authentication**: Laravel AI Posts API

#### Nodo 5: Guardar JSON en sistema de archivos (Write Binary File)

**Configuración:**
- **File Name**: `posts-export-{{$today.format("YYYY-MM-DD")}}.json`
- **Data**: `{{$node["Exportar a JSON"].json}}`

#### Nodo 6: Notificar éxito (Slack)

**Configuración:**
- **Authentication**: Tu credencial de Slack
- **Channel**: El canal donde enviar la notificación
- **Text**:
```
✅ Exportación de posts completada

Número de posts exportados: {{$node["Obtener posts listos"].json["data"].length}}
Archivo: posts-export-{{$today.format("YYYY-MM-DD")}}.json
```

## Flujo de trabajo para integración con WordPress

También puedes combinar este package con la API de WordPress para publicar automáticamente los posts:

```json
{
  "nodes": [
    {
      "name": "Obtener posts listos",
      "type": "n8n-nodes-base.httpRequest",
      "parameters": {
        "url": "https://tu-app.com/api/ai-posts/export/ready",
        "authentication": "httpBasicAuth",
        "method": "GET",
        "options": {
          "redirect": {
            "redirect": {
              "followRedirects": true
            }
          }
        }
      }
    },
    {
      "name": "Ciclo por cada post",
      "type": "n8n-nodes-base.set",
      "parameters": {
        "mode": "each",
        "sourceValue": "={{ $node[\"Obtener posts listos\"].json[\"data\"] }}",
        "options": {}
      }
    },
    {
      "name": "Publicar en WordPress",
      "type": "n8n-nodes-base.wordpress",
      "parameters": {
        "resource": "post",
        "operation": "create",
        "title": "={{ $node[\"Ciclo por cada post\"].json.title }}",
        "content": "={{ $node[\"Ciclo por cada post\"].json.content }}",
        "status": "publish",
        "excerpt": "={{ $node[\"Ciclo por cada post\"].json.summary }}",
        "additional": {
          "metadata": "={{ $node[\"Ciclo por cada post\"].json.metadata }}"
        }
      }
    },
    {
      "name": "Marcar como exportado",
      "type": "n8n-nodes-base.httpRequest",
      "parameters": {
        "url": "={{ \"https://tu-app.com/api/ai-posts/\" + $node[\"Ciclo por cada post\"].json.id + \"/mark-as-exported\" }}",
        "authentication": "httpBasicAuth",
        "method": "POST",
        "options": {}
      }
    }
  ],
  "connections": {
    "Obtener posts listos": {
      "main": [
        [
          {
            "node": "Ciclo por cada post",
            "type": "main",
            "index": 0
          }
        ]
      ]
    },
    "Ciclo por cada post": {
      "main": [
        [
          {
            "node": "Publicar en WordPress",
            "type": "main",
            "index": 0
          }
        ]
      ]
    },
    "Publicar en WordPress": {
      "main": [
        [
          {
            "node": "Marcar como exportado",
            "type": "main",
            "index": 0
          }
        ]
      ]
    }
  }
}
```

## Consideraciones adicionales

### Manejo de errores

Para un flujo de trabajo robusto, considera añadir nodos de manejo de errores y bifurcaciones condicionales para manejar diferentes escenarios.

### Variables de entorno

Es recomendable utilizar variables de entorno en n8n para almacenar valores como la URL base de tu API, facilitando el despliegue en diferentes entornos.

### Paralelización

Para procesar múltiples posts de forma eficiente, puedes utilizar nodos como "Split In Batches" para dividir grandes conjuntos de datos y procesarlos en paralelo.

## Descarga de ejemplos

Puedes descargar los flujos de ejemplo completos desde el [repositorio GitHub](https://github.com/luinuxscl/ai-posts/tree/main/examples/n8n) del package.
