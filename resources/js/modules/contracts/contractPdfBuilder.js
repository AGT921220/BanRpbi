export function buildContractPdf(doc, data) {
    const issuer = data.issuer || {};
    const client = data.client || {};
    const contract = data.contract || {};

    doc.setFont("helvetica", "bold");
    doc.setFontSize(14);
    doc.text(issuer.razon_social || "BAN RPBI", 105, 18, { align: "center" });

    doc.setFontSize(12);
    doc.text("Contrato de prestación de servicios RPBI", 105, 28, { align: "center" });

    doc.setFont("helvetica", "normal");
    doc.setFontSize(9);
    let y = 42;

    y = writeLine(doc, y, "Prestador", issuer.razon_social || "—");
    y = writeLine(doc, y, "Domicilio prestador", issuer.address || "—");
    y = writeLine(doc, y, "Cliente", client.name || "—");
    y = writeLine(doc, y, "RFC", client.rfc || "—");
    y = writeLine(doc, y, "Domicilio cliente", client.address || "—");
    y = writeLine(doc, y, "Contrato catálogo", contract.name || "—");
    y = writeLine(doc, y, "Vigencia", `${data.start_date || "—"} a ${data.end_date || "—"}`);
    y = writeLine(doc, y, "Duración", `${contract.duration_months || "—"} meses`);
    y = writeLine(doc, y, "Frecuencia", contract.frequency_label || contract.frequency || "—");
    y = writeLine(doc, y, "Costo", formatMoney(data.price ?? contract.cost));
    y = writeLine(doc, y, "Estado", data.status_label || "—");

    const profiles = (contract.profiles || [])
        .map((p) => `${p.code || ""} ${p.name || ""}`.trim())
        .filter(Boolean)
        .join(", ");
    y = writeLine(doc, y, "Perfiles RPBI", profiles || "—");

    y += 6;
    doc.setFont("helvetica", "bold");
    doc.text("Notas del contrato", 14, y);
    y += 5;
    doc.setFont("helvetica", "normal");
    const notes = String(data.notes || contract.notes || "Sin notas registradas.");
    const lines = doc.splitTextToSize(notes, 182);
    doc.text(lines, 14, y);

    return doc;
}

function writeLine(doc, y, label, value) {
    doc.setFont("helvetica", "bold");
    doc.text(`${label}:`, 14, y);
    doc.setFont("helvetica", "normal");
    const lines = doc.splitTextToSize(String(value || "—"), 140);
    doc.text(lines, 55, y);
    return y + Math.max(lines.length * 5, 7);
}

function formatMoney(value) {
    if (value == null || value === "") {
        return "—";
    }

    return new Intl.NumberFormat("es-MX", {
        style: "currency",
        currency: "MXN",
    }).format(Number(value));
}
