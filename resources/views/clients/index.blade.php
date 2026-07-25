@extends('layouts.app')

@section('title', 'Clientes')
@section('page-title', 'Clientes')

@section('page-actions')
    @can(\App\Features\Permissions\Constants\PermissionTypes::CLIENTS_CREATE)
        <a href="{{ route('clients.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-2"></i>
            Nuevo cliente
        </a>
    @endcan
@endsection

@section('content')
    <div class="container-xl">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-users me-2"></i>
                    Listado de clientes
                </h3>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table
                        id="clients-table"
                        class="table table-vcenter card-table w-100"
                        data-url="{{ route('clients.index') }}"
                    >
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Teléfono</th>
                                <th>Empresa</th>
                                <th>Creado</th>
                                <th class="w-1">Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/modules/clients/index.js')
@endpush
