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

        let doc = new jsPDF();
        doc = buildContractPdf(doc, data);
        doc.setProperties({
            title: `Contrato ${data.client_contract_id}`,
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
