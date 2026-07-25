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
 * @param {string|HTMLElement} target ID, selector o elemento de la tabla.
 * @param {Array<object>} columns Columnas de DataTables.
 * @param {object} options Opciones adicionales.
 *
 * @returns {DataTable|null}
 */
export function createDataTable(target, columns, options = {}) {
    const table = resolveTable(target);

    if (!table) {
        console.warn('DataTable: no se encontró la tabla.', target);
        return null;
    }

    const url = options.url ?? table.dataset.url;

    if (!url) {
        console.warn('DataTable: la tabla no tiene una URL configurada.', table);
        return null;
    }

    const {
        url: ignoredUrl,
        requestHeaders = {},
        requestParams = {},
        loader = true,
        loaderText = 'Cargando',
        entityName = 'registros',
        language = {},
        ...dataTableOptions
    } = options;

    return new DataTable(table, {
        processing: true,
        serverSide: true,
        pageLength: 15,
        order: [[0, 'asc']],

        ajax: async (request, callback) => {
            try {
                const additionalParams = typeof requestParams === 'function'
                    ? requestParams()
                    : requestParams;

                const response = await get(
                    url,
                    loaderText+ ' '+entityName,
                    {
                        ...request,
                        ...additionalParams,
                    },
                    {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...requestHeaders,
                    },
                    loader
                );

                callback(response);
            } catch (error) {
                console.error('DataTable: error al cargar información.', error);

                callback({
                    draw: request.draw,
                    recordsTotal: 0,
                    recordsFiltered: 0,
                    data: [],
                    error: 'No fue posible cargar la información.',
                });
            }
        },

        columns,

        language: {
            ...DEFAULT_LANGUAGE,
            info: `Mostrando _START_ a _END_ de _TOTAL_ ${entityName}`,
            infoEmpty: `Mostrando 0 a 0 de 0 ${entityName}`,
            infoFiltered: `(filtrado de _MAX_ ${entityName})`,
            ...language,
            paginate: {
                ...DEFAULT_LANGUAGE.paginate,
                ...language.paginate,
            },
        },

        ...dataTableOptions,
    });
}

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