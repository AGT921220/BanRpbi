import { get } from "../../admin.js";

const CLIENT_SELECT = "#client_id";
const SERVICES_CHECKLIST = "#services-checklist";
const SERVICES_EMPTY_MESSAGE = "#services-empty-message";
const SELECT_ALL = "#select-all-services";
const SUBMIT_BUTTON = "#create-invoice-submit";

const STATUS_LABELS = {
    pending: "Pendiente",
    scheduled: "Programada",
};

$(function () {
    initClientSelect();
    initSelectAll();
});

function initClientSelect() {
    $(CLIENT_SELECT).on("change", function () {
        loadBillableServices($(this).val());
    });

    if ($(CLIENT_SELECT).val()) {
        loadBillableServices($(CLIENT_SELECT).val());
    }
}

async function loadBillableServices(clientId) {
    resetServicesList();

    if (!clientId) {
        return;
    }

    try {
        const response = await get(
            "/admin/invoice-billable-service-headers",
            "Cargando servicios por facturar...",
            { client_id: clientId },
        );

        renderServices(response.data ?? []);
    } catch (error) {
        $(SERVICES_EMPTY_MESSAGE)
            .removeClass("d-none")
            .text("No fue posible cargar los servicios del cliente.");
    }
}

function renderServices(services) {
    if (!services.length) {
        $(SERVICES_EMPTY_MESSAGE)
            .removeClass("d-none")
            .text("Este cliente no tiene servicios pendientes de factura.");
        $(SUBMIT_BUTTON).prop("disabled", true);

        return;
    }

    $(SERVICES_EMPTY_MESSAGE).addClass("d-none");
    $(SERVICES_CHECKLIST).removeClass("d-none").html("");

    services.forEach((service) => {
        const statusLabel = STATUS_LABELS[service.status] ?? service.status;
        const manifestLabel = service.manifest_id
            ? `Manifiesto #${service.manifest_id}`
            : "Sin manifiesto";

        $(SERVICES_CHECKLIST).append(`
            <label class="row align-items-center py-3 cursor-pointer" for="service-${service.id}">
                <span class="col-auto">
                    <input
                        type="checkbox"
                        class="form-check-input service-checkbox"
                        name="service_ids[]"
                        id="service-${service.id}"
                        value="${service.id}"
                    >
                </span>
                <span class="col">
                    <span class="fw-bold">Servicio #${service.id}</span>
                    <span class="d-block text-secondary">
                        Fecha: ${formatDate(service.service_date)}
                        · Zona: ${service.zone ?? "Sin zona"}
                        · ${manifestLabel}
                    </span>
                    <span class="d-block text-secondary small">
                        Estado: ${statusLabel}
                    </span>
                </span>
            </label>
        `);
    });

    $(SELECT_ALL).prop("disabled", false);
    $(SUBMIT_BUTTON).prop("disabled", false);
    bindServiceCheckboxEvents();
    restoreOldSelections();
}

function resetServicesList() {
    $(SERVICES_CHECKLIST).addClass("d-none").empty();
    $(SELECT_ALL).prop("checked", false).prop("disabled", true);
    $(SUBMIT_BUTTON).prop("disabled", true);
    $(SERVICES_EMPTY_MESSAGE)
        .removeClass("d-none")
        .text("Selecciona un cliente para ver sus servicios pendientes de factura.");
}

function initSelectAll() {
    $(SELECT_ALL).on("change", function () {
        $(".service-checkbox").prop("checked", $(this).is(":checked"));
    });
}

function bindServiceCheckboxEvents() {
    $(".service-checkbox").on("change", function () {
        const $checkboxes = $(".service-checkbox");
        const total = $checkboxes.length;
        const checked = $checkboxes.filter(":checked").length;

        $(SELECT_ALL).prop("checked", total > 0 && total === checked);
        $(SUBMIT_BUTTON).prop("disabled", checked === 0);
    });
}

function restoreOldSelections() {
    const oldValues = new Set(
        ($("#create-invoice-form").data("old-service-ids") ?? "")
            .toString()
            .split(",")
            .filter(Boolean),
    );

    if (!oldValues.size) {
        return;
    }

    $(".service-checkbox").each(function () {
        $(this).prop("checked", oldValues.has($(this).val()));
    });

    const $checkboxes = $(".service-checkbox");
    const total = $checkboxes.length;
    const checked = $checkboxes.filter(":checked").length;

    $(SELECT_ALL).prop("checked", total > 0 && total === checked);
    $(SUBMIT_BUTTON).prop("disabled", checked === 0);
}

function formatDate(value) {
    if (!value) {
        return "—";
    }

    const [year, month, day] = value.split("-").map(Number);
    const date = new Date(year, month - 1, day);

    return date.toLocaleDateString("es-MX", {
        day: "numeric",
        month: "long",
        year: "numeric",
    });
}
