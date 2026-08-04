import { createDataTable } from '../../shared/datatable.js';

document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content') ?? '';

    createDataTable('clients-table', [
        { data: 'full_name', name: 'full_name' },
        { data: 'email', name: 'email' },
        { data: 'phone', name: 'phone' },
        { data: 'company', name: 'company' },
        { data: 'created_at', name: 'created_at' },
        {
            data: null,
            name: 'actions',
            orderable: false,
            searchable: false,
            className: 'text-end',
            render: (_data, _type, row) => {
                let actions = '';

                if (!row.has_contract) {
                    actions += `
                        <a
                            class="btn btn-sm btn-success assign-contract-btn"
                            data-client-id="${row.id}"
                        >
                            <i class="ti ti-file-plus me-1"></i>
                            Asignar contrato
                        </a>
                    `;
                }

                if (row.can_update) {
                    actions += `
                        <a
                            href="/clients/${row.id}/edit"
                            class="btn btn-sm btn-outline-primary"
                        >
                            <i class="ti ti-pencil me-1"></i>
                            Editar
                        </a>
                    `;
                }

                if (row.can_delete) {
                    actions += `
                        <form
                            action="/clients/${row.id}"
                            method="POST"
                            onsubmit="return confirm('¿Eliminar este cliente?')"
                        >
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="ti ti-trash me-1"></i>
                                Eliminar
                            </button>
                        </form>
                    `;
                }

                return actions
                    ? `<div class="btn-list flex-nowrap">${actions}</div>`
                    : '';
            },
        },
    ], {
        entityName: 'clientes',
        loader: false,
    });
});

$(document).on('click', '.assign-contract-btn', function () {
    console.log('Assign contract button clicked');
    const clientId = $(this).closest('tr').data('id');
    const url = `/clients/${clientId}/assign-contract`;
    alert(clientId);
});