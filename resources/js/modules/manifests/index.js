import { createDataTable, getDataTableData } from "../../shared/datatable.js";

const TABLE_ID = "manifests-table";

let activeFilters = [];

$(function () {
    initManifestFilters();
    initManifestsTable();
});

function initManifestsTable() {
    createDataTable(
        TABLE_ID,
        [
            {
                data: "id",
                name: "id",
                orderSequence: ["asc", "desc"],
            },
            {
                data: "status",
                name: "status",
                orderSequence: ["asc", "desc"],
            },
            {
                data: "client",
                name: "clients.id",
                orderSequence: ["asc", "desc"],
            },
            {
                data: "date",
                name: "services.service_date",
                orderSequence: ["asc", "desc"],
            },
            {
                data: null,
                name: "actions",
                orderable: false,
                searchable: false,
                className: "text-end",
                render(_data, _type, row) {
                    const hasInvoice = !!row.invoice_id;
                    const invoiceButton = hasInvoice
                        ? `<a
        data-id="${row.invoice_id}"
        class="btn btn-sm btn-outline-primary btn-invoice-pdf"
        title="Descargar Factura"
    >
        <i class="ti ti-file-type-pdf me-1"></i>
        Factura
    </a>`
                        : "";

                    return `
    <a
        data-id="${row.id}"
        class="btn btn-sm btn-outline-secondary btn-manifest-pdf"
        title="Descargar PDF"
    >
        <i class="ti ti-file-type-pdf me-1"></i>
        Manifiesto
    </a>
    ${invoiceButton}
`;
                },
            },
        ],
        {
            entityName: "manifiestos",
            pageLength: 10,
            ajax: (data, callback) => {
                getDataTableData(data, callback, {
                    url: "/admin/manifest-headers",
                    loaderText: "Cargando manifiestos...",
                    params: {
                        filters: activeFilters,
                    },
                });
            },
        },
    );
}

function initManifestFilters() {
    $("#apply-filters").on("click", function () {
        activeFilters = buildFilters();
        reloadManifests();
    });

    $("#clear-filters").on("click", function () {
        $("#manifest-service-date").val("");
        $("#invoice-status-all").prop("checked", true);
        activeFilters = [];
        reloadManifests();
    });
}

function buildFilters() {
    const filters = [];
    const serviceDate = $("#manifest-service-date").val();
    const invoiceStatus = $('input[name="invoice_status"]:checked').val();

    if (serviceDate) {
        filters.push({
            operator: "where",
            field: "services.service_date",
            value: serviceDate,
        });
    }

    if (invoiceStatus === "1") {
        filters.push({
            operator: "where_not_null",
            field: "services.invoice_id",
        });
    }

    if (invoiceStatus === "0") {
        filters.push({
            operator: "where_null",
            field: "services.invoice_id",
        });
    }

    return filters;
}

function reloadManifests() {
    const table = $(`#${TABLE_ID}`).DataTable();

    table.ajax.reload();
}
