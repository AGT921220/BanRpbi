<div class="card mb-3">
    <div class="card-body py-3">

        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h3 class="card-title mb-1">Filtros</h3>
                <div class="text-secondary small">
                    Filtra los servicios según su estado de facturación.
                </div>
            </div>

            <i class="ti ti-filter fs-2 text-secondary"></i>
        </div>

        <div class="mb-3">
            <label class="form-label">
                Estado de facturación
            </label>

            <div class="btn-group" role="group">

                <input
                    type="radio"
                    class="btn-check"
                    name="invoice_status"
                    id="invoice-status-all"
                    value=""
                    checked
                >

                <label
                    class="btn btn-sm btn-outline-secondary"
                    for="invoice-status-all"
                >
                    Todos
                </label>

                <input
                    type="radio"
                    class="btn-check"
                    name="invoice_status"
                    id="invoice-status-invoiced"
                    value="1"
                >

                <label
                    class="btn btn-sm btn-outline-success"
                    for="invoice-status-invoiced"
                >
                    <i class="ti ti-file-check me-1"></i>
                    Facturados
                </label>

                <input
                    type="radio"
                    class="btn-check"
                    name="invoice_status"
                    id="invoice-status-not-invoiced"
                    value="0"
                >

                <label
                    class="btn btn-sm btn-outline-warning"
                    for="invoice-status-not-invoiced"
                >
                    <i class="ti ti-file-off me-1"></i>
                    No facturados
                </label>

            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <button
                type="button"
                class="btn btn-sm btn-outline-secondary"
                id="clear-filters"
            >
                Limpiar
            </button>

            <button
                type="button"
                class="btn btn-sm btn-primary"
                id="apply-filters"
            >
                <i class="ti ti-filter-check me-1"></i>
                Aplicar
            </button>
        </div>

    </div>
</div>