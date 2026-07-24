@php
    $selectedPermissions = collect(old(
        'permissions',
        isset($role) ? $role->permissions->pluck('name')->all() : []
    ));
@endphp

<div class="mb-3">
    <label for="name" class="form-label required">Nombre</label>
    <input
        type="text"
        name="name"
        id="name"
        class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name', $role->name ?? '') }}"
        required
        autofocus
        @disabled(isset($role) && $role->name === 'Super Administrador')
    >
    @if (isset($role) && $role->name === 'Super Administrador')
        <input type="hidden" name="name" value="{{ $role->name }}">
    @endif
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

@can(\App\Features\Permissions\Constants\PermissionTypes::ROLES_ASSIGN_PERMISSIONS)
    <div class="mb-0">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <label class="form-label mb-0">Permisos</label>
            <div class="btn-list">
                <button type="button" class="btn btn-sm btn-outline-primary" id="select-all-permissions">
                    <i class="ti ti-checks me-1"></i>
                    Agregar todos
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="deselect-all-permissions">
                    <i class="ti ti-x me-1"></i>
                    Quitar todos
                </button>
            </div>
        </div>

        @include('partials.permissions-grid', [
            'groupedPermissions' => $groupedPermissions,
            'moduleLabels' => $moduleLabels,
            'selectedPermissions' => $selectedPermissions,
            'lockedPermissions' => collect(),
            'inputName' => 'permissions[]',
            'checkboxClass' => 'role-permission-checkbox',
        ])

        @error('permissions')
            <div class="text-danger small mt-2">{{ $message }}</div>
        @enderror
    </div>
@else
    <div class="alert alert-info mb-0">
        No tienes permiso para asignar permisos a este rol.
    </div>
@endcan

@push('scripts')
<script>
    document.getElementById('select-all-permissions')?.addEventListener('click', function () {
        document.querySelectorAll('.role-permission-checkbox').forEach((checkbox) => {
            checkbox.checked = true;
        });
    });

    document.getElementById('deselect-all-permissions')?.addEventListener('click', function () {
        document.querySelectorAll('.role-permission-checkbox').forEach((checkbox) => {
            checkbox.checked = false;
        });
    });
</script>
@endpush
