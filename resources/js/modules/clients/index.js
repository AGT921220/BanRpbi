import { Modal } from 'bootstrap';
import { createDataTable } from '../../shared/datatable.js';
import { showToast } from '../../admin.js';

const STATUS_LABELS = {
    configuration_pending: 'Pendiente de configuración',
    pending_approval: 'Pendiente de aprobación',
    approved: 'Aprobado',
    rejected: 'Rechazado',
};

const TOTAL_STEPS = 4;

document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content') ?? '';

    const table = createDataTable('clients-table', [
        { data: 'full_name', name: 'full_name' },
        { data: 'email', name: 'email' },
        { data: 'phone', name: 'phone' },
        { data: 'company', name: 'company' },
        { data: 'created_at', name: 'created_at' },
        {
            data: null,
            name: 'actions',
            orderable: false,
            searchable: false,
            className: 'text-end',
            render: (_data, _type, row) => {
                let actions = '';

                if (row.can_configure) {
                    const label = row.has_active_contract
                        ? 'Actualizar contrato'
                        : 'Asignar contrato';
                    const icon = row.has_active_contract
                        ? 'ti ti-file-pencil'
                        : 'ti ti-file-plus';

                    actions += `
                        <button
                            type="button"
                            class="btn btn-sm btn-success configure-client-btn"
                            data-client-id="${row.id}"
                        >
                            <i class="${icon} me-1"></i>
                            ${label}
                        </button>
                    `;
                }

                if (row.can_update) {
                    actions += `
                        <a
                            href="/clients/${row.id}/edit"
                            class="btn btn-sm btn-outline-primary"
                        >
                            <i class="ti ti-pencil me-1"></i>
                            Editar
                        </a>
                    `;
                }

                if (row.can_delete) {
                    actions += `
                        <form
                            action="/clients/${row.id}"
                            method="POST"
                            onsubmit="return confirm('¿Eliminar este cliente?')"
                        >
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="ti ti-trash me-1"></i>
                                Eliminar
                            </button>
                        </form>
                    `;
                }

                return actions
                    ? `<div class="btn-list flex-nowrap">${actions}</div>`
                    : '';
            },
        },
    ], {
        entityName: 'clientes',
        loader: false,
    });

    const modalElement = document.getElementById('configure-client-modal');
    const form = document.getElementById('configure-client-form');

    if (!modalElement || !form || !window.BanHttp) {
        return;
    }

    const modal = Modal.getOrCreateInstance(modalElement);
    const clientIdInput = document.getElementById('configure-client-id');
    const clientNameLabel = document.getElementById('configure-client-name');
    const modalTitleText = document.getElementById('configure-client-modal-title-text');
    const modalIcon = document.getElementById('configure-client-modal-icon');
    const contractSelect = document.getElementById('configure-contract-id');
    const zoneSelect = document.getElementById('configure-zone-id');
    const startDateInput = document.getElementById('configure-start-date');
    const endDateInput = document.getElementById('configure-end-date');
    const notesInput = document.getElementById('configure-notes');
    const profileCheckboxes = () => [
        ...document.querySelectorAll('.configure-profile-checkbox'),
    ];
    const rejectionAlert = document.getElementById('configure-client-rejection');
    const readonlyAlert = document.getElementById('configure-client-readonly');
    const prevBtn = document.getElementById('configure-prev-btn');
    const nextBtn = document.getElementById('configure-next-btn');
    const saveCloseBtn = document.getElementById('configure-save-close-btn');
    const submitBtn = document.getElementById('configure-submit-btn');

    let currentStep = 1;
    let canEdit = true;
    let configurationStatus = 'configuration_pending';
    let activeContract = null;
    let hasActiveContract = false;

    $(document).on('click', '.configure-client-btn', async function () {
        const clientId = $(this).data('client-id');

        try {
            const data = await window.BanHttp.get(
                `/clients/${clientId}/configuration`,
                'Cargando configuración...',
            );
            fillForm(data);
            goToStep(1);
            modal.show();
        } catch (error) {
            showToast('danger', extractError(error, 'No se pudo cargar la configuración.'));
        }
    });

    $(contractSelect).on('change', () => {
        updateContractDetails();
        updateEndDate();
        updateSummary();
        updateSubmitButton();
    });

    $(zoneSelect).on('change', () => {
        updateZoneDetails();
        updateSummary();
        updateSubmitButton();
    });

    $(document).on('change', '.configure-profile-checkbox', () => {
        updateSummary();
        updateSubmitButton();
    });

    $(startDateInput).on('change', updateEndDate);

    $(prevBtn).on('click', () => {
        if (currentStep > 1) {
            goToStep(currentStep - 1);
        }
    });

    $(nextBtn).on('click', async () => {
        const saved = await saveConfiguration(false);

        if (!saved) {
            return;
        }

        if (currentStep < TOTAL_STEPS) {
            goToStep(currentStep + 1);
        }
    });

    $(saveCloseBtn).on('click', async () => {
        const saved = await saveConfiguration(false);

        if (saved) {
            modal.hide();
            table?.ajax?.reload(null, false);
        }
    });

    $(submitBtn).on('click', async () => {
        const saved = await saveConfiguration(false);

        if (!saved) {
            return;
        }

        try {
            const response = await window.BanHttp.post(
                `/clients/${clientIdInput.value}/configuration/submit`,
                {},
                'Enviando a aprobación...',
            );

            modal.hide();
            showToast('success', response.message || 'Configuración enviada a aprobación.');
            table?.ajax?.reload(null, false);
        } catch (error) {
            showToast('danger', extractError(error, 'No se pudo enviar a aprobación.'));
        }
    });

    function fillForm(data) {
        form.reset();
        canEdit = data.can_edit !== false;
        configurationStatus = data.configuration_status || 'configuration_pending';
        activeContract = data.active_contract || null;
        hasActiveContract = Boolean(data.has_active_contract);
        clientIdInput.value = data.id;
        clientNameLabel.textContent = data.full_name || '';
        contractSelect.value = data.contract_id ?? '';
        zoneSelect.value = data.zone_id ?? '';
        startDateInput.value = data.start_date || new Date().toISOString().slice(0, 10);
        endDateInput.value = data.end_date || '';
        notesInput.value = data.notes || '';

        const selectedProfileIds = new Set(
            (data.profile_ids || []).map((id) => String(id)),
        );

        profileCheckboxes().forEach((checkbox) => {
            checkbox.checked = selectedProfileIds.has(checkbox.value);
        });

        updateModalTitle(hasActiveContract);

        if (data.rejection_reason) {
            rejectionAlert.textContent = `Rechazado: ${data.rejection_reason}`;
            rejectionAlert.classList.remove('d-none');
        } else {
            rejectionAlert.classList.add('d-none');
        }

        readonlyAlert.classList.toggle('d-none', canEdit);
        setFormEditable(canEdit);
        updateContractDetails();
        updateZoneDetails();

        if (!endDateInput.value) {
            updateEndDate();
        }

        updateSummary();
        updateSubmitButton();
    }

    function updateModalTitle(isUpdate) {
        if (modalTitleText) {
            modalTitleText.textContent = isUpdate
                ? 'Actualizar contrato'
                : 'Asignar contrato';
        }

        if (modalIcon) {
            modalIcon.className = isUpdate
                ? 'ti ti-file-pencil me-2'
                : 'ti ti-file-plus me-2';
        }
    }

    function setFormEditable(editable) {
        [contractSelect, zoneSelect, startDateInput, endDateInput, notesInput]
            .forEach((el) => {
                el.disabled = !editable;
            });

        profileCheckboxes().forEach((checkbox) => {
            checkbox.disabled = !editable;
        });

        saveCloseBtn.classList.toggle('d-none', !editable);
        nextBtn.classList.toggle('d-none', !editable);
        submitBtn.classList.toggle('d-none', !editable || currentStep !== TOTAL_STEPS);
    }

    function selectedProfileIds() {
        return profileCheckboxes()
            .filter((checkbox) => checkbox.checked)
            .map((checkbox) => Number(checkbox.value));
    }

    async function saveConfiguration(closeAfter) {
        if (!canEdit) {
            return true;
        }

        const payload = {
            contract_id: contractSelect.value || null,
            zone_id: zoneSelect.value || null,
            start_date: startDateInput.value || null,
            end_date: endDateInput.value || null,
            notes: notesInput.value || null,
            profile_ids: selectedProfileIds(),
        };

        try {
            const response = await window.BanHttp.put(
                `/clients/${clientIdInput.value}/configuration`,
                payload,
                'Guardando configuración...',
            );

            configurationStatus = response.configuration_status || configurationStatus;
            updateSummary();
            showToast('success', response.message || 'Configuración guardada.');

            if (closeAfter) {
                modal.hide();
                table?.ajax?.reload(null, false);
            }

            return true;
        } catch (error) {
            showToast('danger', extractError(error, 'No se pudo guardar la configuración.'));
            return false;
        }
    }

    function goToStep(step) {
        currentStep = step;

        document.querySelectorAll('#configure-client-steps .step-item').forEach((item) => {
            const itemStep = Number(item.dataset.step);
            item.classList.toggle('active', itemStep === step);
        });

        document.querySelectorAll('.configure-step').forEach((panel) => {
            panel.classList.toggle('d-none', Number(panel.dataset.stepPanel) !== step);
        });

        prevBtn.classList.toggle('d-none', step === 1 || !canEdit);
        nextBtn.classList.toggle('d-none', step === TOTAL_STEPS || !canEdit);
        submitBtn.classList.toggle('d-none', step !== TOTAL_STEPS || !canEdit);
        updateSummary();
        updateSubmitButton();
    }

    function updateContractDetails() {
        const option = contractSelect.selectedOptions[0];
        const details = document.getElementById('configure-contract-details');

        if (!option?.value) {
            details.classList.add('d-none');
            return;
        }

        document.getElementById('configure-contract-duration').textContent =
            `${option.dataset.durationMonths || '—'} meses`;
        document.getElementById('configure-contract-frequency').textContent =
            option.dataset.frequency || '—';
        document.getElementById('configure-contract-catalog-notes').textContent =
            option.dataset.notes || 'Sin notas';
        details.classList.remove('d-none');
    }

    function updateZoneDetails() {
        const option = zoneSelect.selectedOptions[0];
        const details = document.getElementById('configure-zone-details');

        if (!option?.value) {
            details.classList.add('d-none');
            return;
        }

        document.getElementById('configure-zone-description').textContent =
            option.dataset.description || 'Sin descripción';
        details.classList.remove('d-none');
    }

    function updateEndDate() {
        const option = contractSelect.selectedOptions[0];
        const months = Number(option?.dataset.durationMonths || 0);
        const startDate = startDateInput.value;

        if (!months || !startDate) {
            return;
        }

        const date = new Date(`${startDate}T00:00:00`);
        date.setMonth(date.getMonth() + months);
        endDateInput.value = date.toISOString().slice(0, 10);
    }

    function updateSummary() {
        const contractOption = contractSelect.selectedOptions[0];
        const zoneOption = zoneSelect.selectedOptions[0];
        const activeAlert = document.getElementById('summary-active-contract-alert');
        const selectedProfiles = profileCheckboxes()
            .filter((checkbox) => checkbox.checked)
            .map((checkbox) => `${checkbox.dataset.profileCode} — ${checkbox.dataset.profileName}`);

        document.getElementById('summary-client-name').textContent =
            clientNameLabel.textContent || '—';
        document.getElementById('summary-contract').textContent =
            contractOption?.value ? contractOption.textContent.trim() : 'Sin seleccionar';
        document.getElementById('summary-duration').textContent =
            contractOption?.dataset.durationMonths
                ? `${contractOption.dataset.durationMonths} meses`
                : '—';
        document.getElementById('summary-zone').textContent =
            zoneOption?.value ? zoneOption.textContent.trim() : 'Sin seleccionar';
        document.getElementById('summary-profiles').textContent =
            selectedProfiles.length > 0 ? selectedProfiles.join(', ') : 'Sin seleccionar';
        document.getElementById('summary-status').textContent =
            STATUS_LABELS[configurationStatus] || configurationStatus;

        if (activeContract?.name) {
            activeAlert.textContent =
                `Contrato vigente actual: ${activeContract.name}`
                + (activeContract.end_date ? ` (hasta ${activeContract.end_date}).` : '.')
                + ' Al aprobar ambos directores, el nuevo lo reemplazará.';
            activeAlert.classList.remove('d-none');
        } else {
            activeAlert.classList.add('d-none');
        }
    }

    function updateSubmitButton() {
        const ready = Boolean(
            contractSelect.value
            && zoneSelect.value
            && selectedProfileIds().length > 0
            && canEdit,
        );
        submitBtn.disabled = !ready;
    }

    function extractError(error, fallback) {
        return error.response?.data?.message
            ?? Object.values(error.response?.data?.errors ?? {}).flat()[0]
            ?? fallback;
    }
});
