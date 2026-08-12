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
                    Se requieren las aprobaciones de <strong>Director Ventas</strong> y
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
                                            <form
                                                method="POST"
                                                action="{{ route('approvals.approve', $client) }}"
                                                onsubmit="return confirm('¿Registrar tu aprobación como director?')"
                                            >
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="ti ti-check me-1"></i>
                                                    Aprobar
                                                </button>
                                            </form>
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
@endsection
