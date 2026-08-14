import { createDataTable } from "../../shared/datatable.js";

$(function () {
    createDataTable(
        "manifests-table",
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
                    let hasInvoice = !!row.invoice_id;
                    console.log(hasInvoice);

                    let invoiceButton = hasInvoice
                        ? `<a
        data-id="${row.invoice_id}"
        class="btn btn-sm btn-outline-primary btn-invoice-pdf"
        title="Descargar Factura"
    >
        <i class="ti ti-file-type-pdf me-1"></i>
        Factura
    </a>
`
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
                        `/admin/manifest-headers?${params.toString()}`,
                        {
                            headers: {
                                Accept: "application/json",
                            },
                        },
                    );

                    if (!response.ok) {
                        throw new Error(
                            "No se pudieron cargar los manifiestos.",
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
