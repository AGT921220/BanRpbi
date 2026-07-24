@extends('layouts.app')

@section('title', 'Roles')
@section('page-title', 'Roles')

@section('page-actions')
    @can(\App\Features\Permissions\Constants\PermissionTypes::ROLES_CREATE)
        <a href="{{ route('roles.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-2"></i>
            Nuevo rol
        </a>
    @endcan
@endsection

@section('content')
    <div class="container-xl">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Listado de roles</h3>
            </div>

            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Permisos</th>
                            <th>Usuarios</th>
                            <th class="w-1">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roles as $role)
                            <tr>
                                <td>
                                    <strong>{{ $role->name }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-azure-lt">{{ $role->permissions->count() }} permisos</span>
                                </td>
                                <td>
                                    <span class="text-secondary">{{ $role->users_count }}</span>
                                </td>
                                <td>
                                    <div class="btn-list flex-nowrap">
                                        @can(\App\Features\Permissions\Constants\PermissionTypes::ROLES_UPDATE)
                                            <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-outline-primary">
                                                Editar
                                            </a>
                                        @endcan

                                        @can(\App\Features\Permissions\Constants\PermissionTypes::ROLES_DELETE)
                                            <form
                                                action="{{ route('roles.destroy', $role) }}"
                                                method="POST"
                                                onsubmit="return confirm('¿Eliminar este rol?')"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    @disabled($role->name === 'Super Administrador')
                                                >
                                                    Eliminar
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-secondary py-4">
                                    No hay roles registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($roles->hasPages())
                <div class="card-footer d-flex align-items-center">
                    {{ $roles->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
