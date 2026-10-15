@extends('layouts.app')

@section('title', 'Generar proceso')
@section('page-title', 'Generar proceso')

@section('content')
    <div class="container-xl">
        <form method="POST" action="{{ route('procesos.store') }}" id="process-create-form">
            @csrf

            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-flask me-2"></i>
                                Proceso
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label required" for="waste_process_type_id">Tipo de proceso</label>
                                <select
                                    name="waste_process_type_id"
                                    id="waste_process_type_id"
                                    class="form-select @error('waste_process_type_id') is-invalid @enderror"
                                    required
                                >
                                    <option value="">Selecciona el tipo</option>
                                    @foreach ($processos as $process)
                                        <option
                                            value="{{ $process->id }}"
                                            @selected((string) old('waste_process_type_id') === (string) $process->id)
                                        >
                                            {{ $process->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('waste_process_type_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-0">
                                <label class="form-label required" for="date_start">Fecha de inicio</label>
                                <input
                                    type="date"
                                    name="date_start"
                                    id="date_start"
                                    class="form-control @error('date_start') is-invalid @enderror"
                                    value="{{ old('date_start', $fechaProcess) }}"
                                    required
                                >
                                @error('date_start')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-file-certificate me-2"></i>
                                Manifiestos
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="text-secondary text-center py-4" id="manifests-placeholder">
                                Selecciona el tipo de proceso para ver los manifiestos que puedes incluir.
                            </div>

                            <div id="process-manifests" class="d-none">
                                @if ($manifests->isEmpty())
                                    <div class="text-secondary text-center py-4">
                                        No hay manifiestos disponibles.
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-vcenter card-table">
                                            <thead>
                                                <tr>
                                                    <th class="w-1">
                                                        <input
                                                            type="checkbox"
                                                            class="form-check-input"
                                                            id="select-all-manifests"
                                                            aria-label="Seleccionar todos"
                                                        >
                                                    </th>
                                                    <th>N° manifiesto</th>
                                                    <th>Cliente</th>
                                                    <th>Fecha</th>
                                                    <th>Perfiles</th>
                                                    <th class="text-end">Peso</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($manifests as $manifest)
                                                    <tr>
                                                        <td>
                                                            <input
                                                                type="checkbox"
                                                                class="form-check-input manifest-check"
                                                                name="manifest_ids[]"
                                                                value="{{ $manifest['id'] }}"
                                                                data-label="{{ $manifest['id'] }} · {{ $manifest['client'] }}"
                                                                @checked(in_array($manifest['id'], array_map('intval', old('manifest_ids', [])), true))
                                                                aria-label="Seleccionar manifiesto {{ $manifest['id'] }}"
                                                            >
                                                        </td>
                                                        <td>{{ $manifest['id'] }}</td>
                                                        <td>{{ $manifest['client'] }}</td>
                                                        <td>{{ $manifest['date'] }}</td>
                                                        <td>{{ $manifest['profiles'] !== '' ? $manifest['profiles'] : '—' }}</td>
                                                        <td class="text-end">{{ $manifest['weight'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif

                                @error('manifest_ids')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-end">
                            <button type="button" class="btn btn-primary" id="open-process-confirm">
                                <i class="ti ti-plus me-1"></i>
                                Crear proceso
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="modal modal-blur fade" id="process-confirm-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Manifiestos seleccionados</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <ul class="mb-0" id="selected-manifests"></ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" form="process-create-form">Crear</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/modules/procesos/create.js')
@endpush
