import { Modal } from '@tabler/core/dist/js/tabler.esm.js';
import { createDataTable } from '../../shared/datatable.js';
import { showToast } from '../../admin.js';

const STATUS_LABELS = {
    configuration_pending: 'Pendiente de configuración',
    pending_approval: 'Pendiente de aprobación',
    approved: 'Aprobado',
    rejected: 'Rechazado',
};

const TOTAL_STEPS = 3;

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
    const rejectionAlert = document.getElementById('configure-client-rejection');
    const readonlyAlert = document.getElementById('configure-client-readonly');
    const prevBtn = document.getElementById('configure-prev-btn');
    const nextBtn = document.getElementById('configure-next-btn');
    const saveCloseBtn = document.getElementById('configure-save-close-btn');
    const submitBtn = document.getElementById('configure-submit-btn');
    const generateInvoiceCheckbox = document.getElementById('configure-generate-invoice');
    const invoiceManifestsWrap = document.getElementById('configure-invoice-manifests-wrap');
    const invoiceManifestCountInput = document.getElementById('configure-invoice-manifest-count');
    const invoiceManifestsHint = document.getElementById('configure-invoice-manifests-hint');

    let currentStep = 1;
    let canEdit = true;
    let configurationStatus = 'configuration_pending';
    let activeContract = null;
    let hasActiveContract = false;
    let expectedCollectionsCount = 0;

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
        updateInvoiceOptions();
        updateSummary();
        updateSubmitButton();
    });

    $(zoneSelect).on('change', () => {
        updateZoneDetails();
        updateSummary();
        updateSubmitButton();
    });

    $(startDateInput).on('change', () => {
        updateEndDate();
        updateInvoiceOptions();
        updateSummary();
    });

    $(endDateInput).on('change', () => {
        updateInvoiceOptions();
        updateSummary();
    });

    $(generateInvoiceCheckbox).on('change', () => {
        updateInvoiceOptions();
    });

    $(invoiceManifestCountInput).on('input change', () => {
        clampInvoiceManifestCount();
    });

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
        generateInvoiceCheckbox.checked = false;
        invoiceManifestCountInput.value = '';

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

        updateInvoiceOptions();
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
        [contractSelect, zoneSelect, startDateInput, endDateInput, notesInput, generateInvoiceCheckbox, invoiceManifestCountInput]
            .forEach((el) => {
                el.disabled = !editable;
            });

        saveCloseBtn.classList.toggle('d-none', !editable);
        nextBtn.classList.toggle('d-none', !editable);
        submitBtn.classList.toggle('d-none', !editable || currentStep !== TOTAL_STEPS);
    }

    async function saveConfiguration(closeAfter) {
        if (!canEdit) {
            return true;
        }

        const generateInvoice = Boolean(generateInvoiceCheckbox.checked);
        const payload = {
            contract_id: contractSelect.value || null,
            zone_id: zoneSelect.value || null,
            start_date: startDateInput.value || null,
            end_date: endDateInput.value || null,
            notes: notesInput.value || null,
            generate_invoice: generateInvoice,
            invoice_manifest_count: generateInvoice
                ? Number(invoiceManifestCountInput.value || 0) || null
                : null,
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
        updateInvoiceOptions();
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
        document.getElementById('configure-contract-cost').textContent =
            formatCost(option.dataset.cost);
        document.getElementById('configure-contract-profiles').textContent =
            option.dataset.profiles || 'Sin perfiles';
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

        document.getElementById('summary-client-name').textContent =
            clientNameLabel.textContent || '—';
        document.getElementById('summary-contract').textContent =
            contractOption?.value ? contractOption.textContent.trim() : 'Sin seleccionar';
        document.getElementById('summary-duration').textContent =
            contractOption?.dataset.durationMonths
                ? `${contractOption.dataset.durationMonths} meses`
                : '—';
        document.getElementById('summary-cost').textContent =
            contractOption?.value ? formatCost(contractOption.dataset.cost) : '—';
        document.getElementById('summary-zone').textContent =
            zoneOption?.value ? zoneOption.textContent.trim() : 'Sin seleccionar';
        document.getElementById('summary-profiles').textContent =
            contractOption?.dataset.profiles || 'Sin seleccionar';
        document.getElementById('summary-status').textContent =
            STATUS_LABELS[configurationStatus] || configurationStatus;
        document.getElementById('summary-collections-count').textContent =
            expectedCollectionsCount > 0
                ? String(expectedCollectionsCount)
                : '—';

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
        const generateInvoice = Boolean(generateInvoiceCheckbox.checked);
        const invoiceCount = Number(invoiceManifestCountInput.value || 0);
        const invoiceReady = !generateInvoice
            || (
                invoiceCount >= 1
                && expectedCollectionsCount > 0
                && invoiceCount <= expectedCollectionsCount
            );

        const ready = Boolean(
            contractSelect.value
            && zoneSelect.value
            && canEdit
            && invoiceReady,
        );
        submitBtn.disabled = !ready;
    }

    function updateInvoiceOptions() {
        expectedCollectionsCount = countExpectedCollections();
        const generateInvoice = Boolean(generateInvoiceCheckbox.checked);

        invoiceManifestsWrap.classList.toggle('d-none', !generateInvoice);
        invoiceManifestCountInput.disabled = !canEdit || !generateInvoice;

        if (expectedCollectionsCount > 0) {
            invoiceManifestCountInput.min = '1';
            invoiceManifestCountInput.max = String(expectedCollectionsCount);
            invoiceManifestsHint.textContent =
                `Máximo ${expectedCollectionsCount} (recolecciones que se van a generar).`;
        } else {
            invoiceManifestCountInput.removeAttribute('max');
            invoiceManifestsHint.textContent =
                'El máximo es el número de recolecciones que se van a generar.';
        }

        if (generateInvoice) {
            if (!invoiceManifestCountInput.value && expectedCollectionsCount > 0) {
                invoiceManifestCountInput.value = String(expectedCollectionsCount);
            }
            clampInvoiceManifestCount();
        } else {
            invoiceManifestCountInput.value = '';
        }

        updateSubmitButton();
    }

    function clampInvoiceManifestCount() {
        if (!generateInvoiceCheckbox.checked) {
            return;
        }

        let value = Number(invoiceManifestCountInput.value || 0);

        if (!Number.isFinite(value) || value < 1) {
            value = expectedCollectionsCount > 0 ? 1 : 0;
        }

        if (expectedCollectionsCount > 0 && value > expectedCollectionsCount) {
            value = expectedCollectionsCount;
        }

        if (value > 0) {
            invoiceManifestCountInput.value = String(value);
        }

        updateSubmitButton();
    }

    /**
     * Mirrors ServiceDateGenerator: one recolección (and manifesto) per period
     * while start_date < end_date for weekly / biweekly / monthly.
     */
    function countExpectedCollections() {
        const frequency = contractSelect.selectedOptions[0]?.dataset.frequencyKey;
        const startValue = startDateInput.value;
        const endValue = endDateInput.value;

        if (!frequency || !startValue || !endValue) {
            return 0;
        }

        const startDate = parseLocalDate(startValue);
        const endDate = parseLocalDate(endValue);

        if (!startDate || !endDate || !(startDate < endDate)) {
            return 0;
        }

        let count = 0;
        let cursor = new Date(startDate);

        while (cursor < endDate) {
            count += 1;
            cursor = nextServiceDate(cursor, frequency);

            if (count > 10000) {
                break;
            }
        }

        return count;
    }

    function parseLocalDate(value) {
        const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value);

        if (!match) {
            return null;
        }

        return new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));
    }

    function nextServiceDate(date, frequency) {
        const next = new Date(date);

        if (frequency === 'weekly') {
            next.setDate(next.getDate() + 7);
            return next;
        }

        if (frequency === 'biweekly') {
            next.setDate(next.getDate() + 14);
            return next;
        }

        if (frequency === 'monthly') {
            const day = next.getDate();
            next.setDate(1);
            next.setMonth(next.getMonth() + 1);
            const lastDay = new Date(next.getFullYear(), next.getMonth() + 1, 0).getDate();
            next.setDate(Math.min(day, lastDay));
            return next;
        }

        return next;
    }

    function formatCost(value) {
        const amount = Number(value);

        if (Number.isNaN(amount)) {
            return '—';
        }

        return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN',
        }).format(amount);
    }

    function extractError(error, fallback) {
        return error.response?.data?.message
            ?? Object.values(error.response?.data?.errors ?? {}).flat()[0]
            ?? fallback;
    }
});
