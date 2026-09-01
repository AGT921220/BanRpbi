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

                <!-- Calendario -->
                <label
                    class="btn btn-icon btn-outline-secondary mb-0 position-relative"
                    title="Seleccionar fecha"
                >
                    <i class="ti ti-calendar"></i>

                    <input
                        type="date"
                        id="service-date"
                        class="position-absolute opacity-0"
                        style="width: 1px; height: 1px;"
                        value="{{ $serviceDate ?? '' }}"
                    >
                </label>

            </div>
        </div>
    </div>
</div>
