@extends('layouts.app')

@section('title', 'Aprobaciones')
@section('page-title', 'Aprobaciones')

@section('content')
    <div class="container-xl">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-circle-check me-2"></i>
                    Clientes pendientes de aprobación
                </h3>
            </div>

            <div class="card-body border-bottom">
                <p class="text-secondary mb-0">
                    Se requieren las aprobaciones de <strong>Director de Ventas</strong> y
                    <strong>Director General</strong>. Al completar ambas, el contrato pendiente
                    reemplaza al vigente (si existe).
                </p>
            </div>

            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Contrato nuevo</th>
                            <th>Contrato vigente</th>
                            <th>Zona</th>
                            <th>Aprobaciones</th>
                            <th>Enviado</th>
                            <th class="w-1">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($clients as $client)
                            @php
                                $approvedRoles = $client->configurationApprovals
                                    ->pluck('role_name')
                                    ->all();
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $client->fullName() }}</div>
                                    <div class="text-secondary">{{ $client->email }}</div>
                                </td>
                                <td>
                                    {{ $client->pendingContract?->contract?->name ?? '—' }}
                                    @if ($client->pendingContract?->contract)
                                        <div class="text-secondary">
                                            {{ $client->pendingContract->contract->duration_months }} meses
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    {{ $client->activeContract?->contract?->name ?? 'Ninguno' }}
                                    @if ($client->activeContract?->end_date)
                                        <div class="text-secondary">
                                            Hasta {{ $client->activeContract->end_date->format('d/m/Y') }}
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $client->zone?->name ?? '—' }}</td>
                                <td>
                                    <div class="badges-list">
                                        @foreach ($requiredApprovalRoles as $roleName)
                                            @if (in_array($roleName, $approvedRoles, true))
                                                <span class="badge bg-success-lt">{{ $roleName }}</span>
                                            @else
                                                <span class="badge bg-secondary-lt">{{ $roleName }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    {{ $client->configuration_submitted_at?->format('d/m/Y H:i') ?? '—' }}
                                </td>
                                <td>
                                    <div class="btn-list flex-nowrap">
                                        @if($client->can_approve)
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-success js-approve-client"
                                                data-bs-toggle="modal"
                                                data-bs-target="#approve-client-modal"
                                                data-client-id="{{ $client->id }}"
                                                data-client-name="{{ $client->fullName() }}"
                                            >
                                                <i class="ti ti-check me-1"></i>
                                                Aprobar
                                            </button>
                                        @endif

                                        @if($client->can_reject)
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#reject-client-modal-{{ $client->id }}"
                                            >
                                                <i class="ti ti-x me-1"></i>
                                                Rechazar
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>

                            @if($client->can_reject)
                                <div
                                    class="modal modal-blur fade"
                                    id="reject-client-modal-{{ $client->id }}"
                                    tabindex="-1"
                                    aria-hidden="true"
                                >
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <form
                                                method="POST"
                                                action="{{ route('approvals.reject', $client) }}"
                                            >
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Rechazar configuración</h5>
                                                    <button
                                                        type="button"
                                                        class="btn-close"
                                                        data-bs-dismiss="modal"
                                                        aria-label="Cerrar"
                                                    ></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="text-secondary">
                                                        Cliente: <strong>{{ $client->fullName() }}</strong>.
                                                        El contrato vigente no se cancelará.
                                                    </p>
                                                    <label class="form-label" for="reject-reason-{{ $client->id }}">
                                                        Motivo (opcional)
                                                    </label>
                                                    <textarea
                                                        name="reason"
                                                        id="reject-reason-{{ $client->id }}"
                                                        class="form-control"
                                                        rows="3"
                                                        maxlength="2000"
                                                    ></textarea>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-link" data-bs-dismiss="modal">
                                                        Cancelar
                                                    </button>
                                                    <button type="submit" class="btn btn-danger">
                                                        Confirmar rechazo
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <tr>
                                <td colspan="7" class="text-secondary text-center py-4">
                                    No hay clientes pendientes de aprobación.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($clients->hasPages())
                <div class="card-footer">
                    {{ $clients->links() }}
                </div>
            @endif
        </div>
    </div>

    <div
        class="modal modal-blur fade"
        id="approve-client-modal"
        tabindex="-1"
        aria-labelledby="approve-client-modal-title"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <form
                    id="approve-client-form"
                    method="POST"
                    action="#"
                    data-action-template="{{ route('approvals.approve', ['client' => '__CLIENT_ID__']) }}"
                >
                    @csrf
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    <div class="modal-status bg-success"></div>
                    <div class="modal-body text-center py-4">
                        <i class="ti ti-circle-check icon icon-lg text-success mb-2"></i>
                        <h3 id="approve-client-modal-title">Aprobar configuración</h3>
                        <div class="text-secondary" id="approve-client-message">
                            ¿Registrar tu aprobación como director?
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="w-100">
                            <div class="row">
                                <div class="col">
                                    <button type="button" class="btn w-100" data-bs-dismiss="modal">
                                        Cancelar
                                    </button>
                                </div>
                                <div class="col">
                                    <button type="submit" class="btn btn-success w-100" id="approve-client-submit">
                                        <i class="ti ti-check me-1"></i>
                                        Sí, aprobar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modalEl = document.getElementById('approve-client-modal');
            const form = document.getElementById('approve-client-form');
            const messageEl = document.getElementById('approve-client-message');
            const submitBtn = document.getElementById('approve-client-submit');

            if (!modalEl || !form || !messageEl || !submitBtn) {
                return;
            }

            const urlTemplate = form.dataset.actionTemplate || '';
            const defaultMessage = '¿Registrar tu aprobación como director?';
            const submitDefaultHtml = submitBtn.innerHTML;

            const updateApproveModal = (clientId, clientName = '') => {
                form.action = urlTemplate.replace('__CLIENT_ID__', encodeURIComponent(clientId));
                messageEl.replaceChildren();

                if (clientName) {
                    const strong = document.createElement('strong');
                    strong.textContent = clientName;
                    messageEl.append('¿Registrar tu aprobación como director para ', strong, '?');
                    return;
                }

                messageEl.textContent = defaultMessage;
            };

            document.querySelectorAll('.js-approve-client').forEach((button) => {
                button.addEventListener('click', () => {
                    updateApproveModal(
                        button.dataset.clientId || '',
                        (button.dataset.clientName || '').trim(),
                    );
                });
            });

            modalEl.addEventListener('show.bs.modal', (event) => {
                const trigger = event.relatedTarget;

                if (!trigger?.getAttribute) {
                    return;
                }

                updateApproveModal(
                    trigger.getAttribute('data-client-id') || '',
                    (trigger.getAttribute('data-client-name') || '').trim(),
                );
            });

            modalEl.addEventListener('hidden.bs.modal', () => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = submitDefaultHtml;
            });

            form.addEventListener('submit', () => {
                submitBtn.disabled = true;
                submitBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Aprobando...';
            });
        });
    </script>
@endpush
