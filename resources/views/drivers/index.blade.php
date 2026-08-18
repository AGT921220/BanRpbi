@extends('layouts.app')

@section('title', 'Choferes')
@section('page-title', 'Choferes')

@section('page-actions')
    @can(\App\Features\Permissions\Constants\PermissionTypes::DRIVERS_CREATE)
        <a href="{{ route('drivers.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-2"></i>
            Nuevo chofer
        </a>
    @endcan
@endsection

@section('content')
    <div class="container-xl">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-steering-wheel me-2"></i>
                    Listado de choferes
                </h3>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table
                        id="drivers-table"
                        class="table table-vcenter card-table w-100"
                        data-url="{{ route('driver-headers.index') }}"
                    >
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Usuario</th>
                                <th>Teléfono</th>
                                <th>Zona</th>
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
    @vite('resources/js/modules/drivers/index.js')
@endpush
