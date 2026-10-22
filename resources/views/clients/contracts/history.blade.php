@extends('layouts.app')

@section('title', 'Historial de contratos')
@section('page-title', 'Historial de contratos')

@section('page-actions')
    <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i>
        Volver a clientes
    </a>
    @can(\App\Features\Permissions\Constants\PermissionTypes::CLIENTS_UPDATE)
        <a href="{{ route('clients.edit', $client) }}" class="btn btn-outline-primary">
            <i class="ti ti-pencil me-1"></i>
            Editar cliente
        </a>
    @endcan
@endsection

@section('content')
    <div class="container-xl">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3 align-items-center">
                    <div class="col-md">
                        <div class="text-secondary small">Cliente</div>
                        <div class="fw-bold fs-3">
                            {{ $client->company ?: trim($client->name.' '.$client->parentarl_surname) }}
                        </div>
                        <div class="text-secondary">
                            {{ $client->email ?: 'Sin correo' }}
                            · {{ $client->phone ?: 'Sin teléfono' }}
                            · Zona: {{ $client->zone?->name ?? 'Sin zona' }}
                        </div>
                    </div>
                    <div class="col-md-auto">
                        <span class="badge bg-blue-lt">
                            {{ $history->count() }} contrato(s)
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-history me-2"></i>
                    Historial de contratos del cliente
                </h3>
            </div>

            <div class="card-body border-bottom py-3">
                <form
                    method="GET"
                    action="{{ route('clients.contracts.history', $client) }}"
                    class="row g-2 align-items-end"
                >
                    <div class="col-md-4 col-lg-3">
                        <label class="form-label" for="history-status">Estado</label>
                        <select name="status" id="history-status" class="form-select">
                            <option value="">Todos</option>
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected($statusFilter !== null && (int) $statusFilter === (int) $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-filter me-1"></i>
                            Filtrar
                        </button>
                    </div>
                    @if ($statusFilter !== null)
                        <div class="col-auto">
                            <a
                                href="{{ route('clients.contracts.history', $client) }}"
                                class="btn btn-outline-secondary"
                            >
                                <i class="ti ti-x me-1"></i>
                                Limpiar
                            </a>
                        </div>
                    @endif
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Contrato</th>
                            <th>Frecuencia</th>
                            <th>Duración</th>
                            <th>Vigencia</th>
                            <th>Estado</th>
                            <th>Costo</th>
                            <th>Perfiles RPBI</th>
                            <th>Notas</th>
                            <th class="w-1">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($history as $row)
                            @php
                                $badge = match ($row['status']) {
                                    \App\Models\ClientContract::STATUS_ACTIVE => 'bg-green-lt',
                                    \App\Models\ClientContract::STATUS_PENDING => 'bg-yellow-lt',
                                    \App\Models\ClientContract::STATUS_COMPLETED => 'bg-blue-lt',
                                    \App\Models\ClientContract::STATUS_CANCELLED => 'bg-red-lt',
                                    default => 'bg-secondary-lt',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $row['contract_name'] ?? '—' }}</div>
                                    <div class="text-secondary small">#{{ $row['id'] }}</div>
                                </td>
                                <td>{{ $row['frequency_label'] ?? '—' }}</td>
                                <td>
                                    {{ $row['duration_months'] !== null ? $row['duration_months'].' meses' : '—' }}
                                </td>
                                <td>
                                    {{ $row['start_date'] ?? '—' }}
                                    —
                                    {{ $row['end_date'] ?? '—' }}
                                </td>
                                <td>
                                    <span class="badge {{ $badge }}">{{ $row['status_label'] }}</span>
                                </td>
                                <td>
                                    {{ $row['price'] !== null ? '$'.number_format((float) $row['price'], 2) : '—' }}
                                </td>
                                <td>
                                    @forelse ($row['profiles'] ?? [] as $profile)
                                        <span class="badge bg-blue-lt">{{ $profile }}</span>
                                    @empty
                                        <span class="text-secondary">—</span>
                                    @endforelse
                                </td>
                                <td class="text-secondary">
                                    {{ \Illuminate\Support\Str::limit($row['notes'] ?? '—', 50) }}
                                </td>
                                <td>
                                    <div class="btn-list flex-nowrap">
                                        @if ($canViewContractPdf && ($row['can_show_pdf'] ?? false))
                                            <a
                                                href="#"
                                                data-id="{{ $row['id'] }}"
                                                class="btn btn-sm btn-outline-secondary btn-contract-pdf"
                                            >
                                                <i class="ti ti-file-text me-1"></i>
                                                PDF
                                            </a>
                                        @else
                                            <span class="text-secondary">—</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-secondary py-4">
                                    Este cliente no tiene contratos en el historial.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/modules/contracts/contractPdf.js')
    @vite('resources/js/modules/contracts/contractPdfBuilder.js')
@endpush
