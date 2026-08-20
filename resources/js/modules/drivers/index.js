import { createDataTable } from "../../shared/datatable.js";

$(function () {
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content") ?? "";

    createDataTable(
        "drivers-table",
        [
            {
                data: "name",
                name: "drivers.name",
                orderSequence: ["asc", "desc"],
            },
            {
                data: "user",
                name: "users.name",
                orderSequence: ["asc", "desc"],
            },
            {
                data: "phone",
                name: "drivers.phone",
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
    <div class="btn-list flex-nowrap">
        <a
            href="/drivers/${row.id}/edit"
            class="btn btn-sm btn-outline-primary"
        >
            <i class="ti ti-pencil me-1"></i>
            Editar
        </a>
        <form
            action="/drivers/${row.id}"
            method="POST"
            onsubmit="return confirm('¿Eliminar este chofer?')"
        >
            <input type="hidden" name="_token" value="${csrfToken}">
            <input type="hidden" name="_method" value="DELETE">
            <button type="submit" class="btn btn-sm btn-outline-danger">
                <i class="ti ti-trash me-1"></i>
                Eliminar
            </button>
        </form>
    </div>
`;
                },
            },
        ],
        {
            entityName: "choferes",
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
                        `/driver-headers?${params.toString()}`,
                        {
                            headers: {
                                Accept: "application/json",
                            },
                        },
                    );

                    if (!response.ok) {
                        throw new Error(
                            "No se pudieron cargar los choferes.",
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
