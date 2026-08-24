/**
 * Carga y rellena el modal de usuarios.
 * Usa BanHttp.get para editar y fetch para validar nickname.
 */

import { checkNicknameAvailability } from './services/nicknameAvailability';

let nicknameCheckTimer = null;
let nicknameAvailable = true;
let nicknameCheckToken = 0;

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

function getSubmitButton() {
    return document.querySelector('#user-admin-form button[type="submit"]');
}

function setNicknameFeedback(state, message = '') {
    const input = document.getElementById('user-nickname');
    const feedback = document.getElementById('user-nickname-feedback');
    const hint = document.getElementById('user-nickname-hint');
    const submit = getSubmitButton();

    if (!input || !feedback) {
        return;
    }

    input.classList.remove('is-valid', 'is-invalid');
    feedback.classList.add('d-none');
    feedback.textContent = '';

    if (hint) {
        hint.classList.toggle('d-none', state === 'invalid' || state === 'valid');
    }

    if (state === 'invalid') {
        nicknameAvailable = false;
        input.classList.add('is-invalid');
        feedback.className = 'invalid-feedback d-block';
        feedback.textContent = message || 'Este nickname ya está en uso.';
    } else if (state === 'valid') {
        nicknameAvailable = true;
        input.classList.add('is-valid');
        feedback.className = 'valid-feedback d-block';
        feedback.textContent = message || 'Nickname disponible.';
    } else if (state === 'checking') {
        nicknameAvailable = false;
        feedback.className = 'form-hint';
        feedback.textContent = 'Validando nickname…';
    } else {
        nicknameAvailable = true;
        feedback.className = 'invalid-feedback d-none';
        feedback.textContent = '';
    }

    if (submit) {
        submit.disabled = !nicknameAvailable;
    }
}

function clearNicknameFeedback() {
    setNicknameFeedback('idle');
}

async function validateNicknameInput() {
    const input = document.getElementById('user-nickname');
    const form = document.getElementById('user-admin-form');

    if (!input || !form) {
        return;
    }

    const nickname = input.value.trim();
    const checkUrl = input.dataset.checkUrl || '/users/check-nickname';
    const ignoreUserId = form.dataset.userId || null;
    const token = ++nicknameCheckToken;

    if (nickname === '') {
        clearNicknameFeedback();
        return;
    }

    setNicknameFeedback('checking');

    try {
        const result = await checkNicknameAvailability(nickname, ignoreUserId, checkUrl);

        if (token !== nicknameCheckToken) {
            return;
        }

        setNicknameFeedback(
            result.available ? 'valid' : 'invalid',
            result.message,
        );

        if (result.nickname && result.nickname !== nickname) {
            input.value = result.nickname;
        }
    } catch (error) {
        if (token !== nicknameCheckToken) {
            return;
        }

        setNicknameFeedback('invalid', error.message || 'No se pudo validar el nickname.');
    }
}

function scheduleNicknameCheck() {
    clearTimeout(nicknameCheckTimer);
    nicknameCheckTimer = setTimeout(() => {
        validateNicknameInput();
    }, 350);
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
    delete form.dataset.userId;
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

    clearNicknameFeedback();
    document.dispatchEvent(new Event('permissions:refresh-groups'));
}

function fillUserForm(data) {
    const form = document.getElementById('user-admin-form');
    if (!form) {
        return;
    }

    resetUserForm();

    form.action = data.update_url;
    form.dataset.userId = String(data.id ?? '');

    let methodInput = form.querySelector('[name="_method"]');
    if (!methodInput) {
        methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        form.appendChild(methodInput);
    }
    methodInput.value = 'PUT';

    form.querySelector('[name="name"]').value = data.name ?? '';
    form.querySelector('[name="nickname"]').value = data.nickname ?? '';
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
    clearNicknameFeedback();
}

async function loadUserIntoModal(userId, editUrl) {
    if (!window.BanHttp) {
        return;
    }

    const url = editUrl || `/users/${userId}/edit`;
    const data = await window.BanHttp.get(url, 'Cargando Usuario');
    fillUserForm(data);
}

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('user-form-modal');
    const form = document.getElementById('user-admin-form');
    const nicknameInput = document.getElementById('user-nickname');

    if (!modal || !form) {
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

    nicknameInput?.addEventListener('input', scheduleNicknameCheck);
    nicknameInput?.addEventListener('blur', () => {
        clearTimeout(nicknameCheckTimer);
        validateNicknameInput();
    });

    form.addEventListener('submit', (event) => {
        if (!nicknameAvailable) {
            event.preventDefault();
            setNicknameFeedback('invalid', 'Corrige el nickname antes de guardar.');
            nicknameInput?.focus();
        }
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
