import { jsPDF } from "jspdf";

const MARGIN = 8;

const INCINERACION_COLUMNS = [
    { key: "fecha", label: "FECHA", w: 1.15 },
    { key: "hora", label: "HORA DE CARGA Y PARO DE CARGA", w: 1.35 },
    { key: "temp_camara_primaria", label: "Temp. Cámara primaria °C", w: 1.1, numeric: true },
    { key: "temp_camara_secundaria", label: "Temp. Cámara Secundaria °C", w: 1.1, numeric: true },
    { key: "temp_entrada_venturi", label: "Temperatura Entrada Venturi °C", w: 1.15, numeric: true },
    { key: "temp_salida_venturi", label: "Temperatura Salida Venturi °C", w: 1.15, numeric: true },
    { key: "temp_salida_chimenea", label: "Temperatura Salida Chimenea °C", w: 1.15, numeric: true },
    { key: "cl_en_rps", label: "CL en RPs", w: 0.85, numeric: true },
    { key: "co_ppm", label: "CO/ ppm (Monóxido de carbono)", w: 1.15, numeric: true },
    { key: "oxigeno", label: "O2 (Oxígeno)", w: 0.9, numeric: true },
    { key: "presion_negativa", label: "Presión negativa INH2O", w: 1.05, numeric: true },
    { key: "radiacion_residuos", label: "Radiación en residuos", w: 1.05, numeric: true },
    { key: "consumo_gas_lp", label: "Consumo de gas LP Lts", w: 1.05, numeric: true, total: true },
    { key: "consumo_promedio_agua", label: "Consumo Promedio de Agua m3", w: 1.1, numeric: true, total: true },
    { key: "evaporacion_agua_tratada", label: "Evaporación Agua Tratada m3", w: 1.1, numeric: true, total: true },
    { key: "solidos_mixtos", label: "Solidos Mixtos Generados Kg", w: 1.1, numeric: true, total: true },
    { key: "rp_solidos_lodos", label: "RP (solidos y/o Lodos) Kg.", w: 1.15, numeric: true, total: true },
    { key: "medicamentos_caducos", label: "Medicamentos Caducos Kg.", w: 1.1, numeric: true, total: true },
    { key: "patologicos", label: "Patológicos kg.", w: 1, numeric: true, total: true },
    { key: "cultivos_cepas", label: "Cultivos y Cepas kg.", w: 1, numeric: true, total: true },
    { key: "sangre", label: "Sangre kg.", w: 0.9, numeric: true, total: true },
    { key: "ceniza_generada", label: "Ceniza generada", w: 0.95, numeric: true, total: true },
    { key: "descarga_lavador", label: "Descarga de lavador m3", w: 1.05, numeric: true, total: true },
    { key: "comments", label: "OBSERVACIONES", w: 1.55 },
];

const ESTERILIZACION_COLUMNS = [
    { key: "fecha", label: "FECHA DE CARGA", w: 1.1 },
    { key: "hora_carga", label: "Hora de Carga", w: 1.1 },
    { key: "hora_termino", label: "Hora Termino del Proceso Carga", w: 1.4 },
    { key: "temp_esterilizador", label: "Temperatura del Esterilizador 156°C", w: 1.5, numeric: true },
    { key: "presion_camara_principal", label: "Presión Cámara Principal lb/in2 (50 PSI)", w: 1.6, numeric: true },
    { key: "eliminacion_biologica", label: "Eliminación biológica", w: 1.3, flexible: true },
    { key: "ciclo_carga", label: "Ciclo de Carga/45 Minutos", w: 1.3, flexible: true },
    { key: "rpbi_bi1_cepas", label: "RPBI BI1 cepas y cultivos kg", w: 1.4, numeric: true },
    { key: "rpbi_bi2_punzocortantes", label: "RPBI BI2 Punzocortantes kg", w: 1.4, numeric: true },
    { key: "rpbi_bi4_no_anatomicos", label: "RPBI BI4 No Anatómicos kg", w: 1.4, numeric: true },
    { key: "rpbi_bi5_sangre", label: "RPBI BI5 sangre kg", w: 1.3, numeric: true },
    { key: "total_procesada", label: "Total procesada", w: 1.2, numeric: true },
    { key: "comments", label: "OBSERVACIONES", w: 1.8 },
];

function formatKg(value) {
    const number = Number(value);
    if (!Number.isFinite(number)) {
        return "0,00";
    }

    const negative = number < 0;
    const [whole, fraction] = Math.abs(number).toFixed(2).split(".");
    const grouped = whole.replace(/\B(?=(\d{3})+(?!\d))/g, ".");

    return `${negative ? "-" : ""}${grouped},${fraction}`;
}

function formatValue(value, column) {
    if (value === null || value === undefined || value === "") {
        return column.numeric ? formatKg(0) : "-";
    }

    if (column.numeric) {
        return formatKg(value);
    }

    const text = String(value);
    if (column.flexible && /^-?\d+(\.\d+)?$/.test(text)) {
        return text.includes(".") ? formatKg(text) : text;
    }

    return text.replaceAll("—", "-");
}

function pageInner(doc) {
    return doc.internal.pageSize.getWidth() - MARGIN * 2;
}

function widthsOf(columns, inner) {
    const sum = columns.reduce((total, column) => total + column.w, 0);

    return columns.map((column) => (column.w / sum) * inner);
}

function linesOf(doc, text, width, size, bold) {
    doc.setFont("helvetica", bold ? "bold" : "normal");
    doc.setFontSize(size);
    const lines = doc.splitTextToSize(String(text ?? ""), Math.max(4, width - 1.4));

    return lines.length ? lines : [""];
}

function textLineHeight(size) {
    return size * 0.42;
}

function rowHeightFor(doc, widths, values, size, bold) {
    let maxLines = 1;

    values.forEach((value, index) => {
        maxLines = Math.max(maxLines, linesOf(doc, value, widths[index], size, bold).length);
    });

    return Math.max(5.2, maxLines * textLineHeight(size) + 1.6);
}

function drawCell(doc, x, y, w, h, text, size, bold, fill) {
    doc.setDrawColor(0);
    doc.setLineWidth(0.15);

    if (fill) {
        doc.setFillColor(fill[0], fill[1], fill[2]);
        doc.rect(x, y, w, h, "FD");
    } else {
        doc.rect(x, y, w, h);
    }

    const lines = linesOf(doc, text, w, size, bold);
    const block = lines.length * textLineHeight(size);
    const startY = y + Math.max(0.7, (h - block) / 2);
    doc.text(lines, x + w / 2, startY, { align: "center", baseline: "top" });
}

function drawUniformRow(doc, y, widths, values, size, bold, fill) {
    const height = rowHeightFor(doc, widths, values, size, bold);
    let x = MARGIN;

    values.forEach((value, index) => {
        drawCell(doc, x, y, widths[index], height, value, size, bold, fill);
        x += widths[index];
    });

    return y + height;
}

function ensureSpace(doc, y, needed, drawHeader) {
    if (y + needed <= doc.internal.pageSize.getHeight() - MARGIN) {
        return y;
    }

    doc.addPage();

    return drawHeader(MARGIN);
}

function drawDataTable(doc, y, columns, rows, options = {}) {
    const widths = widthsOf(columns, pageInner(doc));
    const headerSize = options.headerSize ?? 4.2;
    const bodySize = options.bodySize ?? 6;

    const drawHeader = (startY) => {
        let cursor = startY;

        if (options.drawGroups) {
            cursor = options.drawGroups(doc, cursor, widths);
        }

        if (options.skipLabels) {
            return cursor;
        }

        return drawUniformRow(
            doc,
            cursor,
            widths,
            columns.map((column) => column.label),
            headerSize,
            true,
        );
    };

    y = drawHeader(y);

    if (rows.length === 0) {
        doc.setFont("helvetica", "italic");
        doc.setFontSize(8);
        doc.text("Sin tratamientos.", MARGIN, y + 5);
        y += 8;
    }

    rows.forEach((row) => {
        const values = columns.map((column) => formatValue(row[column.key], column));
        const height = rowHeightFor(doc, widths, values, bodySize, false);
        y = ensureSpace(doc, y, height, drawHeader);
        y = drawUniformRow(doc, y, widths, values, bodySize, false);
    });

    if (options.drawFooter) {
        y = options.drawFooter(doc, y, widths, drawHeader);
    }

    return y;
}

function drawCenter(doc, y, text, size) {
    doc.setFont("helvetica", "bold");
    doc.setFontSize(size);
    doc.text(text, doc.internal.pageSize.getWidth() / 2, y, { align: "center" });

    return y + size * 0.5;
}

function drawMeta(doc, y, left, right) {
    doc.setFont("helvetica", "normal");
    doc.setFontSize(8);
    doc.text(left, MARGIN, y);
    doc.text(right, doc.internal.pageSize.getWidth() - MARGIN, y, { align: "right" });

    return y + 5;
}

function drawEsterilizacionGroups(doc, y, widths) {
    const groups = [
        { start: 0, end: 3, label: "" },
        { start: 3, end: 7, label: "Parámetros de Medición" },
        { start: 7, end: 11, label: "Residuos procesados" },
        { start: 11, end: 13, label: "" },
    ];
    const height = 5;

    groups.forEach((group) => {
        const x = MARGIN + widths.slice(0, group.start).reduce((total, width) => total + width, 0);
        const w = widths.slice(group.start, group.end).reduce((total, width) => total + width, 0);
        drawCell(doc, x, y, w, height, group.label, 6.5, true);
    });

    return y + height;
}

function drawEsterilizacionTotal(doc, y, widths, drawHeader, section) {
    const height = 7;
    const cursor = ensureSpace(doc, y, height, drawHeader);
    const labelWidth = widths.slice(0, 7).reduce((total, width) => total + width, 0);
    const values = [
        formatKg(section.totales.bi1),
        formatKg(section.totales.bi2),
        formatKg(section.totales.bi4),
        formatKg(section.totales.bi5),
        formatKg(section.totales.total),
        "",
    ];
    const fill = [198, 224, 245];

    drawCell(doc, MARGIN, cursor, labelWidth, height, section.fila_total, 6, true, fill);

    let x = MARGIN + labelWidth;
    values.forEach((value, index) => {
        drawCell(doc, x, cursor, widths[index + 7], height, value, 6, true, fill);
        x += widths[index + 7];
    });

    return cursor + height;
}

function drawIncineracionHeader(doc, y, widths) {
    const bands = [6.2, 6.2, 10];
    const spans = [
        { c0: 0, c1: 1, r0: 0, r1: 3, label: "FECHA", size: 4.2 },
        { c0: 1, c1: 2, r0: 0, r1: 3, label: "HORA DE CARGA Y PARO DE CARGA", size: 3.6 },
        { c0: 2, c1: 15, r0: 0, r1: 1, label: "PARAMETROS DE MEDICION", size: 5 },
        { c0: 15, c1: 21, r0: 0, r1: 1, label: "RESIDUOS INCINERADOS", size: 5 },
        { c0: 21, c1: 23, r0: 0, r1: 1, label: "", size: 4 },
        { c0: 23, c1: 24, r0: 0, r1: 3, label: "OBSERVACIONES", size: 4 },
        { c0: 2, c1: 7, r0: 1, r1: 2, label: "Monitoreo de temperaturas", size: 4.4 },
        { c0: 7, c1: 12, r0: 1, r1: 2, label: "Monitoreo de Parámetros", size: 4.4 },
        { c0: 12, c1: 15, r0: 1, r1: 2, label: "Consumos", size: 4.4 },
        { c0: 15, c1: 19, r0: 1, r1: 2, label: "RP's", size: 4.4 },
        { c0: 19, c1: 21, r0: 1, r1: 2, label: "RPBI'S", size: 4.4 },
        { c0: 21, c1: 23, r0: 1, r1: 2, label: "Residuos Generados de Proceso", size: 3.8 },
    ];

    INCINERACION_COLUMNS.forEach((column, index) => {
        if (index < 2 || index > 22) {
            return;
        }

        spans.push({ c0: index, c1: index + 1, r0: 2, r1: 3, label: column.label, size: 3.3 });
    });

    const yOf = (row) => y + bands.slice(0, row).reduce((total, height) => total + height, 0);
    const xOf = (column) => MARGIN + widths.slice(0, column).reduce((total, width) => total + width, 0);
    const wOf = (start, end) => widths.slice(start, end).reduce((total, width) => total + width, 0);
    const hOf = (start, end) => bands.slice(start, end).reduce((total, height) => total + height, 0);

    spans.forEach((span) => {
        drawCell(
            doc,
            xOf(span.c0),
            yOf(span.r0),
            wOf(span.c0, span.c1),
            hOf(span.r0, span.r1),
            span.label,
            span.size,
            true,
        );
    });

    return y + bands.reduce((total, height) => total + height, 0);
}

function drawIncineracionTotal(doc, y, widths, drawHeader, rows) {
    const height = 6;
    const cursor = ensureSpace(doc, y, height, drawHeader);
    const firstTotal = INCINERACION_COLUMNS.findIndex((column) => column.total);
    const labelWidth = widths.slice(0, firstTotal).reduce((total, width) => total + width, 0);
    const fill = [198, 224, 245];

    drawCell(doc, MARGIN, cursor, labelWidth, height, "TOTALES", 6, true, fill);

    let x = MARGIN + labelWidth;
    INCINERACION_COLUMNS.forEach((column, index) => {
        if (index < firstTotal) {
            return;
        }

        const value = column.total
            ? formatKg(rows.reduce((total, row) => total + (Number(row[column.key]) || 0), 0))
            : "";
        drawCell(doc, x, cursor, widths[index], height, value, 5.5, true, fill);
        x += widths[index];
    });

    return cursor + height;
}

function drawSignatures(doc, y, operator = "") {
    if (y + 24 > doc.internal.pageSize.getHeight() - MARGIN) {
        doc.addPage();
        y = MARGIN;
    }

    y += 16;
    const mid = doc.internal.pageSize.getWidth() / 2;
    const leftEnd = mid - 12;
    const rightStart = mid + 12;
    const rightEnd = doc.internal.pageSize.getWidth() - MARGIN;

    doc.setDrawColor(0);
    doc.setLineWidth(0.2);
    doc.line(MARGIN + 8, y, leftEnd, y);
    doc.line(rightStart, y, rightEnd, y);
    doc.setFont("helvetica", "normal");
    doc.setFontSize(8);
    if (operator) {
        doc.text(operator, (MARGIN + 8 + leftEnd) / 2, y - 2, { align: "center" });
    }
    doc.text("Operador Nombre y Firma", (MARGIN + 8 + leftEnd) / 2, y + 4, { align: "center" });
    doc.text("Reviso Nombre y firma", (rightStart + rightEnd) / 2, y + 4, { align: "center" });
}

function drawIncineracionBanner(doc, y, section) {
    const width = pageInner(doc);
    const height = 10;
    doc.setDrawColor(0);
    doc.setLineWidth(0.15);
    doc.rect(MARGIN, y, width, height);
    doc.setFont("helvetica", "bold");
    doc.setFontSize(8);
    doc.text("AUTORIZACION", MARGIN + 3, y + 6);
    doc.text(section.autorizacion || "", MARGIN + 36, y + 6);
    doc.setFontSize(9);
    doc.text(section.titulo, doc.internal.pageSize.getWidth() / 2 + 10, y + 6, { align: "center" });
    doc.setFontSize(8);
    doc.text(`MES: ${section.mes || ""}`, MARGIN + width - 2, y + 6, { align: "right" });

    return y + height;
}

function drawIncineracion(doc, section) {
    let y = drawIncineracionBanner(doc, MARGIN, section);
    y = drawDataTable(doc, y, INCINERACION_COLUMNS, section.rows, {
        bodySize: 5.2,
        skipLabels: true,
        drawGroups: drawIncineracionHeader,
        drawFooter: (pdf, cursor, widths, drawHeader) => drawIncineracionTotal(pdf, cursor, widths, drawHeader, section.rows),
    });
    drawSignatures(doc, y, section.operador || "");
}

function drawEsterilizacion(doc, section) {
    let y = drawCenter(doc, 12, section.titulo, 11);
    y = drawCenter(doc, y + 1, `AUTORIZACION ${section.autorizacion || ""}`.trim(), 8);
    y = drawMeta(
        doc,
        y + 2,
        `Nombre del Operador: ${section.operador || ""}`,
        `MES: ${section.mes || ""}`,
    );
    y = drawDataTable(doc, y + 1, ESTERILIZACION_COLUMNS, section.rows, {
        headerSize: 4.6,
        bodySize: 6.5,
        drawGroups: drawEsterilizacionGroups,
        drawFooter: (pdf, cursor, widths, drawHeader) => drawEsterilizacionTotal(pdf, cursor, widths, drawHeader, section),
    });
    drawSignatures(doc, y);
}

function buildSectionPdf(draw, section) {
    const doc = new jsPDF({ orientation: "landscape", unit: "mm", format: "a4" });
    draw(doc, section);
    doc.setProperties({
        title: section.titulo || "Bitácora de tratamientos",
        author: "BAN RPBI",
        creator: "BAN RPBI",
    });

    return doc;
}

function openPdf(doc) {
    const url = URL.createObjectURL(doc.output("blob"));
    const link = document.createElement("a");
    link.href = url;
    link.target = "_blank";
    link.rel = "noopener";
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.setTimeout(() => URL.revokeObjectURL(url), 60000);
}

function buildLogbookPdf(data) {
    const files = [];

    if (data.incineracion) {
        files.push(buildSectionPdf(drawIncineracion, data.incineracion));
    }

    if (data.esterilizacion) {
        files.push(buildSectionPdf(drawEsterilizacion, data.esterilizacion));
    }

    files.forEach((doc) => {
        openPdf(doc);
    });
}

function bindFilters() {
    document.querySelectorAll("[data-logbook-filter]").forEach((link) => {
        if (link.dataset.bound === "1") {
            return;
        }

        link.dataset.bound = "1";
        link.addEventListener("click", (event) => {
            const form = document.getElementById("logbook-form");
            if (!form) {
                return;
            }

            event.preventDefault();
            const params = new URLSearchParams();
            params.set("filtro", link.dataset.logbookFilter);
            const dateFrom = form.querySelector("[name=date_from]")?.value;
            const dateTo = form.querySelector("[name=date_to]")?.value;

            if (dateFrom) {
                params.set("date_from", dateFrom);
            }

            if (dateTo) {
                params.set("date_to", dateTo);
            }

            window.location = `${form.action}?${params.toString()}`;
        });
    });
}

function bindGenerate() {
    const button = document.getElementById("generar-bitacora");
    if (!button || button.dataset.bound === "1") {
        return;
    }

    button.dataset.bound = "1";
    button.addEventListener("click", async (event) => {
        event.preventDefault();
        const form = button.form;
        const params = new URLSearchParams(new FormData(form));
        params.set("generate", "1");

        try {
            const response = await fetch(`${form.action}?${params.toString()}`, {
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
                credentials: "same-origin",
            });

            if (!response.ok) {
                submitGenerate(form);
                return;
            }

            buildLogbookPdf(await response.json());
        } catch (error) {
            submitGenerate(form);
        }
    });
}

function submitGenerate(form) {
    const input = document.createElement("input");
    input.type = "hidden";
    input.name = "generate";
    input.value = "1";
    form.appendChild(input);
    form.submit();
}

bindFilters();
bindGenerate();

if (window.__logbookPdf) {
    buildLogbookPdf(window.__logbookPdf);
}
