import { Modal } from "bootstrap";

const TYPE_SELECT = "#waste_process_type_id";
const MANIFEST_PANEL = "#process-manifests";
const PLACEHOLDER = "#manifests-placeholder";
const OPEN_CONFIRM = "#open-process-confirm";
const SELECT_ALL = "#select-all-manifests";
const CONFIRM_LIST = "#selected-manifests";
const CONFIRM_MODAL = "#process-confirm-modal";

document.addEventListener("DOMContentLoaded", () => {
    const typeSelect = document.querySelector(TYPE_SELECT);
    const panel = document.querySelector(MANIFEST_PANEL);
    const placeholder = document.querySelector(PLACEHOLDER);
    const openConfirm = document.querySelector(OPEN_CONFIRM);
    const selectAll = document.querySelector(SELECT_ALL);
    const confirmList = document.querySelector(CONFIRM_LIST);
    const modalElement = document.querySelector(CONFIRM_MODAL);

    if (!typeSelect || !panel || !modalElement) {
        return;
    }

    const modal = new Modal(modalElement);

    const showManifests = () => {
        const selected = typeSelect.value !== "";
        panel.classList.toggle("d-none", !selected);
        placeholder.classList.toggle("d-none", selected);
    };

    typeSelect.addEventListener("change", showManifests);
    showManifests();

    selectAll?.addEventListener("change", () => {
        document.querySelectorAll(".manifest-check").forEach((input) => {
            input.checked = selectAll.checked;
        });
    });

    openConfirm?.addEventListener("click", () => {
        const selected = [...document.querySelectorAll(".manifest-check:checked")];
        if (selected.length === 0) {
            return;
        }

        confirmList.innerHTML = selected
            .map((input) => `<li>Manifiesto ${input.dataset.label}</li>`)
            .join("");
        modal.show();
    });
});
