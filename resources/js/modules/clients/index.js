import { createDataTable } from '../../shared/datatable.js';

document.addEventListener('DOMContentLoaded', () => {
    createDataTable('clients-table', [
        {
            data: 'full_name',
            name: 'full_name',
        },
        {
            data: 'email',
            name: 'email',
        },
        {
            data: 'phone',
            name: 'phone',
        },
        {
            data: 'company',
            name: 'company',
        },
        {
            data: 'created_at',
            name: 'created_at',
        },
        {
            data: 'actions',
            name: 'actions',
            orderable: false,
            searchable: false,
            className: 'text-end',
            render: (data) => data || '',
        },
    ], {
        entityName: 'clientes',
    });
});