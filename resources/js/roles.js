/**
 * Carga y rellena el modal de roles.
 * Usa BanHttp.get.
 */

function resetRoleForm() {
    const form = document.getElementById('role-admin-form');
    if (!form) {
        return;
    }

    form.reset();
    form.action = form.dataset.storeUrl;
    form.querySelector('[name="_method"]')?.remove();

    const nameInput = form.querySelector('[name="name"]');
    if (nameInput) {
        nameInput.disabled = false;
    }

    form.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
        checkbox.checked = false;
        checkbox.disabled = false;
    });

    document.getElementById('role-modal-title').innerHTML = '<i class="ti ti-shield-plus me-2"></i> Nuevo rol';
    document.dispatchEvent(new Event('permissions:refresh-groups'));
}

function fillRoleForm(data) {
    const form = document.getElementById('role-admin-form');
    if (!form) {
        return;
    }

    resetRoleForm();

    form.action = data.update_url;

    let methodInput = form.querySelector('[name="_method"]');
    if (!methodInput) {
        methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        form.appendChild(methodInput);
    }
    methodInput.value = 'PUT';

    const nameInput = form.querySelector('[name="name"]');
    nameInput.value = data.name ?? '';

    if (data.name === 'Super Administrador') {
        nameInput.disabled = true;
    }

    (data.permissions || []).forEach((permissionName) => {
        const checkbox = form.querySelector(`.role-permission-checkbox[value="${CSS.escape(permissionName)}"]`);
        if (checkbox) {
            checkbox.checked = true;
        }
    });

    document.getElementById('role-modal-title').innerHTML = '<i class="ti ti-shield-cog me-2"></i> Editar rol';
    document.dispatchEvent(new Event('permissions:refresh-groups'));
}

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('role-form-modal');
    if (!modal) {
        return;
    }

    modal.addEventListener('show.bs.modal', async (event) => {
        const trigger = event.relatedTarget;
        const roleId = trigger?.getAttribute?.('data-role-id');
        const editUrl = trigger?.getAttribute?.('data-edit-url');

        if (roleId || editUrl) {
            try {
                const url = editUrl || `/roles/${roleId}/edit`;
                const data = await window.BanHttp.get(url);
                fillRoleForm(data);
            } catch (error) {
                console.error(error);
                resetRoleForm();
            }
            return;
        }

        resetRoleForm();
    });

    document.getElementById('select-all-permissions')?.addEventListener('click', () => {
        document.querySelectorAll('#role-form-modal .role-permission-checkbox').forEach((checkbox) => {
            checkbox.checked = true;
        });
        document.dispatchEvent(new Event('permissions:refresh-groups'));
    });

    document.getElementById('deselect-all-permissions')?.addEventListener('click', () => {
        document.querySelectorAll('#role-form-modal .role-permission-checkbox').forEach((checkbox) => {
            checkbox.checked = false;
        });
        document.dispatchEvent(new Event('permissions:refresh-groups'));
    });
});
