import { jsPDF } from "jspdf";
import { buildInvoice } from "./invoicePdfBuilder";
import QRCode from "qrcode";
import { get, showToast } from "../../admin";

$(document).on("click", ".btn-invoice-pdf", async function (e) {
    e.preventDefault();

    const id = $(this).data("id");
    const url = `/admin/invoices/${id}`;

    try {
        const invoiceData = await getInvoice(url);

        createInvoice(invoiceData);
    } catch (error) {
        console.error(error);
        showToast("danger", "Error al generar la factura");
        return;
    }
});

async function getInvoice(url) {
    return get(url, "Creando Factura!", {}, {}, true, 15000).then((response) => {
        if (response && response.data) {
            return response.data;
        }
        throw new Error("No se pudo obtener la factura.");
    });
}

async function createInvoice(invoice) {
    var doc = new jsPDF();

    const qrPayload = String(
        invoice.verification_url || invoice.uuid || invoice.folio || "",
    );

    const qrImage = await QRCode.toDataURL(qrPayload, {
        width: 300,
        margin: 1,
        color: {
            dark: "#000000",
            light: "#ffffff",
        },
    });

    var img = new Image();
    img.onload = function () {
        doc = buildInvoice(doc, invoice, img, qrImage);

        doc.setProperties({
            title: "Factura " + invoice.folio,
            author: "BAN RPBI",
            creator: "BAN RPBI",
        });
        var pdfBlob = doc.output("blob");
        var url = URL.createObjectURL(pdfBlob);
        setTimeout(() => {
            window.open(url, "_blank");
        }, 500);
    };

    img.src = "/images/logo.png";
}
