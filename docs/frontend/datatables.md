# DataTables (`createDataTable`)

Helper reutilizable para inicializar tablas server-side con [DataTables](https://datatables.net/) en el panel administrativo.

**Archivo:** `resources/js/shared/datatable.js`

## Propósito

`createDataTable()` encapsula la configuración común de DataTables:

- `serverSide` y `processing`
- paginación y ordenamiento
- parámetros de búsqueda de DataTables
- columnas dinámicas
- textos en español
- loader global vía `get()` de `admin.js`
- manejo de errores con respuesta vacía segura

La petición HTTP la realiza `get()` (`resources/js/admin.js`), que ya centraliza loader, headers, parámetros GET, CSRF y manejo de errores a nivel de Axios.

## Ejemplo básico

```html
<table id="clients-table" data-url="/clients/data" class="table">
    <!-- thead... -->
</table>
```

```js
import { createDataTable } from '../../shared/datatable.js';

createDataTable('clients-table', [
    { data: 'full_name', name: 'full_name' },
    { data: 'email', name: 'email' },
    { data: 'phone', name: 'phone' },
    {
        data: 'actions',
        name: 'actions',
        orderable: false,
        searchable: false,
    },
], {
    entityName: 'clientes',
});
```

## Ejemplo con filtros dinámicos

`requestParams` puede ser un objeto o una función. Si es función, se evalúa en cada petición AJAX:

```js
createDataTable('clients-table', columns, {
    entityName: 'clientes',
    requestParams: () => ({
        status: document.querySelector('#status-filter')?.value,
    }),
});
```

Objeto estático:

```js
createDataTable('clients-table', columns, {
    entityName: 'clientes',
    requestParams: {
        status: 'active',
    },
});
```

## Ejemplo con encabezados adicionales

```js
createDataTable('clients-table', columns, {
    entityName: 'clientes',
    requestHeaders: {
        'X-Custom-Header': 'valor',
    },
});
```

## URL: `options.url` o `data-url`

La URL del endpoint se resuelve así:

1. `options.url`, si se proporciona.
2. Si no, el atributo `data-url` del elemento `<table>`.

```html
<table id="clients-table" data-url="/api/clients"></table>
```

```js
// Usa data-url automáticamente
createDataTable('clients-table', columns, { entityName: 'clientes' });

// O sobrescribe la URL
createDataTable('clients-table', columns, {
    url: '/api/clients/export-view',
    entityName: 'clientes',
});
```

## `entityName`

Personaliza los textos de idioma y el mensaje del loader:

| Valor       | Ejemplo de texto                              |
| ----------- | --------------------------------------------- |
| `registros` | Mostrando 1 a 15 de 100 registros             |
| `clientes`  | Mostrando 1 a 15 de 100 clientes              |

El loader muestra: `"Cargando clientes"` (o el `loaderText` configurado + `entityName`).

## Estructura esperada de la respuesta backend

El endpoint debe devolver el formato estándar de DataTables server-side:

```json
{
    "draw": 1,
    "recordsTotal": 100,
    "recordsFiltered": 25,
    "data": []
}
```

| Campo             | Descripción                                              |
| ----------------- | -------------------------------------------------------- |
| `draw`            | Contador de petición; debe reflejar el `draw` recibido   |
| `recordsTotal`    | Total de registros sin filtrar                           |
| `recordsFiltered` | Total tras aplicar filtros/búsqueda                      |
| `data`            | Array de filas                                           |

## Manejo de errores

Si la petición falla, se registra el error en consola y se responde a DataTables con:

```json
{
    "draw": 1,
    "recordsTotal": 0,
    "recordsFiltered": 0,
    "data": [],
    "error": "No fue posible cargar la información."
}
```

Así la tabla no queda en estado de carga indefinido.

## Opciones adicionales de DataTables

Cualquier opción no reconocida por el helper (`pageLength`, `order`, `dom`, callbacks, etc.) se reparte con `...dataTableOptions` y se pasa **directamente** a DataTables:

```js
createDataTable('clients-table', columns, {
    entityName: 'clientes',
    pageLength: 25,
    order: [[1, 'desc']],
    loader: false,
});
```

### Opciones propias del helper

| Opción            | Tipo                     | Default       | Descripción                                      |
| ----------------- | ------------------------ | ------------- | ------------------------------------------------ |
| `url`             | `string`                 | `data-url`    | Endpoint AJAX                                    |
| `requestHeaders`  | `object`                 | `{}`          | Headers adicionales                              |
| `requestParams`   | `object \| function`     | `{}`          | Parámetros extra (estáticos o dinámicos)         |
| `loader`          | `boolean`                | `true`        | Mostrar loader global                            |
| `loaderText`      | `string`                 | `'Cargando'`  | Texto base del loader                            |
| `entityName`      | `string`                 | `'registros'` | Nombre de entidad en textos                      |
| `language`        | `object`                 | `{}`          | Sobrescrituras del idioma de DataTables          |

### `target`

Acepta:

- ID sin `#`: `'clients-table'`
- Selector CSS: `'#clients-table'`, `.my-table`
- Elemento: `document.getElementById('clients-table')`
