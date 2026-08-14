const FILL = [255, 255, 255];
const BORDER = [0, 0, 0];
const TEXT = [0, 0, 0];

const COLORS = {
    headerFill: FILL,
    headerBorder: BORDER,
    headerBarFill: BORDER,
    headerText: TEXT,

    folioFill: FILL,
    folioBorder: BORDER,
    folioText: TEXT,

    uuidLabel: TEXT,
    uuidText: TEXT,

    receptorTitleFill: FILL,
    receptorTitleBorder: BORDER,
    receptorTitleText: TEXT,
    receptorFill: FILL,
    receptorBorder: BORDER,
    receptorLabel: TEXT,
    receptorText: TEXT,

    tableHeaderFill: FILL,
    tableHeaderBorder: BORDER,
    tableHeaderText: TEXT,
    tableRowFill: FILL,
    tableRowAltFill: FILL,
    tableRowBorder: BORDER,
    tableCodeText: TEXT,
    tableText: TEXT,
    tableMuted: TEXT,
    tableAmountText: TEXT,

    totalsFill: FILL,
    totalsBorder: BORDER,
    totalsLabel: TEXT,
    totalsText: TEXT,
    totalsBarFill: FILL,
    totalsBarBorder: BORDER,
    totalsBarText: TEXT,

    qrFill: FILL,
    qrBorder: BORDER,
    qrText: TEXT,

    stampTitleFill: FILL,
    stampTitleBorder: BORDER,
    stampTitleText: TEXT,
    stampText: TEXT,
    stampLabel: TEXT,

    footerFill: FILL,
    footerBorder: BORDER,
    footerLabel: TEXT,
    footerText: TEXT,
};

export function buildInvoice(doc, invoice, img, qrImage) {
    const client = invoice.client || {};
    const issuer = invoice.issuer || {};
    const items = invoice.items || [];
    const ivaPercentage = Number(invoice.iva_percentage || 16);
    const subtotal = items.reduce(
        (sum, item) => sum + Number(item.quantity || 1) * Number(item.price || 0),
        0,
    );
    const iva = subtotal * (ivaPercentage / 100);
    const total = subtotal + iva;

    paintHeader(doc, img, issuer, invoice);
    paintReceptor(doc, client);
    const tableEnd = paintItems(doc, items);
    const totalsEnd = paintTotals(doc, tableEnd, subtotal, iva, ivaPercentage, total);
    paintStamps(doc, invoice, qrImage, totalsEnd);

    return doc;
}

function paintHeader(doc, img, issuer, invoice) {
    fillRect(doc, 0, 0, 210, 38, COLORS.headerFill, COLORS.headerBorder);
    fillRect(doc, 0, 38, 210, 2, COLORS.headerBarFill, COLORS.headerBarFill);

    const logo = logoToPng(img);
    doc.addImage(logo, "PNG", 10, 6, 22, 22);

    doc.setTextColor(...COLORS.headerText);
    doc.setFont("helvetica", "bold");
    doc.setFontSize(14);
    doc.text(36, 14, issuer.razon_social || "BAN RPBI");
    doc.setFont("helvetica", "normal");
    doc.setFontSize(7);
    doc.text(36, 20, issuer.address || "");
    doc.text(
        36,
        24,
        [
            issuer.street,
            issuer.num_ext,
            issuer.colony,
            issuer.city,
            issuer.state,
            issuer.postal_code,
        ]
            .filter(Boolean)
            .join(" "),
    );
    if (issuer.phone) {
        doc.text(36, 28, `Tel: ${issuer.phone}`);
    }

    fillRoundedRect(doc, 138, 6, 62, 26, 2, COLORS.folioFill, COLORS.folioBorder);
    doc.setTextColor(...COLORS.folioText);
    doc.setFont("helvetica", "bold");
    doc.setFontSize(10);
    doc.text(169, 13, "FACTURA CFDI", { align: "center" });
    doc.setFont("helvetica", "normal");
    doc.setFontSize(7);
    doc.text(141, 19, "Folio: " + String(invoice.folio ?? ""));
    doc.text(141, 23, "Fecha: " + String(invoice.created_at ?? ""));
    doc.text(141, 27, "Estatus: " + String(invoice.status ?? ""));

    doc.setTextColor(...COLORS.uuidLabel);
    doc.setFont("helvetica", "bold");
    doc.setFontSize(7);
    doc.text(10, 46, "UUID / Folio fiscal:");
    doc.setFont("helvetica", "normal");
    doc.setTextColor(...COLORS.uuidText);
    doc.text(42, 46, String(invoice.uuid || "PENDIENTE"));
}

function paintReceptor(doc, client) {
    fillRect(doc, 10, 50, 189, 7, COLORS.receptorTitleFill, COLORS.receptorTitleBorder);
    doc.setTextColor(...COLORS.receptorTitleText);
    doc.setFont("helvetica", "bold");
    doc.setFontSize(8);
    doc.text(12, 55, "RECEPTOR");

    fillRect(doc, 10, 57, 189, 22, COLORS.receptorFill, COLORS.receptorBorder);

    doc.setTextColor(...COLORS.receptorLabel);
    doc.setFont("helvetica", "normal");
    doc.setFontSize(6.5);
    doc.text(12, 62, "Nombre / Razón social");
    doc.setTextColor(...COLORS.receptorText);
    doc.setFont("helvetica", "bold");
    doc.setFontSize(8);
    doc.text(12, 66, String(client.name || "—"));

    doc.setTextColor(...COLORS.receptorLabel);
    doc.setFont("helvetica", "normal");
    doc.setFontSize(6.5);
    doc.text(12, 71, "RFC");
    doc.text(52, 71, "NRA");
    doc.text(110, 71, "Correo");
    doc.setTextColor(...COLORS.receptorText);
    doc.setFont("helvetica", "bold");
    doc.setFontSize(7.5);
    doc.text(12, 75, String(client.rfc || "—"));
    doc.text(52, 75, String(client.nra || "—"));
    doc.setFont("helvetica", "normal");
    doc.text(110, 75, String(client.email || "—"));
}

function paintItems(doc, items) {
    const tableTop = 84;

    fillRect(doc, 10, tableTop, 189, 7, COLORS.tableHeaderFill, COLORS.tableHeaderBorder);
    doc.setTextColor(...COLORS.tableHeaderText);
    doc.setFont("helvetica", "bold");
    doc.setFontSize(7);
    doc.text(12, tableTop + 5, "Clave");
    doc.text(32, tableTop + 5, "Descripción");
    doc.text(128, tableTop + 5, "Cant.");
    doc.text(148, tableTop + 5, "P. unitario");
    doc.text(176, tableTop + 5, "Importe");

    let y = tableTop + 7;
    const rows = items.length ? items : [{ empty: true }];

    rows.forEach((item, index) => {
        const rowHeight = 12;
        const isEven = index % 2 === 0;
        const rowFill = isEven ? COLORS.tableRowFill : COLORS.tableRowAltFill;

        fillRect(doc, 10, y, 189, rowHeight, rowFill, COLORS.tableRowBorder);

        if (item.empty) {
            doc.setTextColor(...COLORS.tableMuted);
            doc.setFont("helvetica", "normal");
            doc.setFontSize(7);
            doc.text(12, y + 7, "Sin conceptos");
            y += rowHeight;
            return;
        }

        const quantity = Number(item.quantity || 1);
        const price = Number(item.price || 0);
        const amount = quantity * price;

        doc.setTextColor(...COLORS.tableCodeText);
        doc.setFont("helvetica", "bold");
        doc.setFontSize(8);
        doc.text(12, y + 5, String(item.code || ""));

        doc.setTextColor(...COLORS.tableText);
        doc.text(32, y + 5, String(item.name || ""));
        doc.setFont("helvetica", "normal");
        doc.setFontSize(6);
        doc.setTextColor(...COLORS.tableMuted);
        doc.text(32, y + 9, String(item.description || "").substring(0, 85));

        doc.setTextColor(...COLORS.tableText);
        doc.setFontSize(7.5);
        doc.text(140, y + 7, String(quantity), { align: "right" });
        doc.text(168, y + 7, formatMoney(price), { align: "right" });
        doc.setFont("helvetica", "bold");
        doc.setTextColor(...COLORS.tableAmountText);
        doc.text(196, y + 7, formatMoney(amount), { align: "right" });

        y += rowHeight;
    });

    return y;
}

function paintTotals(doc, y, subtotal, iva, ivaPercentage, total) {
    const boxY = y + 4;

    fillRoundedRect(doc, 137, boxY, 62, 24, 2, COLORS.totalsFill, COLORS.totalsBorder);

    doc.setTextColor(...COLORS.totalsLabel);
    doc.setFont("helvetica", "normal");
    doc.setFontSize(7.5);
    doc.text(140, boxY + 6, "Subtotal");
    doc.setTextColor(...COLORS.totalsText);
    doc.text(196, boxY + 6, formatMoney(subtotal), { align: "right" });

    doc.setTextColor(...COLORS.totalsLabel);
    doc.text(140, boxY + 12, `IVA ${ivaPercentage}%`);
    doc.setTextColor(...COLORS.totalsText);
    doc.text(196, boxY + 12, formatMoney(iva), { align: "right" });

    fillRect(doc, 137, boxY + 16, 62, 8, COLORS.totalsBarFill, COLORS.totalsBarBorder);
    doc.setTextColor(...COLORS.totalsBarText);
    doc.setFont("helvetica", "bold");
    doc.setFontSize(8);
    doc.text(140, boxY + 21.5, "Total");
    doc.text(196, boxY + 21.5, formatMoney(total), { align: "right" });

    return boxY + 28;
}

function paintStamps(doc, invoice, qrImage, startY) {
    let y = startY;

    if (qrImage) {
        fillRoundedRect(doc, 10, y, 36, 40, 2, COLORS.qrFill, COLORS.qrBorder);
        doc.addImage(qrImage, "PNG", 12, y + 2, 32, 32);
        doc.setTextColor(...COLORS.qrText);
        doc.setFont("helvetica", "bold");
        doc.setFontSize(6);
        doc.text(28, y + 37, "QR SAT", { align: "center" });
    }

    doc.setTextColor(...COLORS.stampLabel);
    doc.setFont("helvetica", "bold");
    doc.setFontSize(7);
    doc.text(50, y + 4, "Cadena original del complemento SAT");
    y = writeWrapped(
        doc,
        invoice.cadena_complemento,
        50,
        y + 8,
        148,
        5.5,
        COLORS.stampText,
    ) + 4;

    y = Math.max(y, startY + 42);

    y = stampBlock(doc, y, "Sello digital del CFDI", invoice.sello_cfdi);
    y = stampBlock(doc, y, "Sello digital del SAT", invoice.sello_sat);

    fillRect(doc, 10, y, 189, 16, COLORS.footerFill, COLORS.footerBorder);

    doc.setTextColor(...COLORS.footerLabel);
    doc.setFont("helvetica", "bold");
    doc.setFontSize(7);
    doc.text(12, y + 5, "No. de serie del certificado SAT");
    doc.setTextColor(...COLORS.footerText);
    doc.setFont("helvetica", "normal");
    doc.text(12, y + 10, String(invoice.serie_sat || "—"));

    doc.setTextColor(...COLORS.footerLabel);
    doc.setFont("helvetica", "bold");
    doc.text(90, y + 5, "URL de verificación");
    doc.setFont("helvetica", "normal");
    doc.setFontSize(6);
    writeWrapped(doc, invoice.verification_url, 90, y + 9, 106, 5.5, COLORS.footerText);
}

function stampBlock(doc, y, title, value) {
    if (y > 250) {
        doc.addPage();
        y = 16;
    }

    const textY = y + 10;
    const endY = measureWrapped(doc, value, 185, 5.5);
    const boxHeight = Math.max(textY + endY - y + 2, 14);

    fillRect(doc, 10, y, 189, boxHeight, COLORS.stampTitleFill, COLORS.stampTitleBorder);
    doc.setFillColor(...COLORS.stampTitleFill);
    doc.rect(10, y, 189, 6, "F");

    doc.setTextColor(...COLORS.stampTitleText);
    doc.setFont("helvetica", "bold");
    doc.setFontSize(7.5);
    doc.text(12, y + 4.2, title);

    writeWrapped(doc, value, 12, textY, 185, 5.5, COLORS.stampText);

    return y + boxHeight + 3;
}

function writeWrapped(doc, value, x, y, width, fontSize, color = COLORS.stampText) {
    const text = String(value || "").trim() || "—";

    doc.setFont("helvetica", "normal");
    doc.setFontSize(fontSize);
    doc.setTextColor(...color);

    const lines = doc.splitTextToSize(text, width);
    doc.text(lines, x, y);

    return y + lines.length * (fontSize * 0.42 + 0.6);
}

function measureWrapped(doc, value, width, fontSize) {
    const text = String(value || "").trim() || "—";
    doc.setFont("helvetica", "normal");
    doc.setFontSize(fontSize);
    const lines = doc.splitTextToSize(text, width);

    return lines.length * (fontSize * 0.42 + 0.6);
}

function fillRect(doc, x, y, w, h, fill, border) {
    doc.setFillColor(...fill);
    doc.setDrawColor(...border);
    doc.rect(x, y, w, h, "FD");
}

function fillRoundedRect(doc, x, y, w, h, r, fill, border) {
    doc.setFillColor(...fill);
    doc.setDrawColor(...border);
    doc.roundedRect(x, y, w, h, r, r, "FD");
}

function logoToPng(img) {
    const canvas = document.createElement("canvas");
    canvas.width = img.width;
    canvas.height = img.height;
    const ctx = canvas.getContext("2d");
    ctx.fillStyle = "#ffffff";
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    ctx.drawImage(img, 0, 0);

    return canvas.toDataURL("image/png");
}

function formatMoney(value) {
    return (
        "$" +
        Number(value || 0).toLocaleString("es-MX", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })
    );
}
