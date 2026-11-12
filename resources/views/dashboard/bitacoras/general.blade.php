@extends('layouts.app')

@section('title', $title)
@section('page-title', $title)

@section('content')
    <div class="container-xl">
        <form id="logbook-form" method="GET" action="{{ route('bitacoras.general') }}">
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-filter me-2"></i>
                        Filtros
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="date_field">Aplicar rango a</label>
                            <select name="date_field" id="date_field" class="form-select">
                                <option value="service_date" @selected($dateField === 'service_date')>Fecha de servicio</option>
                                <option value="collected_at" @selected($dateField === 'collected_at')>Fecha de recolección</option>
                                <option value="warehouse_entry_at" @selected($dateField === 'warehouse_entry_at')>Fecha de entrada al almacén</option>
                            </select>
                        </div>
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
                        <div class="col-md-4">
                            <label class="form-label" for="client_id">Clientes</label>
                            <select name="client_id" id="client_id" class="form-select">
                                <option value="">Todos</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}" @selected((int) $clientId === (int) $client->id)>
                                        {{ $client->company ?: trim($client->name.' '.$client->parentarl_surname) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="status">Estatus</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">Todos</option>
                                @foreach ($statuses as $value => $label)
                                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="manifest_id">Id de manifiesto</label>
                            <input
                                type="number"
                                name="manifest_id"
                                id="manifest_id"
                                class="form-control"
                                min="1"
                                value="{{ $manifestId }}"
                                placeholder="Id de manifiesto"
                            >
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="driver_id">Chofer</label>
                            <select name="driver_id" id="driver_id" class="form-select">
                                <option value="">Todos</option>
                                @foreach ($drivers as $driver)
                                    <option value="{{ $driver->id }}" @selected((int) $driverId === (int) $driver->id)>
                                        {{ $driver->fullName() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="rpbi_profile_id">Perfil</label>
                            <select name="rpbi_profile_id" id="rpbi_profile_id" class="form-select">
                                <option value="">Todos</option>
                                @foreach ($profiles as $profile)
                                    <option value="{{ $profile->id }}" @selected((int) $profileId === (int) $profile->id)>
                                        {{ $profile->code }} — {{ $profile->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-outline-secondary" id="buscar-bitacora">
                        <i class="ti ti-search me-1"></i>
                        Buscar
                    </button>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Bitácora general</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table
                            id="logbook-table"
                            class="table table-vcenter card-table w-100"
                            data-url="{{ route('bitacora-general.index') }}"
                            data-general="1"
                        >
                            <thead>
                                <tr>
                                    <th>Servicio</th>
                                    <th>Detalle</th>
                                    <th>Manifiesto</th>
                                    <th>Código de perfil</th>
                                    <th>Nombre de perfil</th>
                                    <th>Peso</th>
                                    <th>Fecha de servicio</th>
                                    <th>Fecha de recolección</th>
                                    <th>Estado</th>
                                    <th>Ingreso a almacén</th>
                                    <th>Cliente</th>
                                    <th>Chofer</th>
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
