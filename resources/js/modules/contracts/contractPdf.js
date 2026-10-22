import { jsPDF } from "jspdf";
import { get, showToast } from "../../admin";
import { buildContractPdf } from "./contractPdfBuilder";

$(document).on("click", ".btn-contract-pdf", async function (e) {
    e.preventDefault();

    const id = $(this).data("id");

    if (!id) {
        showToast("warning", "El contrato aún no está listo para generar PDF.");
        return;
    }

    try {
        const response = await get(
            `/admin/client-contracts/${id}`,
            "Generando contrato PDF...",
        );
        const data = response.data;

        let doc = new jsPDF({ unit: "pt", format: "letter" });
        // Por ahora el builder reproduce el DOC de referencia con datos fijos/vacíos.
        doc = buildContractPdf(doc, data);
        doc.setProperties({
            title: "CONTRATO DE PRESTACION DE SERVICIOS",
            author: "BAN RPBI",
            creator: "BAN RPBI",
        });

        const url = URL.createObjectURL(doc.output("blob"));
        window.open(url, "_blank");
    } catch (error) {
        console.error(error);
        const message =
            error?.response?.data?.message ||
            "No se pudo generar el PDF del contrato.";
        showToast("danger", message);
    }
});
