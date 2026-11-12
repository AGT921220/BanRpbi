import { createDataTable, getDataTableData } from "../../shared/datatable.js";
import "./logbookPdf.js";

const TABLE_ID = "logbook-table";

$(function () {
    const table = document.getElementById(TABLE_ID);
    const form = document.getElementById("logbook-form");

    if (!table || !form) {
        return;
    }

    const general = table.dataset.general === "1";
    const columns = [];

    if (!general) {
        columns.push({
            data: "id",
            name: "actions",
            orderable: false,
            searchable: false,
            render(id) {
                return `
                    <input
                        type="checkbox"
                        class="form-check-input"
                        name="process_ids[]"
                        value="${id}"
                        aria-label="Seleccionar tratamiento ${id}"
                    >
                `;
            },
        });
    }

    columns.push(
        {
            data: "id",
            name: "waste_processes.id",
            orderSequence: ["asc", "desc"],
        },
        {
            data: "fecha",
            name: "waste_processes.date_start",
            orderSequence: ["asc", "desc"],
        },
        {
            data: "tipo",
            name: "waste_process_types.name",
            orderSequence: ["asc", "desc"],
        },
        {
            data: "estado",
            name: "waste_processes.id",
            orderable: false,
        },
        {
            data: "manifiestos",
            name: "actions",
            orderable: false,
        },
    );

    createDataTable(TABLE_ID, columns, {
        entityName: "tratamientos",
        pageLength: 15,
        order: [[general ? 1 : 2, "asc"]],
        ajax: (data, callback) => {
            getDataTableData(data, callback, {
                url: table.dataset.url,
                loaderText: "Cargando tratamientos...",
                params: currentParams(form),
            });
        },
    });

    form.addEventListener("submit", (event) => {
        event.preventDefault();
        reloadLogbook();
    });

    document.querySelectorAll("[data-logbook-filter]").forEach((link) => {
        link.addEventListener("click", (event) => {
            event.preventDefault();
            const input = form.querySelector("[name=filtro]");

            if (input) {
                input.value = link.dataset.logbookFilter;
            }

            document.querySelectorAll("[data-logbook-filter]").forEach((item) => {
                const active = item === link;
                item.classList.toggle("btn-primary", active);
                item.classList.toggle("btn-outline-secondary", !active);
            });

            reloadLogbook();
        });
    });
});

function currentParams(form) {
    return {
        filtro: form.querySelector("[name=filtro]")?.value || "ambas",
        date_from: form.querySelector("[name=date_from]")?.value || "",
        date_to: form.querySelector("[name=date_to]")?.value || "",
    };
}

function reloadLogbook() {
    const table = $(`#${TABLE_ID}`).DataTable();

    table.ajax.reload();
}
