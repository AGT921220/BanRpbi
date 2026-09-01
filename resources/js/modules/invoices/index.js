import { createDataTable } from "../../shared/datatable.js";

$(function () {
    createDataTable(
        "invoices-table",
        [
            {
                data: "id",
                name: "invoices.id",
                orderSequence: ["asc", "desc"],
            },
            {
                data: "status",
                name: "invoices.status",
                orderSequence: ["asc", "desc"],
            },
            {
                data: "client",
                name: "clients.company",
                orderSequence: ["asc", "desc"],
            },
            {
                data: "external_id",
                name: "invoices.external_id",
                orderSequence: ["asc", "desc"],
            },
            {
                data: "created_at",
                name: "invoices.created_at",
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
        data-id="${row.id}"
        class="btn btn-sm btn-outline-primary btn-invoice-pdf"
        title="Descargar Factura"
    >
        <i class="ti ti-file-type-pdf me-1"></i>
        Factura
    </a>
`;
                },
            },
        ],
        {
            entityName: "facturas",
            pageLength: 10,

            ajax: async (data, callback) => {
                try {
                    const params = new URLSearchParams({
                        offset: data.start,
                        limit: data.length,
                        draw: data.draw,
                    });

                    const order = data.order?.[0];

                    if (order) {
                        const column = data.columns?.[order.column];

                        if (
                            column?.name &&
                            column.name !== "actions" &&
                            ["asc", "desc"].includes(order.dir)
                        ) {
                            params.set("order_by", column.name);
                            params.set("order_direction", order.dir);
                        }
                    }

                    const response = await fetch(
                        `/admin/invoice-headers?${params.toString()}`,
                        {
                            headers: {
                                Accept: "application/json",
                            },
                        },
                    );

                    if (!response.ok) {
                        throw new Error(
                            "No se pudieron cargar las facturas.",
                        );
                    }

                    const payload = await response.json();

                    callback({
                        draw: payload.draw ?? data.draw,
                        recordsTotal: payload.recordsTotal ?? 0,
                        recordsFiltered: payload.recordsFiltered ?? 0,
                        data: payload.data ?? [],
                    });
                } catch (error) {
                    console.error(error);

                    callback({
                        draw: data.draw,
                        recordsTotal: 0,
                        recordsFiltered: 0,
                        data: [],
                    });
                }
            },
        },
    );
});
