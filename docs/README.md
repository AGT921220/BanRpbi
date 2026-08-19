# Documentación BAN RPBI

Índice de la documentación interna del proyecto.

## Documentos

| Documento | Descripción |
| --------- | ----------- |
| [Módulos y alcance del sistema RPBI](functional-scope.md) | Objetivo, roles, flujos, módulos, dependencias, reglas transversales, pendientes, fuera de alcance, convenciones técnicas y estado de implementación |
| [Configuración de Google Maps](google-maps.md) | API key, variables Vite, restricciones, centro del mapa, yarn y costos |
| [Modificadores de consultas Eloquent](query-modifiers.md) | `QueryFilter`, `QueryOptions` y `BuilderFilter`: filtros y opciones tipados para consultas Eloquent |
| [DataTables (`createDataTable`)](frontend/datatables.md) | Helper frontend para tablas server-side: opciones, filtros dinámicos, `data-url` y formato de respuesta |
| [Arquitectura para clonar el sistema](prompt-arquitectura-sistema-similar.txt) | Docker, Redis, Horizon, Features, Headers/DataTables, auth y prompt listo para un sistema similar |

## Uso recomendado

1. Leer primero el [alcance funcional](functional-scope.md) antes de implementar o ampliar un módulo.
2. Distinguir alcance confirmado, propuesto y decisiones pendientes.
3. Contrastar siempre con el estado de implementación y la evidencia en código.
4. Actualizar la tabla de estado cuando un módulo cambie de fase.
