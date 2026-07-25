import DataTable from 'datatables.net-bs5';
import 'datatables.net-bs5/css/dataTables.bootstrap5.css';

const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute('content');

function initClientsTable() {
    const table = document.getElementById('clients-table');

    if (!table || !table.dataset.url) {
        return;
    }

    new DataTable(table, {
        processing: true,
        serverSide: true,
        pageLength: 15,
        order: [[0, 'asc']],
        ajax: {
            url: table.dataset.url,
            type: 'GET',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
            },
        },
        columns: [
            { data: 'full_name' },
            { data: 'email' },
            { data: 'phone' },
            { data: 'company' },
            { data: 'created_at' },
            {
                data: 'actions',
                orderable: false,
                searchable: false,
                className: 'text-end',
                render: (data) => {
                    const wrapper = document.createElement('div');
                    wrapper.innerHTML = data || '';

                    return wrapper;
                },
            },
        ],
        language: {
            processing: 'Cargando...',
            search: 'Buscar:',
            lengthMenu: 'Mostrar _MENU_ registros',
            info: 'Mostrando _START_ a _END_ de _TOTAL_ clientes',
            infoEmpty: 'Mostrando 0 a 0 de 0 clientes',
            infoFiltered: '(filtrado de _MAX_ clientes)',
            emptyTable: 'No hay clientes registrados.',
            zeroRecords: 'No se encontraron clientes.',
            paginate: {
                first: 'Primera',
                last: 'Última',
                next: 'Siguiente',
                previous: 'Anterior',
            },
        },
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initClientsTable);
} else {
    initClientsTable();
}
