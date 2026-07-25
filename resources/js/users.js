/**
 * Carga y rellena el modal de usuarios.
 * Usa BanHttp.get / BanHttp.post.
 */

import { showToast } from "./admin";

function getRolesPermissionsMap() {
    const node = document.getElementById('users-roles-permissions-map');
    if (!node) {
        return {};
    }

    try {
        return JSON.parse(node.textContent);
    } catch (error) {
        return {};
    }
}

function refreshPermissionLocks() {
    const map = getRolesPermissionsMap();
    const locked = new Set();

    document.querySelectorAll('#user-form-modal .user-role-checkbox:checked').forEach((checkbox) => {
        (map[checkbox.value] || []).forEach((permission) => locked.add(permission));
    });

    document.querySelectorAll('#user-form-modal .user-permission-checkbox').forEach((checkbox) => {
        const isLocked = locked.has(checkbox.dataset.permission);
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

function resetUserForm() {
    const form = document.getElementById('user-admin-form');
    if (!form) {
        return;
    }

    form.reset();
    form.action = form.dataset.storeUrl;
    form.querySelector('[name="_method"]')?.remove();

    form.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
        checkbox.checked = false;
        checkbox.disabled = false;
    });

    form.querySelectorAll('.badge-role-lock').forEach((badge) => badge.remove());

    document.getElementById('user-modal-title').innerHTML = '<i class="ti ti-user-plus me-2"></i> Nuevo usuario';
    document.getElementById('user-password-help')?.classList.add('d-none');
    document.getElementById('user-password-input')?.setAttribute('required', 'required');
    document.getElementById('user-password-confirmation-input')?.setAttribute('required', 'required');

    document.dispatchEvent(new Event('permissions:refresh-groups'));
}

function fillUserForm(data) {
    const form = document.getElementById('user-admin-form');
    if (!form) {
        return;
    }

    resetUserForm();

    form.action = data.update_url;

    let methodInput = form.querySelector('[name="_method"]');
    if (!methodInput) {
        methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        form.appendChild(methodInput);
    }
    methodInput.value = 'PUT';

    form.querySelector('[name="name"]').value = data.name ?? '';
    form.querySelector('[name="email"]').value = data.email ?? '';

    document.getElementById('user-modal-title').innerHTML = '<i class="ti ti-user-edit me-2"></i> Editar usuario';
    document.getElementById('user-password-help')?.classList.remove('d-none');
    document.getElementById('user-password-input')?.removeAttribute('required');
    document.getElementById('user-password-confirmation-input')?.removeAttribute('required');

    (data.roles || []).forEach((roleName) => {
        const checkbox = form.querySelector(`.user-role-checkbox[value="${CSS.escape(roleName)}"]`);
        if (checkbox) {
            checkbox.checked = true;
        }
    });

    const selected = new Set([
        ...(data.direct_permissions || []),
        ...(data.locked_permissions || []),
    ]);

    form.querySelectorAll('.user-permission-checkbox').forEach((checkbox) => {
        checkbox.checked = selected.has(checkbox.value);
    });

    refreshPermissionLocks();
}

async function loadUserIntoModal(userId, editUrl) {
    if (!window.BanHttp) {
        return;
    }

    const url = editUrl || `/users/${userId}/edit`;
    const data = await window.BanHttp.get(url,'Cargando Usuarios');
    fillUserForm(data);
}

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('user-form-modal');
    if (!modal) {
        return;
    }

    modal.addEventListener('show.bs.modal', async (event) => {
        const trigger = event.relatedTarget;
        const userId = trigger?.getAttribute?.('data-user-id');
        const editUrl = trigger?.getAttribute?.('data-edit-url');

        if (userId || editUrl) {
            try {
                await loadUserIntoModal(userId, editUrl);
            } catch (error) {
                console.error(error);
                resetUserForm();
            }
            return;
        }

        resetUserForm();
    });

    document.querySelectorAll('#user-form-modal .user-role-checkbox').forEach((checkbox) => {
        checkbox.addEventListener('change', refreshPermissionLocks);
    });

    document.getElementById('select-all-user-permissions')?.addEventListener('click', () => {
        document.querySelectorAll('#user-form-modal .user-permission-checkbox').forEach((checkbox) => {
            if (!checkbox.disabled) {
                checkbox.checked = true;
            }
        });
        document.dispatchEvent(new Event('permissions:refresh-groups'));
    });

    document.getElementById('deselect-all-user-permissions')?.addEventListener('click', () => {
        document.querySelectorAll('#user-form-modal .user-permission-checkbox').forEach((checkbox) => {
            if (!checkbox.disabled) {
                checkbox.checked = false;
            }
        });
        document.dispatchEvent(new Event('permissions:refresh-groups'));
    });
});
