@php
    $selectedPermissions = collect();
@endphp

<div class="mb-3">
    <label for="role-name" class="form-label required">Nombre</label>
    <div class="input-icon">
        <span class="input-icon-addon"><i class="ti ti-shield-lock"></i></span>
        <input
            type="text"
            name="name"
            id="role-name"
            class="form-control"
            required
        >
    </div>
    <div class="invalid-feedback d-none" data-error-for="name"></div>
</div>

@can(\App\Features\Permissions\Constants\PermissionTypes::ROLES_ASSIGN_PERMISSIONS)
    <div class="mb-0 mt-4 pt-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div>
                <label class="form-label mb-1">
                    <i class="ti ti-key me-1"></i>
                    Permisos
                </label>
            </div>
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

        <div class="invalid-feedback d-none" data-error-for="permissions"></div>
    </div>
@else
    <div class="alert alert-info mb-0">
        <i class="ti ti-info-circle me-1"></i>
        No tienes permiso para asignar permisos a este rol.
    </div>
@endcan
