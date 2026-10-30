@extends('layouts.app')

@section('title', 'Proceso #'.$process->id)
@section('page-title', 'Proceso #'.$process->id)

@section('page-actions')
    <a href="{{ route('procesos.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i>
        Listado
    </a>
    @unless ($process->is_finished)
        <a href="{{ route('procesos.edit', $process->id) }}" class="btn btn-primary">
            <i class="ti ti-pencil me-1"></i>
            Continuar captura
        </a>
    @endunless
@endsection

@section('content')
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Resumen</h3>
                    </div>
                    <div class="card-body">
                        <div class="datagrid">
                            <div class="datagrid-item">
                                <div class="datagrid-title">Tipo</div>
                                <div class="datagrid-content">{{ $process->type?->name ?? '—' }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">
                                    {{ $process->waste_process_type_id === \App\Models\WasteProcessType::PROCESS_ESTERILIZACION ? 'Fecha de carga' : 'Fecha' }}
                                </div>
                                <div class="datagrid-content">{{ $process->date_start }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Hora de carga</div>
                                <div class="datagrid-content">{{ $process->hour_start_machine ? \Illuminate\Support\Carbon::parse($process->hour_start_machine)->format('H:i') : '—' }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">
                                    {{ $process->waste_process_type_id === \App\Models\WasteProcessType::PROCESS_ESTERILIZACION ? 'Hora término del proceso' : 'Paro de carga' }}
                                </div>
                                <div class="datagrid-content">{{ $process->hour_machine ? \Illuminate\Support\Carbon::parse($process->hour_machine)->format('H:i') : '—' }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Observaciones</div>
                                <div class="datagrid-content">{{ $process->comments ?: '—' }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Estado</div>
                                <div class="datagrid-content">
                                    {{ $process->is_finished ? 'Cerrado' : 'Abierto' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($process->waste_process_type_id === \App\Models\WasteProcessType::PROCESS_INCINERACION)
                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">Parámetros de incineración</h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                @foreach ([
                                    'temp_camara_primaria' => 'Temp. cámara primaria',
                                    'temp_camara_secundaria' => 'Temp. cámara secundaria',
                                    'temp_entrada_venturi' => 'Temperatura entrada Venturi',
                                    'temp_salida_venturi' => 'Temperatura salida Venturi',
                                    'temp_salida_chimenea' => 'Temperatura salida chimenea',
                                    'oxigeno' => 'O2 (Oxígeno)',
                                    'presion_negativa' => 'Presión negativa (IN H2O)',
                                    'consumo_gas_lp' => 'Consumo de gas LP',
                                    'consumo_promedio_agua' => 'Consumo promedio de agua',
                                    'evaporacion_agua_tratada' => 'Evaporación agua tratada',
                                    'patologicos' => 'Patológicos',
                                    'cultivos_cepas' => 'Cultivos y cepas',
                                    'sangre' => 'Sangre',
                                    'ceniza_generada' => 'Ceniza generada',
                                    'descarga_lavador' => 'Descarga de lavador',
                                ] as $field => $label)
                                    <div class="datagrid-item">
                                        <div class="datagrid-title">{{ $label }}</div>
                                        <div class="datagrid-content">{{ $process->{$field} ?? '—' }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                @if ($process->waste_process_type_id === \App\Models\WasteProcessType::PROCESS_ESTERILIZACION)
                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">Parámetros de esterilización</h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                @foreach ([
                                    'temp_esterilizador' => 'Temperatura del esterilizador',
                                    'presion_camara_principal' => 'Presión cámara principal',
                                    'eliminacion_biologica' => 'Eliminación biológica',
                                    'ciclo_carga' => 'Ciclo de carga',
                                    'rpbi_bi1_cepas' => 'RPBI BI1 Cepas y cultivos',
                                    'rpbi_bi2_punzocortantes' => 'RPBI BI2 Punzocortantes',
                                    'rpbi_bi4_no_anatomicos' => 'RPBI BI4 No anatómicos',
                                    'rpbi_bi5_sangre' => 'RPBI BI5 Sangre',
                                    'total_procesada' => 'Total procesada',
                                ] as $field => $label)
                                    <div class="datagrid-item">
                                        <div class="datagrid-title">{{ $label }}</div>
                                        <div class="datagrid-content">{{ $process->{$field} ?? '—' }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">Cantidades por manifiesto</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table card-table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Manifiesto</th>
                                    <th>Cliente</th>
                                    <th>Perfil</th>
                                    <th class="text-end">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($process->details as $detail)
                                    @php
                                        $client = $detail->manifest?->service?->client;
                                        $clientName = $client?->company
                                            ?: trim(($client?->name ?? '').' '.($client?->parentarl_surname ?? ''));
                                    @endphp
                                    <tr>
                                        <td>#{{ $detail->manifest_id }}</td>
                                        <td>{{ $clientName !== '' ? $clientName : '—' }}</td>
                                        <td>{{ $detail->serviceDetail?->rpbiProfile?->name ?? '—' }}</td>
                                        <td class="text-end">{{ $detail->total_weight }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                {{-- <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Certificados / folios</h3>
                    </div>
                    <div class="list-group list-group-flush">
                        @forelse ($process->certificates as $certificate)
                            <div class="list-group-item">
                                Cliente #{{ $certificate->client_id }}
                                · Cert #{{ $certificate->id }}
                                @foreach ($certificate->folios as $folio)
                                    <div class="text-secondary small">Folio #{{ $folio->id }}</div>
                                @endforeach
                            </div>
                        @empty
                            <div class="list-group-item text-secondary">Sin certificados.</div>
                        @endforelse
                    </div>
                </div> --}}

            </div>
        </div>
    </div>
@endsection
