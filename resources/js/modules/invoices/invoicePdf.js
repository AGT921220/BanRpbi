import { jsPDF } from "jspdf";
import { buildInvoice } from "./invoicePdfBuilder";
import QRCode from "qrcode";
import { get, showToast } from "../../admin";

$(document).on("click", ".btn-invoice-pdf", async function (e) {
    e.preventDefault();

    const id = $(this).data("id");

    if (!id) {
        showToast("warning", "No hay factura disponible.");
        return;
    }

    try {
        const invoiceData = await getInvoice(`/admin/invoices/${id}`);

        if (invoiceData.has_official_pdf) {
            window.open(`/admin/invoices/${id}/pdf`, "_blank");
            return;
        }

        await createInvoice(invoiceData);
    } catch (error) {
        console.error(error);
        showToast("danger", "Error al generar la factura");
    }
});

async function getInvoice(url) {
    return get(url, "Cargando factura...").then((response) => {
        if (response && response.data) {
            return response.data;
        }
        throw new Error("No se pudo obtener la factura.");
    });
}

async function createInvoice(invoice) {
    let doc = new jsPDF();

    const qrPayload = String(
        invoice.verification_url || invoice.uuid || invoice.folio || "",
    );

    const qrImage = qrPayload
        ? await QRCode.toDataURL(qrPayload, {
              width: 300,
              margin: 1,
              color: {
                  dark: "#000000",
                  light: "#ffffff",
              },
          })
        : null;

    const img = await loadImage("/images/logo.png");
    doc = buildInvoice(doc, invoice, img, qrImage);

    doc.setProperties({
        title: "Factura " + (invoice.folio ?? ""),
        author: "BAN RPBI",
        creator: "BAN RPBI",
    });

    const url = URL.createObjectURL(doc.output("blob"));
    window.open(url, "_blank");
}

function loadImage(src) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.onload = () => resolve(img);
        img.onerror = reject;
        img.src = src;
    });
}
