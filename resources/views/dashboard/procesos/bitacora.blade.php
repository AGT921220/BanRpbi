@extends('layouts.app')

@section('title', $title)
@section('page-title', $title)

@section('content')
    <div class="container-xl">
        <form id="logbook-form" method="GET" action="{{ $general ? route('procesos.bitacoras.general') : route('procesos.bitacoras.tratamientos') }}">
            <input type="hidden" name="filtro" value="{{ $filtro }}">

            @unless ($general)
            <div class="btn-list mb-3">
                <a
                    href="{{ route('procesos.bitacoras.tratamientos', array_filter(['filtro' => 'incineracion', 'date_from' => $dateFrom, 'date_to' => $dateTo])) }}"
                    class="btn {{ $filtro === 'incineracion' ? 'btn-primary' : 'btn-outline-secondary' }}"
                    data-logbook-filter="incineracion"
                >
                    Sólo incineración
                </a>
                <a
                    href="{{ route('procesos.bitacoras.tratamientos', array_filter(['filtro' => 'esterilizacion', 'date_from' => $dateFrom, 'date_to' => $dateTo])) }}"
                    class="btn {{ $filtro === 'esterilizacion' ? 'btn-primary' : 'btn-outline-secondary' }}"
                    data-logbook-filter="esterilizacion"
                >
                    Sólo esterilización
                </a>
                <a
                    href="{{ route('procesos.bitacoras.tratamientos', array_filter(['filtro' => 'ambas', 'date_from' => $dateFrom, 'date_to' => $dateTo])) }}"
                    class="btn {{ $filtro === 'ambas' ? 'btn-primary' : 'btn-outline-secondary' }}"
                    data-logbook-filter="ambas"
                >
                    Ambas
                </a>
            </div>
            @endunless

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
                    <button type="submit" class="btn btn-outline-secondary" id="buscar-bitacora">
                        <i class="ti ti-search me-1"></i>
                        Buscar
                    </button>
                    @unless ($general)
                        <button type="button" class="btn btn-primary" id="generar-bitacora">
                            <i class="ti ti-notebook me-1"></i>
                            Generar bitácora
                        </button>
                    @endunless
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Tratamientos</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table
                            id="logbook-table"
                            class="table table-vcenter card-table w-100"
                            data-url="{{ $general ? route('bitacora-general.index') : route('logbook-headers.index') }}"
                            data-general="{{ $general ? '1' : '0' }}"
                        >
                            <thead>
                                <tr>
                                    @unless ($general)
                                        <th class="w-1">Sel.</th>
                                    @endunless
                                    <th>#</th>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Estado</th>
                                    <th>Manifiestos</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/modules/procesos/logbook.js')
@endpush
