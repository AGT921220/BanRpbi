@php
    $selectedPermissions = collect(old(
        'permissions',
        isset($role) ? $role->permissions->pluck('name')->all() : []
    ));
@endphp

@if (isset($role) && $role->name === 'Super Administrador')
    <x-form.input
        name="name_display"
        label="Nombre"
        icon="ti ti-shield-lock"
        :value="$role->name"
        disabled
    />
    <input type="hidden" name="name" value="{{ $role->name }}">
@else
    <x-form.input
        name="name"
        label="Nombre"
        icon="ti ti-shield-lock"
        :value="$role->name ?? ''"
        required
        autofocus
    />
@endif

@can(\App\Features\Permissions\Constants\PermissionTypes::ROLES_ASSIGN_PERMISSIONS)
    <div class="mb-0">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <label class="form-label mb-0">
                <i class="ti ti-key me-1"></i>
                Permisos
            </label>
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
        <i class="ti ti-info-circle me-1"></i>
        No tienes permiso para asignar permisos a este rol.
    </div>
@endcan

@push('scripts')
<script>
    document.getElementById('select-all-permissions')?.addEventListener('click', function () {
        document.querySelectorAll('.role-permission-checkbox').forEach((checkbox) => {
            checkbox.checked = true;
        });
        document.dispatchEvent(new Event('permissions:refresh-groups'));
    });

    document.getElementById('deselect-all-permissions')?.addEventListener('click', function () {
        document.querySelectorAll('.role-permission-checkbox').forEach((checkbox) => {
            checkbox.checked = false;
        });
        document.dispatchEvent(new Event('permissions:refresh-groups'));
    });
</script>
@endpush
