import { createDataTable } from '../../shared/datatable.js';

$(function () {
    const table = createDataTable('manifests-table', [
        {
            data: 'manifest_number',
            name: 'manifest_number',
        },
        {
            data: 'status',
            name: 'status',
        },
        {
            data: 'client',
            name: 'client',
        },
        {
            data: null,
            name: 'actions',
            orderable: false,
            searchable: false,
            className: 'text-end',
            render: function (_data, _type, row) {
                return `
                    <a
                        href="/admin/manifests/${row.id}"
                        class="btn btn-sm btn-outline-primary"
                    >
                        <i class="ti ti-eye me-1"></i>
                        Ver
                    </a>
                `;
            },
        },
    ], {
        entityName: 'manifiestos',
        loader: false,
    });

    const loadManifests = async (page = 1, perPage = 10) => {
        try {
            const params = new URLSearchParams({
                page,
                perPage,
            });

            const response = await fetch(
                `/admin/manifest-headers?${params.toString()}`,
                {
                    headers: {
                        'Accept': 'application/json',
                    },
                }
            );

            if (!response.ok) {
                throw new Error('No se pudieron cargar los manifiestos.');
            }

            const responseData = await response.json();

            const manifests = responseData.data ?? responseData;

            table.clear();
            table.rows.add(manifests);
            table.draw(false);
        } catch (error) {
            console.error(error);
        }
    };

    loadManifests();
});