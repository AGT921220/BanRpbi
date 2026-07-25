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

            <div class="card-body border-bottom py-3">
                <form method="GET" action="{{ route('clients.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-8 col-lg-6">
                        <label class="form-label" for="clients-search">Buscar</label>
                        <div class="input-icon">
                            <span class="input-icon-addon">
                                <i class="ti ti-search"></i>
                            </span>
                            <input
                                type="search"
                                name="search"
                                id="clients-search"
                                class="form-control"
                                value="{{ request('search') }}"
                                placeholder="Nombre, apellido, correo, teléfono o empresa"
                            >
                        </div>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-search me-1"></i>
                            Buscar
                        </button>
                    </div>
                    @if (request()->filled('search'))
                        <div class="col-auto">
                            <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">
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
                            <th>Nombre</th>
                            <th>Apellido paterno</th>
                            <th>Correo</th>
                            <th>Teléfono</th>
                            <th>Empresa</th>
                            <th class="w-1">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($clients as $client)
                            <tr>
                                <td>{{ $client->name }}</td>
                                <td>{{ $client->parentarl_surname }}</td>
                                <td class="text-secondary">{{ $client->email }}</td>
                                <td>{{ $client->phone }}</td>
                                <td>{{ $client->company }}</td>
                                <td>
                                    <div class="btn-list flex-nowrap">
                                        @can(\App\Features\Permissions\Constants\PermissionTypes::CLIENTS_UPDATE)
                                            <a
                                                href="{{ route('clients.edit', $client) }}"
                                                class="btn btn-sm btn-outline-primary"
                                            >
                                                <i class="ti ti-pencil me-1"></i>
                                                Editar
                                            </a>
                                        @endcan

                                        @can(\App\Features\Permissions\Constants\PermissionTypes::CLIENTS_DELETE)
                                            <form
                                                action="{{ route('clients.destroy', $client) }}"
                                                method="POST"
                                                onsubmit="return confirm('¿Eliminar este cliente?')"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="ti ti-trash me-1"></i>
                                                    Eliminar
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">
                                    No hay clientes registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($clients->hasPages())
                <div class="card-footer d-flex align-items-center">
                    {{ $clients->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
