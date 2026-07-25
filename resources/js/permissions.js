/**
 * Acordeón de grupos de permisos (sin Bootstrap Collapse).
 * Toggle simple por display para no trabar modales.
 */

function updateGroupState(groupName, root = document) {
    const items = root.querySelectorAll(`.permission-item[data-group="${groupName}"]`);
    const toggle = root.querySelector(`.permission-group-toggle[data-group="${groupName}"]`);
    const counter = root.querySelector(`.permission-group-count[data-group-count="${groupName}"]`);

    if (!items.length || !toggle) {
        return;
    }

    const enabledItems = Array.from(items).filter((item) => !item.disabled);
    const checkedItems = Array.from(items).filter((item) => item.checked);
    const checkedEnabled = enabledItems.filter((item) => item.checked);

    if (counter) {
        counter.textContent = `${checkedItems.length} / ${items.length}`;
    }

    if (enabledItems.length === 0) {
        toggle.checked = checkedItems.length === items.length;
        toggle.indeterminate = false;
        toggle.disabled = true;
        return;
    }

    toggle.disabled = false;
    toggle.checked = checkedEnabled.length === enabledItems.length;
    toggle.indeterminate = checkedEnabled.length > 0 && checkedEnabled.length < enabledItems.length;
}

function setGroupOpen(collapseEl, open) {
    const group = collapseEl.closest('.permission-group');
    const chevron = group?.querySelector('.permission-group-chevron');
    const button = group?.querySelector('.permission-group-toggle-btn');

    collapseEl.classList.toggle('is-open', open);
    collapseEl.hidden = !open;
    collapseEl.style.display = open ? 'block' : 'none';

    if (chevron) {
        chevron.classList.toggle('ti-chevron-down', open);
        chevron.classList.toggle('ti-chevron-right', !open);
    }

    if (button) {
        button.classList.toggle('collapsed', !open);
        button.setAttribute('aria-expanded', open ? 'true' : 'false');
    }
}

function refreshAllGroups(root = document) {
    root.querySelectorAll('.permission-group-toggle').forEach((toggle) => {
        updateGroupState(toggle.dataset.group, root);
    });
}

function setAllGroups(expanded, root = document) {
    root.querySelectorAll('.permission-group-collapse').forEach((collapseEl) => {
        setGroupOpen(collapseEl, expanded);
    });
}

document.addEventListener('change', (event) => {
    const target = event.target;

    if (!(target instanceof HTMLElement)) {
        return;
    }

    const panel = target.closest('.permissions-panel') || document;

    if (target.classList.contains('permission-group-toggle')) {
        const groupName = target.dataset.group;
        panel
            .querySelectorAll(`.permission-item[data-group="${groupName}"]`)
            .forEach((item) => {
                if (!item.disabled) {
                    item.checked = target.checked;
                }
            });
        updateGroupState(groupName, panel);
        return;
    }

    if (target.classList.contains('permission-item')) {
        updateGroupState(target.dataset.group, panel);
    }
});

document.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof Element)) {
        return;
    }

    const groupBtn = target.closest('.permission-group-toggle-btn');
    if (groupBtn) {
        event.preventDefault();
        event.stopPropagation();

        const panel = groupBtn.closest('.permissions-panel') || document;
        const collapseEl = panel.querySelector(groupBtn.getAttribute('data-target'));

        if (collapseEl) {
            setGroupOpen(collapseEl, !collapseEl.classList.contains('is-open'));
        }
        return;
    }

    if (target.closest('.btn-expand-all-groups')) {
        event.preventDefault();
        const panel = target.closest('.permissions-panel') || document;
        setAllGroups(true, panel);
        return;
    }

    if (target.closest('.btn-collapse-all-groups')) {
        event.preventDefault();
        const panel = target.closest('.permissions-panel') || document;
        setAllGroups(false, panel);
    }
});

document.addEventListener('permissions:refresh-groups', () => {
    refreshAllGroups();
});

document.addEventListener('DOMContentLoaded', () => {
    refreshAllGroups();
});
