# Configuración de Google Maps

Guía para habilitar el mapa de zonas geográficas del Dashboard BAN RPBI.

## Variables de entorno

Agregar en `.env` (nunca en Git):

```env
GOOGLE_MAPS_API_KEY=
VITE_GOOGLE_MAPS_API_KEY="${GOOGLE_MAPS_API_KEY}"

MAP_DEFAULT_LAT=25.6866
MAP_DEFAULT_LNG=-100.3161
MAP_DEFAULT_ZOOM=10
VITE_MAP_DEFAULT_LAT="${MAP_DEFAULT_LAT}"
VITE_MAP_DEFAULT_LNG="${MAP_DEFAULT_LNG}"
VITE_MAP_DEFAULT_ZOOM="${MAP_DEFAULT_ZOOM}"
```

| Variable | Uso |
| -------- | --- |
| `GOOGLE_MAPS_API_KEY` | Clave del proyecto en Google Cloud |
| `VITE_GOOGLE_MAPS_API_KEY` | Exposición a Vite para el frontend |
| `MAP_DEFAULT_*` / `VITE_MAP_DEFAULT_*` | Centro y zoom iniciales del mapa |

La clave se lee en JavaScript con `import.meta.env.VITE_GOOGLE_MAPS_API_KEY`. Si falta, la interfaz muestra:

```text
No se configuró VITE_GOOGLE_MAPS_API_KEY.
```

No imprimir la clave en consola ni confirmarla en el repositorio. `.env` ya está en `.gitignore`.

## Configuración en Google Cloud

1. Crear o seleccionar un proyecto en Google Cloud.
2. Habilitar facturación para producción.
3. Habilitar únicamente **Maps JavaScript API**.
4. Crear una API Key.
5. Restringir la llave por referentes HTTP, por ejemplo:
   - `http://localhost/*`
   - `http://localhost:8080/*`
   - `https://dominio-produccion.com/*`
6. Restringir la llave para que solo pueda utilizar **Maps JavaScript API**.

En esta primera implementación **no** se usan Geocoding API, Places API ni Routes API.

## Assets frontend

El mapa de zonas usa:

- Google Maps JavaScript API (`@googlemaps/js-api-loader`)
- Terra Draw + `terra-draw-google-maps-adapter` para dibujar y editar polígonos

Comandos:

```bash
yarn install
yarn dev
yarn build
```

Cualquier cambio en variables `VITE_*` requiere reiniciar `yarn dev` o volver a ejecutar `yarn build`.

## Geometría

Las zonas se guardan como GeoJSON `Polygon` en la columna `geometry`.

Orden de coordenadas:

```text
[longitud, latitud]
```

El anillo exterior debe cerrarse (primer y último punto iguales) y tener al menos tres vértices reales.

## Notas de alcance

- Se permite guardar polígonos que se superpongan; la UI puede mostrar una advertencia.
- La regla definitiva de superposición está pendiente de definición.
- El uso de Maps JavaScript API puede generar costos según el consumo.
- En producción se deben revisar facturación, cuotas y restricciones de la API key.
- El centro por defecto apunta al área metropolitana de Monterrey solo como fallback técnico.

## Relacionado

- [Índice de documentación](README.md)
- [Módulos y alcance del sistema RPBI](functional-scope.md)
