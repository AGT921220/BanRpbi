@php
    $selectedRoles = collect();
    $selectedPermissions = collect();
    $initialLocked = collect();
@endphp

<div class="mb-3">
    <label for="user-name" class="form-label required">Nombre</label>
    <div class="input-icon">
        <span class="input-icon-addon"><i class="ti ti-user"></i></span>
        <input
            type="text"
            name="name"
            id="user-name"
            class="form-control"
            required
        >
    </div>
    <div class="invalid-feedback d-none" data-error-for="name"></div>
</div>

<div class="mb-3">
    <label for="user-email" class="form-label required">Correo electrónico</label>
    <div class="input-icon">
        <span class="input-icon-addon"><i class="ti ti-mail"></i></span>
        <input
            type="email"
            name="email"
            id="user-email"
            class="form-control"
            required
            autocomplete="email"
        >
    </div>
    <div class="invalid-feedback d-none" data-error-for="email"></div>
</div>

<div class="mb-3">
    <label for="user-password-input" class="form-label required">
        Contraseña
        <span id="user-password-help" class="text-secondary d-none">(dejar vacío para no cambiar)</span>
    </label>
    <div class="input-icon">
        <span class="input-icon-addon"><i class="ti ti-lock"></i></span>
        <input
            type="password"
            name="password"
            id="user-password-input"
            class="form-control"
            required
            autocomplete="new-password"
        >
    </div>
    <div class="invalid-feedback d-none" data-error-for="password"></div>
</div>

<div class="mb-3">
    <label for="user-password-confirmation-input" class="form-label required">Confirmar contraseña</label>
    <div class="input-icon">
        <span class="input-icon-addon"><i class="ti ti-lock-check"></i></span>
        <input
            type="password"
            name="password_confirmation"
            id="user-password-confirmation-input"
            class="form-control"
            required
            autocomplete="new-password"
        >
    </div>
</div>

<div class="mb-4">
    <label class="form-label">
        <i class="ti ti-shield me-1"></i>
        Roles
    </label>
    <div class="row g-2">
        @forelse ($roles as $role)
            <div class="col-md-6">
                <label class="form-check">
                    <input
                        class="form-check-input user-role-checkbox"
                        type="checkbox"
                        name="roles[]"
                        value="{{ $role->name }}"
                        data-role="{{ $role->name }}"
                    >
                    <span class="form-check-label">
                        <i class="ti ti-user-shield me-1 text-secondary"></i>
                        {{ $role->name }}
                    </span>
                </label>
            </div>
        @empty
            <div class="col-12">
                <p class="text-secondary mb-0">No hay roles disponibles.</p>
            </div>
        @endforelse
    </div>
    <div class="invalid-feedback d-none" data-error-for="roles"></div>
</div>

<div class="mb-0 mt-4 pt-3">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <label class="form-label mb-1">
                <i class="ti ti-key me-1"></i>
                Permisos
            </label>
            <div class="text-secondary small mb-0">
                Los permisos heredados de un rol aparecen marcados y no se pueden desactivar.
            </div>
        </div>
        <div class="btn-list">
            <button type="button" class="btn btn-sm btn-outline-primary" id="select-all-user-permissions">
                <i class="ti ti-checks me-1"></i>
                Agregar todos
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselect-all-user-permissions">
                <i class="ti ti-x me-1"></i>
                Quitar todos
            </button>
        </div>
    </div>

    @include('partials.permissions-grid', [
        'groupedPermissions' => $groupedPermissions,
        'moduleLabels' => $moduleLabels,
        'selectedPermissions' => $selectedPermissions,
        'lockedPermissions' => $initialLocked,
        'inputName' => 'permissions[]',
        'checkboxClass' => 'user-permission-checkbox',
    ])

    <div class="invalid-feedback d-none" data-error-for="permissions"></div>
</div>
