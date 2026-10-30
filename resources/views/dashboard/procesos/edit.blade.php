@extends('layouts.app')

@section('title', 'Captura proceso #'.$process->getWasteProcessId())
@section('page-title', 'Captura proceso #'.$process->getWasteProcessId())

@section('page-actions')
    <a href="{{ route('procesos.show', $process->getWasteProcessId()) }}" class="btn btn-outline-secondary">
        <i class="ti ti-eye me-1"></i>
        Ver
    </a>
@endsection

@section('content')
    @php $data = $processArray; @endphp
    <div class="container-xl">
        <form method="POST" action="{{ route('procesos.update', $process->getWasteProcessId()) }}" id="process-edit-form">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Datos generales</h3>
                        </div>
                        <div class="card-body">
                            <x-form.input
                                name="date_start"
                                :label="$isEsterilizacion ? 'Fecha de carga' : 'Fecha'"
                                type="date"
                                icon="ti ti-calendar"
                                :value="\Illuminate\Support\Carbon::parse($data['date_start'])->toDateString()"
                                :disabled="$isFinished"
                                required
                            />
                            <x-form.input
                                name="hour_start_machine"
                                label="Hora de carga"
                                type="time"
                                icon="ti ti-clock"
                                :value="$data['start_hour_machine']"
                                :disabled="$isFinished"
                            />
                            <x-form.input
                                name="hour_machine"
                                :label="$isEsterilizacion ? 'Hora término del proceso' : 'Paro de carga'"
                                type="time"
                                icon="ti ti-clock-stop"
                                :value="$data['hour_machine']"
                                :disabled="$isFinished"
                            />
                            <div class="mb-0">
                                <label class="form-label" for="comments">
                                    <i class="ti ti-notes me-1"></i>
                                    Observaciones
                                </label>
                                <textarea name="comments" id="comments" class="form-control" rows="3" @disabled($isFinished)>{{ old('comments', $data['params'][0]['comments'] ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                Parámetros
                                {{ $isIncineracion ? 'de incineración' : 'de esterilización' }}
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                @if ($isIncineracion)
                                    @foreach ([
                                        'temp_camara_primaria' => ['Temp. cámara primaria', 'ti ti-temperature'],
                                        'temp_camara_secundaria' => ['Temp. cámara secundaria', 'ti ti-temperature'],
                                        'temp_entrada_venturi' => ['Temperatura entrada Venturi', 'ti ti-temperature'],
                                        'temp_salida_venturi' => ['Temperatura salida Venturi', 'ti ti-temperature'],
                                        'temp_salida_chimenea' => ['Temperatura salida chimenea', 'ti ti-temperature'],
                                        'oxigeno' => ['O2 (Oxígeno)', 'ti ti-wind'],
                                        'presion_negativa' => ['Presión negativa (IN H2O)', 'ti ti-gauge'],
                                        'consumo_gas_lp' => ['Consumo de gas LP', 'ti ti-flame'],
                                        'consumo_promedio_agua' => ['Consumo promedio de agua', 'ti ti-droplet'],
                                        'evaporacion_agua_tratada' => ['Evaporación agua tratada', 'ti ti-droplet-half-2'],
                                        'patologicos' => ['Patológicos', 'ti ti-biohazard'],
                                        'cultivos_cepas' => ['Cultivos y cepas', 'ti ti-microscope'],
                                        'sangre' => ['Sangre', 'ti ti-droplet-filled'],
                                        'ceniza_generada' => ['Ceniza generada', 'ti ti-trash'],
                                        'descarga_lavador' => ['Descarga de lavador', 'ti ti-bucket'],
                                    ] as $field => [$label, $icon])
                                        <div class="col-md-4">
                                            <x-form.input
                                                :name="$field"
                                                :label="$label"
                                                type="number"
                                                :icon="$icon"
                                                step="0.01"
                                                :value="$data['params'][0][$field] ?? ''"
                                                :disabled="$isFinished"
                                            />
                                        </div>
                                    @endforeach
                                @endif

                                @if ($isEsterilizacion)
                                    @foreach ([
                                        'temp_esterilizador' => ['Temperatura del esterilizador', 'ti ti-temperature', 'number'],
                                        'presion_camara_principal' => ['Presión cámara principal', 'ti ti-gauge', 'number'],
                                        'eliminacion_biologica' => ['Eliminación biológica', 'ti ti-biohazard', 'text'],
                                        'ciclo_carga' => ['Ciclo de carga', 'ti ti-refresh', 'text'],
                                        'rpbi_bi1_cepas' => ['RPBI BI1 Cepas y cultivos', 'ti ti-microscope', 'number'],
                                        'rpbi_bi2_punzocortantes' => ['RPBI BI2 Punzocortantes', 'ti ti-needle', 'number'],
                                        'rpbi_bi4_no_anatomicos' => ['RPBI BI4 No anatómicos', 'ti ti-bone', 'number'],
                                        'rpbi_bi5_sangre' => ['RPBI BI5 Sangre', 'ti ti-droplet-filled', 'number'],
                                        'total_procesada' => ['Total procesada', 'ti ti-sum', 'number'],
                                    ] as $field => [$label, $icon, $type])
                                        <div class="col-md-4">
                                            <x-form.input
                                                :name="$field"
                                                :label="$label"
                                                :type="$type"
                                                :icon="$icon"
                                                :step="$type === 'number' ? '0.01' : null"
                                                :value="$data['params'][0][$field] ?? ''"
                                                :disabled="$isFinished"
                                            />
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>

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
                                        @forelse ($data['details'] as $detail)
                                            <tr>
                                                <td>#{{ $detail['manifest_id'] ?? '—' }}</td>
                                                <td>{{ $detail['client'] ?: '—' }}</td>
                                                <td>{{ $detail['profile'] ?? '—' }}</td>
                                                <td class="text-end">{{ $detail['total_weight'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-secondary">Sin manifiestos en este proceso.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @unless ($isFinished)
                                <div class="card-footer d-flex justify-content-end gap-2">
                                    <button type="submit" class="btn btn-outline-primary" name="finish" value="0">
                                        <i class="ti ti-device-floppy me-1"></i>
                                        Guardar
                                    </button>
                                    <button type="submit" class="btn btn-success" name="finish" value="1" @disabled(! $canFinish)>
                                        <i class="ti ti-check me-1"></i>
                                        Cerrar proceso
                                    </button>
                                </div>
                            @endunless
                        </div>
                </div>
            </div>
        </form>
    </div>
@endsection
