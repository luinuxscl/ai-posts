# ROADMAP: luinuxscl/ai-posts

Este documento detalla las tareas planificadas para el desarrollo del package Laravel `luinuxscl/ai-posts`, que facilitará la creación secuencial de posts para WordPress mediante una máquina de estados.

## Estado Actual

🚀 **Fase actual:** Máquina de Estados Completada

Completados:
- ✅ Estructura básica del package
- ✅ Modelo y migraciones
- ✅ Máquina de estados
- ✅ Servicios principales
- ✅ API REST
- ✅ Mecanismo de exportación

Pendientes:
- 🔄 Testing
- 🔄 Documentación detallada

## Tareas Pendientes

### Fase 1: Estructura Básica del Package

- [x] Crear estructura de directorios inicial
- [x] Configurar composer.json con dependencias y autoload
- [x] Implementar AiPostsServiceProvider
- [x] Crear archivo de configuración base (config/ai-posts.php)
- [x] Configurar Service Provider para publicación de assets
- [x] Crear archivo README.md con instrucciones básicas

### Fase 2: Base de Datos y Modelos

- [x] Diseñar esquema de la tabla ai_posts
- [x] Crear migraciones para la tabla
- [x] Implementar modelo AiPost
- [x] Definir relaciones con otros modelos (si es necesario)
- [x] Implementar traits necesarios (HasFactory, etc.)
- [x] Establecer estados posibles del post (máquina de estados)

### Fase 3: Máquina de Estados

- [x] Implementar clase StateMachine
- [x] Definir estados y transiciones válidas
- [x] Crear validadores para cada transición
- [x] Implementar eventos para cada cambio de estado
- [x] Añadir listeners para esos eventos
- [x] Documentar flujo de estados

### Fase 4: Servicios y Lógica de Negocio

- [x] Implementar AiPostService
- [x] Desarrollar métodos para cada operación (setTitle, setContent, etc.)
- [x] Crear WordPressPublisher para la integración con WordPress
- [x] Implementar lógica para generación automática de resúmenes
- [x] Añadir soporte para la generación de prompts de imágenes
- [x] Implementar sistema de metadatos

### Fase 5: API y Rutas

- [x] Configurar rutas API en el Service Provider
- [x] Implementar controladores para operaciones CRUD
- [x] Desarrollar controlador para transiciones de estado
- [x] Crear requests para validación de datos
- [x] Desarrollar recursos API para respuestas JSON
- [x] Integrar con Sanctum/Jetstream para autenticación

### Fase 6: Mecanismo de Exportación

- [x] Implementar mecanismo para marcar posts como exportados
- [x] Ajustar flujo de estados para terminar en 'ready_to_publish'
- [x] Crear métodos para exportar contenido a formato JSON
- [x] Implementar exportación por lotes (batch)
- [x] Añadir filtros para obtener posts listos para exportar

### Fase 7: Pruebas

- [ ] Configurar PHPUnit para pruebas
- [ ] Implementar pruebas unitarias para cada clase principal
- [ ] Crear pruebas de integración
- [ ] Pruebas específicas para transiciones de estado
- [ ] Pruebas de la API
- [ ] Pruebas de exportación de datos

### Fase 8: Documentación y Finalización

- [ ] Completar README.md con documentación completa
- [ ] Crear documentación para la API (posiblemente con Swagger/OpenAPI)
- [ ] Añadir ejemplos de uso
- [ ] Documentar ejemplos de integración con n8n y otras herramientas
- [ ] Preparar changelog para versionado
- [ ] Publicar en Packagist

## Hitos (Milestones)

1. ✅ **MVP Básico:** Package instalable con migraciones y modelo básico
2. ✅ **Flujo Completo:** Implementación completa de la máquina de estados
3. ✅ **API Funcional:** Endpoints API completamente funcionales
4. ✅ **Mecanismo de Exportación:** Funcionalidad para exportar posts completos
5. 🔄 **v1.0 Release:** Package completo con documentación y pruebas

## Convenciones y Principios

- Seguir principios SOLID
- Mantener una API pública mínima y expresiva
- Documentar todo el código público
- Mantener alta cobertura de pruebas
- Seguir estándares PSR-4 y PSR-12
- Priorizar la simplicidad sobre la complejidad

---

*Este ROADMAP es un documento vivo y se actualizará a medida que avance el desarrollo.*
