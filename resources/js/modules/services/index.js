import { createDataTable, getDataTableData } from "../../shared/datatable.js";

const TABLE_ID = "services-table";
const DATE_INPUT = "#service-date";

$(function () {
    initServiceDate();
    initServicesTable();
    initServiceDateEvents();
});

function initServicesTable() {
    createDataTable(
        TABLE_ID,
        [
            {
                data: "manifest_id",
                name: "manifests.id",
                orderSequence: ["asc", "desc"],
                render(data) {
                    return data ? `#${data}` : "—";
                },
            },
            {
                data: "status.status",
                name: "status",
                orderSequence: ["asc", "desc"],
                render(_data, _type, row) {
                    return row.status?.label || row.status?.status || "—";
                },
            },
            {
                data: "client_name",
                name: "clients.id",
                orderSequence: ["asc", "desc"],
            },
            {
                data: "driver_name",
                name: "drivers.name",
                orderSequence: ["asc", "desc"],
            },
            {
                data: null,
                name: "actions",
                orderable: false,
                searchable: false,
                className: "text-end",
                render(_data, _type, row) {
                    let actions = "";

                    if (row.can_view) {
                        actions += `
                            <a
                                href="/services/${row.id}"
                                class="btn btn-sm btn-outline-primary"
                            >
                                <i class="ti ti-eye me-1"></i>
                                Ver
                            </a>
                        `;
                    }

                    if (row.can_manifest_pdf && row.manifest_id) {
                        actions += `
                            <a
                                href="#"
                                data-id="${row.manifest_id}"
                                class="btn btn-sm btn-outline-secondary btn-manifest-pdf"
                                title="Manifiesto PDF"
                            >
                                <i class="ti ti-file-type-pdf me-1"></i>
                                Manifiesto
                            </a>
                        `;
                    }

                    if (row.can_invoice_pdf && row.invoice_id) {
                        actions += `
                            <a
                                href="#"
                                data-id="${row.invoice_id}"
                                class="btn btn-sm btn-outline-primary btn-invoice-pdf"
                                title="Factura PDF"
                            >
                                <i class="ti ti-file-invoice me-1"></i>
                                Factura
                            </a>
                        `;
                    }

                    return actions
                        ? `<div class="btn-list flex-nowrap justify-content-end">${actions}</div>`
                        : "";
                },
            },
        ],
        {
            entityName: "recolecciones",
            pageLength: 10,
            ajax: (data, callback) => {
                getDataTableData(data, callback, {
                    url: "/admin/service-headers",
                    loaderText: "Cargando recolecciones...",
                    params: {
                        filters: [
                            {
                                operator: "where",
                                field: "service_date",
                                value: $(DATE_INPUT).val(),
                            },
                        ],
                    },
                });
            },
        },
    );
}

function initServiceDate() {
    const $date = $(DATE_INPUT);

    if (!$date.val()) {
        $date.val(formatDate(new Date()));
    }

    updateServiceDateLabel();
}

function initServiceDateEvents() {
    $(DATE_INPUT).on("change", function () {
        updateServiceDateLabel();
        reloadServices();
    });

    $("#service-prev-day").on("click", function () {
        changeServiceDate(-1);
    });

    $("#service-next-day").on("click", function () {
        changeServiceDate(1);
    });

    $("#service-today").on("click", function () {
        setServiceDate(new Date());
    });

    $("#service-date-title, #service-date-text")
        .css("cursor", "pointer")
        .on("click", function () {
            openServiceDatePicker();
        });
}

function openServiceDatePicker() {
    const input = document.querySelector(DATE_INPUT);

    if (!input) {
        return;
    }

    try {
        if (typeof input.showPicker === "function") {
            input.showPicker();
            return;
        }
    } catch (_error) {
        // showPicker puede fallar si el gesto no es válido; se usa click nativo.
    }

    input.focus();
    input.click();
}

function reloadServices() {
    const table = $(`#${TABLE_ID}`).DataTable();

    table.ajax.reload();
}

function changeServiceDate(days) {
    const value = $(DATE_INPUT).val();
    const date = value ? parseDate(value) : new Date();

    date.setDate(date.getDate() + days);

    setServiceDate(date);
}

function setServiceDate(date) {
    $(DATE_INPUT).val(formatDate(date)).trigger("change");
}

function parseDate(value) {
    const [year, month, day] = value.split("-").map(Number);

    return new Date(year, month - 1, day);
}

function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");

    return `${year}-${month}-${day}`;
}

function updateServiceDateLabel() {
    const value = $(DATE_INPUT).val();

    if (!value) {
        return;
    }

    const selectedDate = parseDate(value);
    const today = new Date();

    today.setHours(0, 0, 0, 0);
    selectedDate.setHours(0, 0, 0, 0);

    const difference = Math.round(
        (selectedDate.getTime() - today.getTime()) / (1000 * 60 * 60 * 24),
    );

    let title;

    if (difference === 0) {
        title = "Hoy";
    } else if (difference === 1) {
        title = "Mañana";
    } else if (difference === -1) {
        title = "Ayer";
    } else {
        title = selectedDate.toLocaleDateString("es-MX", {
            weekday: "long",
        });

        title = title.charAt(0).toUpperCase() + title.slice(1);
    }

    $("#service-date-title").text(title);

    $("#service-date-text").text(
        selectedDate.toLocaleDateString("es-MX", {
            day: "numeric",
            month: "long",
            year: "numeric",
        }),
    );
}
