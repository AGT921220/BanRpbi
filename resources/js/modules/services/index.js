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
                data: null,
                name: "actions",
                orderable: false,
                searchable: false,
                className: "text-end",
                render(_data, _type, row) {
                    return `
                        <a
                            href="/admin/services/${row.id}"
                            class="btn btn-sm btn-outline-primary"
                        >
                            <i class="ti ti-eye me-1"></i>
                            Ver
                        </a>
                    `;
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
