@extends('layouts.app')

@section('title', $title)
@section('page-title', $title)

@section('content')
    <style>
        @media print {
            .navbar,
            .page-header,
            .footer,
            .no-print {
                display: none !important;
            }

            .page-wrapper {
                margin: 0 !important;
            }
        }
    </style>

    <div class="container-xl">
        <form method="GET" action="{{ route('procesos.bitacora', $type) }}" class="no-print">
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-filter me-2"></i>
                        Rango y selección
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="date_from">Desde</label>
                            <div class="input-icon">
                                <span class="input-icon-addon">
                                    <i class="ti ti-calendar"></i>
                                </span>
                                <input
                                    type="date"
                                    name="date_from"
                                    id="date_from"
                                    class="form-control"
                                    value="{{ $dateFrom }}"
                                >
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="date_to">Hasta</label>
                            <div class="input-icon">
                                <span class="input-icon-addon">
                                    <i class="ti ti-calendar"></i>
                                </span>
                                <input
                                    type="date"
                                    name="date_to"
                                    id="date_to"
                                    class="form-control"
                                    value="{{ $dateTo }}"
                                >
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-outline-secondary" name="generate" value="0">
                        <i class="ti ti-search me-1"></i>
                        Buscar
                    </button>
                    <button type="submit" class="btn btn-primary" name="generate" value="1">
                        <i class="ti ti-notebook me-1"></i>
                        Generar bitácora
                    </button>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Procesos</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th class="w-1">Sel.</th>
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Manifiestos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($processes as $process)
                                <tr>
                                    <td>
                                        <input
                                            type="checkbox"
                                            class="form-check-input"
                                            name="process_ids[]"
                                            value="{{ $process->id }}"
                                            @checked(in_array($process->id, $selectedIds, true))
                                            aria-label="Seleccionar proceso {{ $process->id }}"
                                        >
                                    </td>
                                    <td>{{ $process->id }}</td>
                                    <td>{{ optional($process->date_start)->format('d/m/Y') }}</td>
                                    <td>{{ $process->is_finished ? 'Cerrado' : 'Proceso' }}</td>
                                    <td>
                                        {{ $process->details->pluck('manifest_id')->unique()->filter()->implode(', ') ?: '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-secondary text-center py-4">
                                        No hay procesos en el rango indicado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </form>

        @if ($generated)
            <div class="d-flex justify-content-end mb-3 no-print">
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="ti ti-printer me-1"></i>
                    Imprimir
                </button>
            </div>

            @forelse ($logbook as $process)
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            Proceso #{{ $process->id }}
                            · {{ optional($process->date_start)->format('d/m/Y') }}
                            · {{ $process->is_finished ? 'Cerrado' : 'Proceso' }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="datagrid mb-3">
                            <div class="datagrid-item">
                                <div class="datagrid-title">Hora de carga</div>
                                <div class="datagrid-content">
                                    {{ $process->hour_start_machine ? \Illuminate\Support\Carbon::parse($process->hour_start_machine)->format('H:i') : '—' }}
                                </div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Hora de término</div>
                                <div class="datagrid-content">
                                    {{ $process->hour_machine ? \Illuminate\Support\Carbon::parse($process->hour_machine)->format('H:i') : '—' }}
                                </div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Observaciones</div>
                                <div class="datagrid-content">{{ $process->comments ?: '—' }}</div>
                            </div>
                        </div>

                        @if ($isIncineracion)
                            <div class="datagrid mb-3">
                                @foreach ([
                                    'temp_camara_primaria' => 'Temp. cámara primaria',
                                    'temp_camara_secundaria' => 'Temp. cámara secundaria',
                                    'oxigeno' => 'Oxígeno',
                                    'consumo_gas_lp' => 'Consumo de gas LP',
                                    'ceniza_generada' => 'Ceniza generada',
                                ] as $field => $label)
                                    <div class="datagrid-item">
                                        <div class="datagrid-title">{{ $label }}</div>
                                        <div class="datagrid-content">{{ filled($process->{$field}) ? $process->{$field} : 0 }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="datagrid mb-3">
                                @foreach ([
                                    'temp_esterilizador' => 'Temperatura del esterilizador',
                                    'presion_camara_principal' => 'Presión cámara principal',
                                    'ciclo_carga' => 'Ciclo de carga',
                                    'total_procesada' => 'Total procesada',
                                ] as $field => $label)
                                    <div class="datagrid-item">
                                        <div class="datagrid-title">{{ $label }}</div>
                                        <div class="datagrid-content">
                                            @if ($field === 'ciclo_carga')
                                                {{ $process->{$field} ?: '—' }}
                                            @else
                                                {{ filled($process->{$field}) ? $process->{$field} : 0 }}
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Manifiesto</th>
                                        <th>Cliente</th>
                                        <th>Perfil</th>
                                        <th class="text-end">Cantidad</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($process->details as $detail)
                                        @php
                                            $client = $detail->manifest?->service?->client;
                                            $clientName = $client?->legal_name
                                                ?: ($client?->company
                                                    ?: trim(($client?->name ?? '').' '.($client?->parentarl_surname ?? '')));
                                        @endphp
                                        <tr>
                                            <td>#{{ $detail->manifest_id }}</td>
                                            <td>{{ $clientName !== '' ? $clientName : '—' }}</td>
                                            <td>{{ $detail->serviceDetail?->rpbiProfile?->name ?? '—' }}</td>
                                            <td class="text-end">{{ $detail->total_weight }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-secondary">Sin manifiestos.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @empty
                <div class="card">
                    <div class="card-body text-secondary text-center py-4">
                        No hay procesos seleccionados para la bitácora.
                    </div>
                </div>
            @endforelse
        @endif
    </div>
@endsection
