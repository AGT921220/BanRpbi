@php
    $selectedRoles = collect(old('roles', isset($selectedRoles) ? $selectedRoles->all() : []));
    $selectedPermissions = collect(old(
        'permissions',
        isset($directPermissions) ? $directPermissions->merge($lockedPermissions ?? [])->unique()->all() : []
    ));
    $initialLocked = collect(old('roles')
        ? []
        : (isset($lockedPermissions) ? $lockedPermissions->all() : [])
    );
@endphp

<x-form.input
    name="name"
    label="Nombre"
    icon="ti ti-user"
    :value="$user->name ?? ''"
    required
    autofocus
/>

<x-form.input
    name="email"
    label="Correo electrónico"
    type="email"
    icon="ti ti-mail"
    :value="$user->email ?? ''"
    required
    autocomplete="email"
/>

<x-form.input
    name="password"
    label="Contraseña"
    type="password"
    icon="ti ti-lock"
    :required="! isset($user)"
    autocomplete="new-password"
    :help="isset($user) ? '(dejar vacío para no cambiar)' : null"
/>

<x-form.input
    name="password_confirmation"
    label="Confirmar contraseña"
    type="password"
    icon="ti ti-lock-check"
    :required="! isset($user)"
    autocomplete="new-password"
/>

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
                        @checked($selectedRoles->contains($role->name))
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
    @error('roles')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

<div class="mb-0">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
        <div>
            <label class="form-label mb-0">
                <i class="ti ti-key me-1"></i>
                Permisos
            </label>
            <div class="text-secondary small">
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

    @error('permissions')
        <div class="text-danger small mt-2">{{ $message }}</div>
    @enderror
</div>

@push('scripts')
<script>
    (function () {
        const rolesPermissionsMap = @json($rolesPermissionsMap);
        const roleCheckboxes = document.querySelectorAll('.user-role-checkbox');
        const permissionCheckboxes = document.querySelectorAll('.user-permission-checkbox');

        function getSelectedRolePermissions() {
            const locked = new Set();

            roleCheckboxes.forEach((checkbox) => {
                if (!checkbox.checked) {
                    return;
                }

                (rolesPermissionsMap[checkbox.value] || []).forEach((permission) => {
                    locked.add(permission);
                });
            });

            return locked;
        }

        function refreshPermissionLocks() {
            const lockedPermissions = getSelectedRolePermissions();

            permissionCheckboxes.forEach((checkbox) => {
                const permission = checkbox.dataset.permission;
                const isLocked = lockedPermissions.has(permission);
                const label = checkbox.closest('label');
                let badge = label?.querySelector('.badge-role-lock');

                if (isLocked) {
                    checkbox.checked = true;
                    checkbox.disabled = true;
                    label?.classList.add('text-secondary');

                    if (label && !badge) {
                        badge = document.createElement('span');
                        badge.className = 'badge bg-secondary-lt ms-1 badge-role-lock';
                        badge.textContent = 'Rol';
                        label.querySelector('.form-check-label')?.appendChild(badge);
                    }
                } else {
                    checkbox.disabled = false;
                    label?.classList.remove('text-secondary');
                    badge?.remove();
                }
            });

            document.dispatchEvent(new Event('permissions:refresh-groups'));
        }

        roleCheckboxes.forEach((checkbox) => {
            checkbox.addEventListener('change', refreshPermissionLocks);
        });

        document.getElementById('select-all-user-permissions')?.addEventListener('click', function () {
            permissionCheckboxes.forEach((checkbox) => {
                if (!checkbox.disabled) {
                    checkbox.checked = true;
                }
            });
            document.dispatchEvent(new Event('permissions:refresh-groups'));
        });

        document.getElementById('deselect-all-user-permissions')?.addEventListener('click', function () {
            permissionCheckboxes.forEach((checkbox) => {
                if (!checkbox.disabled) {
                    checkbox.checked = false;
                }
            });
            document.dispatchEvent(new Event('permissions:refresh-groups'));
        });

        refreshPermissionLocks();
    })();
</script>
@endpush
