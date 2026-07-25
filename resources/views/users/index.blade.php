@extends('layouts.app')

@section('title', 'Usuarios')
@section('page-title', 'Usuarios')

@section('page-actions')
    @can(\App\Features\Permissions\Constants\PermissionTypes::USERS_CREATE)
        <button
            type="button"
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#user-form-modal"
        >
            <i class="ti ti-plus me-2"></i>
            Nuevo usuario
        </button>
    @endcan
@endsection

@section('content')
    <div class="container-xl">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-users me-2"></i>
                    Listado de usuarios
                </h3>
            </div>

            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Roles</th>
                            <th class="w-1">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td class="text-secondary">{{ $user->email }}</td>
                                <td>
                                    @forelse ($user->roles as $role)
                                        <span class="badge bg-blue-lt">{{ $role->name }}</span>
                                    @empty
                                        <span class="text-secondary">Sin rol</span>
                                    @endforelse
                                    @if ($user->permissions->isNotEmpty())
                                        <div class="mt-1">
                                            <span class="badge bg-green-lt">
                                                +{{ $user->permissions->count() }} permiso(s) directo(s)
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-list flex-nowrap">
                                        @can(\App\Features\Permissions\Constants\PermissionTypes::USERS_UPDATE)
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#user-form-modal"
                                                data-user-id="{{ $user->id }}"
                                                data-edit-url="{{ route('users.edit', $user) }}"
                                            >
                                                <i class="ti ti-pencil me-1"></i>
                                                Editar
                                            </button>
                                        @endcan

                                        @can(\App\Features\Permissions\Constants\PermissionTypes::USERS_DELETE)
                                            <form
                                                action="{{ route('users.destroy', $user) }}"
                                                method="POST"
                                                onsubmit="return confirm('¿Eliminar este usuario?')"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    @disabled(auth()->id() === $user->id)
                                                >
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
                                <td colspan="4" class="text-center text-secondary py-4">
                                    No hay usuarios registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="card-footer d-flex align-items-center">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>

    @canany([
        \App\Features\Permissions\Constants\PermissionTypes::USERS_CREATE,
        \App\Features\Permissions\Constants\PermissionTypes::USERS_UPDATE,
    ])
        @include('users._modal')

        <script type="application/json" id="users-roles-permissions-map">
            @json($rolesPermissionsMap)
        </script>
    @endcanany
@endsection
