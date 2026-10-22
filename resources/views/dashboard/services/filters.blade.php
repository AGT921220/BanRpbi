<div class="card mb-3">
    <div class="card-body py-3">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">

            <div>
                <div class="text-secondary small">
                    Fecha de recolección
                </div>

                <div class="fw-bold fs-3" id="service-date-title">
                    Hoy
                </div>

                <div class="text-secondary" id="service-date-text"></div>
            </div>

            <div class="d-flex align-items-center gap-2">

                <!-- Día anterior -->
                <button
                    type="button"
                    class="btn btn-icon btn-outline-secondary"
                    id="service-prev-day"
                    title="Día anterior"
                >
                    <i class="ti ti-chevron-left"></i>
                </button>

                <!-- Hoy -->
                <button
                    type="button"
                    class="btn btn-primary"
                    id="service-today"
                    title="Ir a hoy"
                >
                    <i class="ti ti-calendar-event me-1"></i>
                    Hoy
                </button>

                <!-- Día siguiente -->
                <button
                    type="button"
                    class="btn btn-icon btn-outline-secondary"
                    id="service-next-day"
                    title="Día siguiente"
                >
                    <i class="ti ti-chevron-right"></i>
                </button>

                <!-- Calendario: el input cubre el botón para abrir el picker nativo -->
                <span
                    class="btn btn-icon btn-outline-secondary mb-0 position-relative overflow-hidden p-0"
                    title="Seleccionar fecha"
                    style="width: 2.2rem; height: 2.2rem;"
                >
                    <i class="ti ti-calendar" style="pointer-events: none;"></i>
                    <input
                        type="date"
                        id="service-date"
                        class="position-absolute top-0 start-0 w-100 h-100 border-0"
                        style="opacity: 0; cursor: pointer;"
                        value="{{ $serviceDate ?? '' }}"
                        aria-label="Seleccionar fecha"
                    >
                </span>

            </div>
        </div>
    </div>
</div>
