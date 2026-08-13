import DataTable from 'datatables.net-bs5';
import 'datatables.net-bs5/css/dataTables.bootstrap5.css';

import { get } from '../admin.js';

const DEFAULT_LANGUAGE = {
    processing: 'Cargando...',
    search: 'Buscar:',
    lengthMenu: 'Mostrar _MENU_ registros',
    info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
    infoEmpty: 'Mostrando 0 a 0 de 0 registros',
    infoFiltered: '(filtrado de _MAX_ registros)',
    emptyTable: 'No hay registros disponibles.',
    zeroRecords: 'No se encontraron registros.',
    paginate: {
        first: 'Primera',
        last: 'Última',
        next: 'Siguiente',
        previous: 'Anterior',
    },
};

/**
 * Inicializa un DataTable server-side reutilizable.
 *
 * Usa `get()` de `admin.js` para la petición AJAX (loader, headers, CSRF y parámetros).
 *
 * @param {string|HTMLElement} target
 *   ID de la tabla (`'clients-table'`), selector CSS (`'#clients-table'`)
 *   o el propio elemento HTML.
 * @param {Array<object>} columns
 *   Definición de columnas de DataTables (`data`, `name`, `render`, etc.).
 * @param {object} [options={}]
 * @param {string} [options.url]
 *   Endpoint AJAX. Si se omite, se usa el atributo `data-url` de la tabla.
 * @param {object} [options.requestHeaders={}]
 *   Encabezados adicionales para la petición GET.
 * @param {object|function(): object} [options.requestParams={}]
 *   Parámetros extra enviados en cada petición. Puede ser un objeto estático
 *   o una función que se evalúa en cada request (útil para filtros dinámicos).
 * @param {boolean} [options.loader=true]
 *   Si debe mostrarse el loader global durante la carga.
 * @param {string} [options.loaderText='Cargando']
 *   Texto base del loader. Se concatena con `entityName`.
 * @param {string} [options.entityName='registros']
 *   Nombre de la entidad en textos de idioma y del loader
 *   (p. ej. `'clientes'` → "Mostrando 1 a 15 de 100 clientes").
 * @param {object} [options.language={}]
 *   Sobrescrituras parciales del idioma de DataTables.
 * @param {function} [options.ajax]
 *   Función AJAX personalizada de DataTables `(data, callback)`.
 *   Si se omite, se usa `get()` contra `url` / `data-url`.
 * @param {...*} options.dataTableOptions
 *   Cualquier otra opción se pasa directamente a DataTables
 *   (`pageLength`, `order`, `dom`, etc.).
 *
 * @returns {DataTable|null}
 */
export function createDataTable(target, columns, options = {}) {
    const table = resolveTable(target);

    if (!table) {
        console.warn('DataTable: no se encontró la tabla.', target);
        return null;
    }

    const {
        url = table.dataset.url,
        requestHeaders = {},
        requestParams = {},
        loader = true,
        loaderText = 'Cargando',
        entityName = 'registros',
        language = {},
        ajax,
        ...dataTableOptions
    } = options;

    const ajaxFn = typeof ajax === 'function'
        ? ajax
        : url
            ? (request, callback) => {
                fetchTableData(url, request, {
                    requestHeaders,
                    requestParams,
                    loader,
                    loaderText,
                    entityName,
                }).then(callback);
            }
            : null;

    if (!ajaxFn) {
        console.warn('DataTable: la tabla no tiene una URL ni una función ajax configurada.', table);
        return null;
    }

    return new DataTable(table, {
        processing: true,
        serverSide: true,
        pageLength: 15,
        order: [[0, 'asc']],
        columns,
        language: buildLanguage(entityName, language),
        ajax: ajaxFn,
        ...dataTableOptions,
    });
}

/**
 * Resuelve el elemento `<table>` a partir de un ID, selector o HTMLElement.
 *
 * @param {string|HTMLElement} target
 * @returns {HTMLElement|null}
 */
function resolveTable(target) {
    if (target instanceof HTMLElement) {
        return target;
    }

    if (typeof target !== 'string') {
        return null;
    }

    if (
        target.startsWith('#')
        || target.startsWith('.')
        || target.includes('[')
    ) {
        return document.querySelector(target);
    }

    return document.getElementById(target);
}

/**
 * Obtiene los parámetros adicionales (objeto estático o función).
 *
 * @param {object|function(): object} requestParams
 * @returns {object}
 */
function resolveRequestParams(requestParams) {
    return typeof requestParams === 'function'
        ? requestParams()
        : requestParams;
}

/**
 * Respuesta vacía que DataTables espera ante un error de carga.
 *
 * @param {number} draw
 * @returns {object}
 */
function emptyAjaxResponse(draw) {
    return {
        draw,
        recordsTotal: 0,
        recordsFiltered: 0,
        data: [],
        error: 'No fue posible cargar la información.',
    };
}

/**
 * Construye la configuración de idioma en español, personalizada con `entityName`.
 *
 * @param {string} entityName
 * @param {object} [language={}]
 * @returns {object}
 */
function buildLanguage(entityName, language = {}) {
    return {
        ...DEFAULT_LANGUAGE,
        info: `Mostrando _START_ a _END_ de _TOTAL_ ${entityName}`,
        infoEmpty: `Mostrando 0 a 0 de 0 ${entityName}`,
        infoFiltered: `(filtrado de _MAX_ ${entityName})`,
        ...language,
        paginate: {
            ...DEFAULT_LANGUAGE.paginate,
            ...language.paginate,
        },
    };
}

/**
 * Realiza la petición server-side y devuelve el payload de DataTables.
 *
 * @param {string} url
 * @param {object} request Parámetros generados por DataTables (draw, start, length, search, order...).
 * @param {object} options
 * @returns {Promise<object>}
 */
async function fetchTableData(url, request, {
    requestHeaders = {},
    requestParams = {},
    loader = true,
    loaderText = 'Cargando',
    entityName = 'registros',
} = {}) {
    try {
        return await get(
            url,
            `${loaderText} ${entityName}`,
            {
                ...request,
                ...resolveRequestParams(requestParams),
            },
            {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...requestHeaders,
            },
            loader,
        );
    } catch (error) {
        console.error('DataTable: error al cargar información.', error);
        return emptyAjaxResponse(request.draw);
    }
}
