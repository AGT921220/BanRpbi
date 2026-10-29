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
                                :label="$isIncineracion ? 'Fecha' : 'Fecha inicio'"
                                type="date"
                                icon="ti ti-calendar"
                                :value="\Illuminate\Support\Carbon::parse($data['date_start'])->toDateString()"
                                :disabled="$isFinished"
                                required
                            />
                            <x-form.input
                                name="hour_start_machine"
                                :label="$isIncineracion ? 'Hora de carga' : 'Hora inicio máquina'"
                                type="time"
                                icon="ti ti-clock"
                                :value="$data['start_hour_machine']"
                                :disabled="$isFinished"
                            />
                            @if ($isIncineracion)
                                <x-form.input
                                    name="hour_machine"
                                    label="Paro de carga"
                                    type="time"
                                    icon="ti ti-clock-stop"
                                    :value="$data['hour_machine']"
                                    :disabled="$isFinished"
                                />
                            @endif
                            @if ($isEsterilizacion)
                                <div class="mb-3">
                                    <label class="form-label" for="hour_machine">Hora fin máquina</label>
                                    <input type="time" name="hour_machine" id="hour_machine" class="form-control"
                                           value="{{ old('hour_machine', $data['hour_machine']) }}" @disabled($isFinished)>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="machine_number">Reactor</label>
                                    <select name="machine_number" id="machine_number" class="form-select" @disabled($isFinished)>
                                        <option value="">Selecciona</option>
                                        @foreach ($reactors as $reactor)
                                            <option value="{{ $reactor->id }}" @selected((string) old('machine_number', $data['machine_number']) === (string) $reactor->id)>
                                                {{ $reactor->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="solution">Solución</label>
                                    <input type="text" name="solution" id="solution" class="form-control"
                                           value="{{ old('solution', $data['solution']) }}" @disabled($isFinished)>
                                </div>
                            @endif
                            <div class="mb-0">
                                <label class="form-label" for="comments">
                                    <i class="ti ti-notes me-1"></i>
                                    {{ $isIncineracion ? 'Observaciones' : 'Comentarios' }}
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
                                        'cantidad_solucion_reciclar' => 'Solución a reciclar',
                                        'acido' => 'Ácido',
                                        'sosa_caustica' => 'Sosa cáustica',
                                        'eca_12' => 'ECA 12',
                                        'eca_22' => 'ECA 22',
                                        'eca_1350' => 'ECA 1350',
                                        'eca_49' => 'ECA 49',
                                        'eca_20pa' => 'ECA 20PA',
                                        'sulfactante_c500' => 'Sulfactante C500',
                                        'solucion_clarif_t42' => 'Clarif T42',
                                        'solucion_flocl_146' => 'Flocl 146',
                                        'ph_inicial' => 'pH inicial',
                                        'ph_final' => 'pH final',
                                        'ph_lodos' => 'pH lodos',
                                        'lodos_generados_porcentaje' => '% lodos',
                                        'lodos_generados_tnk_lodos' => 'Lodos tanque',
                                        'ingreso_filtro_prensa' => 'Ingreso filtro prensa',
                                        'lodos_generados_disposicion' => 'Lodos disposición',
                                        'agua_retorno_proceso_filtro_prensa' => 'Agua retorno',
                                        'cantidad_total_agua_reciclada' => 'Agua reciclada total',
                                    ] as $field => $label)
                                        <div class="col-md-4">
                                            <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                                            <input type="number" step="0.01" name="{{ $field }}" id="{{ $field }}"
                                                   class="form-control"
                                                   value="{{ old($field, $data['params'][0][$field] ?? ($field === 'lodos_generados_tnk_lodos' ? ($data['params'][0]['lodos_generados_tnk'] ?? '') : '')) }}"
                                                   @disabled($isFinished)>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($isIncineracion)
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
                    @else
                        <div class="card mt-3">
                            <div class="card-header">
                                <h3 class="card-title">Cierre por peso</h3>
                            </div>
                            <div class="table-responsive">
                                <table class="table card-table table-vcenter">
                                    <thead>
                                        <tr>
                                            <th>Manifiesto</th>
                                            <th>Perfil</th>
                                            <th>Disponible en proceso</th>
                                            <th>Remanente global</th>
                                            <th>Peso a capturar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($data['details'] as $index => $detail)
                                            <tr>
                                                <td>#{{ $detail['manifest_id'] ?? '—' }}</td>
                                                <td>{{ $detail['profile'] ?? '—' }}</td>
                                                <td>{{ $detail['total_weight'] }}</td>
                                                <td>{{ $detail['remaing_weight'] }}</td>
                                                <td>
                                                    <input type="hidden" name="details[{{ $index }}][id]" value="{{ $detail['id'] }}">
                                                    <input
                                                        type="number"
                                                        step="0.01"
                                                        min="0.01"
                                                        max="{{ $detail['remaing_weight'] }}"
                                                        name="details[{{ $index }}][value]"
                                                        class="form-control"
                                                        value="{{ old('details.'.$index.'.value', $detail['process_weight'] > 0 ? $detail['process_weight'] : $detail['total_weight']) }}"
                                                        @disabled($isFinished || $detail['is_finished'])
                                                        @required(! $isFinished && ! $detail['is_finished'] && $canFinish)
                                                    >
                                                </td>
                                            </tr>
                                        @endforeach
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
                    @endif
                </div>
            </div>
        </form>
    </div>
@endsection
