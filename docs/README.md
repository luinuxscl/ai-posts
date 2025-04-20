# Documentación de AI Posts

Bienvenido a la documentación de `luinuxscl/ai-posts`, un package Laravel para la creación secuencial de posts siguiendo un flujo de trabajo definido por estados.

## Contenidos

- [Configuración](configuration.md) - Opciones de configuración y personalización
- [API REST](api.md) - Documentación detallada de todos los endpoints API
- [Integración con n8n](n8n-integration.md) - Ejemplos de flujos de trabajo en n8n
- [Eventos](events.md) - Sistema de eventos y extensibilidad
- [Exportación](export.md) - Opciones y formatos de exportación

## Conceptos Básicos

### Máquina de Estados

El núcleo de este package es una máquina de estados que gestiona el ciclo de vida de un post. Cada post pasa secuencialmente por los siguientes estados:

1. `draft` - Estado inicial al crear un post
2. `title_created` - Cuando se ha definido el título
3. `content_created` - Cuando se ha agregado el contenido principal
4. `summary_created` - Cuando se ha creado el resumen/extracto
5. `image_prompt_created` - Cuando se ha definido el prompt para generar imágenes
6. `ready_to_publish` - Estado final, el post está listo para ser exportado

### Transiciones de Estado

Las transiciones entre estados siguen un flujo predefinido y solo pueden ocurrir en secuencia. Cada transición requiere que se cumplan ciertas condiciones:

- Para avanzar a `title_created`, el post debe tener un título.
- Para avanzar a `content_created`, el post debe tener contenido.
- Para avanzar a `summary_created`, el post debe tener un resumen.
- Para avanzar a `image_prompt_created`, el post debe tener un prompt de imagen.
- Para avanzar a `ready_to_publish`, todas las condiciones anteriores deben cumplirse.

## Casos de Uso

Este package es especialmente útil para:

- Automatización de creación de contenido
- Integración con herramientas de IA generativa
- Preparación de contenido para publicación en cualquier plataforma
- Implementación de flujos de trabajo editoriales
- Integración con sistemas de automatización como n8n

## Principios de diseño

Este package ha sido diseñado siguiendo estos principios:

- **Simplicidad**: Implementa solo la funcionalidad necesaria para el propósito específico.
- **Separación de responsabilidades**: Se enfoca en la preparación del contenido, delegando la publicación a sistemas externos.
- **Extensibilidad**: Sistema de eventos que permite extender la funcionalidad sin modificar el código base.
- **API clara y coherente**: Métodos y endpoints con nombres descriptivos y comportamiento predecible.
