@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\Contract> $contracts */
    $contracts ??= collect();
    /** @var \Illuminate\Support\Collection<int, \App\Models\Zone> $zones */
    $zones ??= collect();
    $frequencyLabels = \App\Models\Contract::frequencyLabels();
@endphp

@can(\App\Features\Permissions\Constants\PermissionTypes::CLIENTS_ASSIGN_CONTRACTS)
    <div
        class="modal modal-blur fade"
        id="configure-client-modal"
        tabindex="-1"
        aria-labelledby="configure-client-modal-title"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <form id="configure-client-form" method="POST" action="#">
                    @csrf

                    <input type="hidden" name="client_id" id="configure-client-id" value="">

                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title" id="configure-client-modal-title">
                                <i class="ti ti-file-plus me-2" id="configure-client-modal-icon"></i>
                                <span id="configure-client-modal-title-text">Asignar contrato</span>
                            </h5>
                            <div class="text-secondary" id="configure-client-name"></div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body">
                        <ul class="steps steps-counter steps-yellow my-3" id="configure-client-steps">
                            <li class="step-item active" data-step="1">Contrato</li>
                            <li class="step-item" data-step="2">Zona</li>
                            <li class="step-item" data-step="3">Resumen</li>
                        </ul>

                        <div class="alert alert-warning d-none" id="configure-client-rejection"></div>
                        <div class="alert alert-info d-none" id="configure-client-readonly">
                            Esta configuración no se puede editar en el estado actual.
                        </div>

                        <div class="configure-step" data-step-panel="1">
                            <div class="mb-3">
                                <label class="form-label required" for="configure-contract-id">Contrato</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon">
                                        <i class="ti ti-file-description"></i>
                                    </span>
                                    <select
                                        name="contract_id"
                                        id="configure-contract-id"
                                        class="form-select"
                                    >
                                        <option value="">Selecciona un contrato</option>
                                        @foreach ($contracts as $contract)
                                            <option
                                                value="{{ $contract->id }}"
                                                data-duration-months="{{ $contract->duration_months }}"
                                                data-frequency="{{ $frequencyLabels[$contract->frequency] ?? $contract->frequency }}"
                                                data-frequency-key="{{ $contract->frequency }}"
                                                data-notes="{{ e($contract->notes ?? '') }}"
                                                data-cost="{{ $contract->cost }}"
                                                data-profiles="{{ e($contract->rpbiProfiles->map(fn ($profile) => $profile->code.' — '.$profile->name)->implode(', ')) }}"
                                            >
                                                {{ $contract->name }}
                                                ({{ $contract->duration_months }} meses)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="card card-sm mb-3 d-none" id="configure-contract-details">
                                <div class="card-body">
                                    <div class="datagrid">
                                        <div class="datagrid-item">
                                            <div class="datagrid-title">Duración</div>
                                            <div class="datagrid-content" id="configure-contract-duration">—</div>
                                        </div>
                                        <div class="datagrid-item">
                                            <div class="datagrid-title">Frecuencia</div>
                                            <div class="datagrid-content" id="configure-contract-frequency">—</div>
                                        </div>
                                        <div class="datagrid-item">
                                            <div class="datagrid-title">Costo</div>
                                            <div class="datagrid-content" id="configure-contract-cost">—</div>
                                        </div>
                                        <div class="datagrid-item">
                                            <div class="datagrid-title">Perfiles RPBI</div>
                                            <div class="datagrid-content" id="configure-contract-profiles">—</div>
                                        </div>
                                        <div class="datagrid-item">
                                            <div class="datagrid-title">Notas del catálogo</div>
                                            <div class="datagrid-content" id="configure-contract-catalog-notes">—</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <x-form.input
                                        name="start_date"
                                        id="configure-start-date"
                                        type="date"
                                        label="Fecha de inicio"
                                        icon="ti ti-calendar-event"
                                    />
                                </div>
                                <div class="col-md-6">
                                    <x-form.input
                                        name="end_date"
                                        id="configure-end-date"
                                        type="date"
                                        label="Fecha de fin"
                                        icon="ti ti-calendar-due"
                                    />
                                </div>
                            </div>

                            <div class="mb-0">
                                <label class="form-label" for="configure-notes">Notas</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon align-items-start pt-2">
                                        <i class="ti ti-notes"></i>
                                    </span>
                                    <textarea
                                        name="notes"
                                        id="configure-notes"
                                        class="form-control"
                                        rows="3"
                                        maxlength="2000"
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="configure-step d-none" data-step-panel="2">
                            <div class="mb-3">
                                <label class="form-label required" for="configure-zone-id">Zona de recolección</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon">
                                        <i class="ti ti-map-pin"></i>
                                    </span>
                                    <select
                                        name="zone_id"
                                        id="configure-zone-id"
                                        class="form-select"
                                    >
                                        <option value="">Selecciona una zona</option>
                                        @foreach ($zones as $zone)
                                            <option
                                                value="{{ $zone->id }}"
                                                data-description="{{ e($zone->description ?? '') }}"
                                            >
                                                {{ $zone->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="card card-sm d-none" id="configure-zone-details">
                                <div class="card-body">
                                    <div class="datagrid-title">Descripción</div>
                                    <div class="datagrid-content" id="configure-zone-description">—</div>
                                </div>
                            </div>
                        </div>

                        <div class="configure-step d-none" data-step-panel="3">
                            <div class="alert alert-info d-none" id="summary-active-contract-alert"></div>
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Cliente</div>
                                    <div class="datagrid-content" id="summary-client-name">—</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Contrato nuevo / propuesto</div>
                                    <div class="datagrid-content" id="summary-contract">—</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Duración</div>
                                    <div class="datagrid-content" id="summary-duration">—</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Costo</div>
                                    <div class="datagrid-content" id="summary-cost">—</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Zona de recolección</div>
                                    <div class="datagrid-content" id="summary-zone">—</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Perfiles RPBI</div>
                                    <div class="datagrid-content" id="summary-profiles">—</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Estado</div>
                                    <div class="datagrid-content" id="summary-status">—</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Recolecciones a generar</div>
                                    <div class="datagrid-content" id="summary-collections-count">—</div>
                                </div>
                            </div>

                            <div class="mt-3 pt-3 border-top">
                                <label class="form-check">
                                    <input
                                        type="checkbox"
                                        class="form-check-input"
                                        id="configure-generate-invoice"
                                        name="generate_invoice"
                                        value="1"
                                    >
                                    <span class="form-check-label">
                                        <i class="ti ti-file-invoice me-1"></i>
                                        Generar factura
                                    </span>
                                </label>

                                <div class="mt-3 d-none" id="configure-invoice-manifests-wrap">
                                    <x-form.input
                                        name="invoice_manifest_count"
                                        id="configure-invoice-manifest-count"
                                        type="number"
                                        label="Manifiestos a facturar"
                                        icon="ti ti-files"
                                        min="1"
                                        step="1"
                                    />
                                    <div class="form-hint" id="configure-invoice-manifests-hint">
                                        El máximo es el número de recolecciones que se van a generar.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-link" data-bs-dismiss="modal">
                            Cerrar
                        </button>

                        <button type="button" class="btn btn-outline-secondary d-none" id="configure-prev-btn">
                            <i class="ti ti-arrow-left me-1"></i>
                            Anterior
                        </button>

                        <button type="button" class="btn btn-outline-primary" id="configure-save-close-btn">
                            <i class="ti ti-device-floppy me-1"></i>
                            Guardar y cerrar
                        </button>

                        <button type="button" class="btn btn-primary" id="configure-next-btn">
                            Guardar y continuar
                            <i class="ti ti-arrow-right ms-1"></i>
                        </button>

                        <button type="button" class="btn btn-success d-none" id="configure-submit-btn" disabled>
                            <i class="ti ti-send me-1"></i>
                            Finalizar y enviar a aprobación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan
