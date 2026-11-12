import { jsPDF } from "jspdf";

const MARGIN = 8;

const INCINERACION_COLUMNS = [
    { key: "id", label: "Tratamiento", w: 1.1 },
    { key: "fecha_completa", label: "Fecha", w: 1.3 },
    { key: "hora_carga", label: "Hora de carga", w: 1.1 },
    { key: "hora_termino", label: "Hora de término", w: 1.2 },
    { key: "estado", label: "Estado", w: 1.2 },
    { key: "temp_camara_primaria", label: "Temp. cámara primaria", w: 1.2, numeric: true },
    { key: "temp_camara_secundaria", label: "Temp. cámara secundaria", w: 1.2, numeric: true },
    { key: "temp_entrada_venturi", label: "Temperatura entrada Venturi", w: 1.3, numeric: true },
    { key: "temp_salida_venturi", label: "Temperatura salida Venturi", w: 1.3, numeric: true },
    { key: "temp_salida_chimenea", label: "Temperatura salida chimenea", w: 1.3, numeric: true },
    { key: "oxigeno", label: "O2 (Oxígeno)", w: 1, numeric: true },
    { key: "presion_negativa", label: "Presión negativa (IN H2O)", w: 1.2, numeric: true },
    { key: "consumo_gas_lp", label: "Consumo de gas LP", w: 1.1, numeric: true },
    { key: "consumo_promedio_agua", label: "Consumo promedio de agua", w: 1.2, numeric: true },
    { key: "evaporacion_agua_tratada", label: "Evaporación agua tratada", w: 1.2, numeric: true },
    { key: "patologicos", label: "Patológicos", w: 1, numeric: true },
    { key: "cultivos_cepas", label: "Cultivos y cepas", w: 1.1, numeric: true },
    { key: "sangre", label: "Sangre", w: 0.9, numeric: true },
    { key: "ceniza_generada", label: "Ceniza generada", w: 1, numeric: true },
    { key: "descarga_lavador", label: "Descarga de lavador", w: 1.1, numeric: true },
    { key: "comments", label: "Observaciones", w: 1.6 },
];

const DETALLE_COLUMNS = [
    { key: "tratamiento", label: "Tratamiento", w: 1.1 },
    { key: "fecha", label: "Fecha", w: 1.2 },
    { key: "manifest", label: "Manifiesto", w: 1.2 },
    { key: "client", label: "Cliente", w: 3 },
    { key: "profile", label: "Perfil", w: 2 },
    { key: "weight", label: "Cantidad", w: 1.2, numeric: true },
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

        return y + 8;
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

function drawSignatures(doc, y) {
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
    doc.text("Operador Nombre y Firma", (MARGIN + 8 + leftEnd) / 2, y + 4, { align: "center" });
    doc.text("Reviso Nombre y firma", (rightStart + rightEnd) / 2, y + 4, { align: "center" });
}

function drawIncineracion(doc, section) {
    let y = drawCenter(doc, 12, section.titulo, 12);
    y = drawMeta(
        doc,
        y + 2,
        `Operador: ${section.operador || "—"}`,
        `Periodo: ${section.mes || "—"}`,
    );
    y = drawDataTable(doc, y + 1, INCINERACION_COLUMNS, section.rows, { headerSize: 4 });

    if (!section.detalles?.length) {
        return;
    }

    y += 8;
    if (y + 24 > doc.internal.pageSize.getHeight() - MARGIN) {
        doc.addPage();
        y = MARGIN;
    }

    doc.setFont("helvetica", "bold");
    doc.setFontSize(9);
    doc.text("Cantidades por manifiesto", MARGIN, y);
    drawDataTable(doc, y + 3, DETALLE_COLUMNS, section.detalles, { headerSize: 6, bodySize: 7 });
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

function buildLogbookPdf(data) {
    const doc = new jsPDF({ orientation: "landscape", unit: "mm", format: "a4" });
    let started = false;

    if (data.incineracion) {
        drawIncineracion(doc, data.incineracion);
        started = true;
    }

    if (data.esterilizacion) {
        if (started) {
            doc.addPage();
        }
        drawEsterilizacion(doc, data.esterilizacion);
    }

    doc.setProperties({
        title: "Bitácora de tratamientos",
        author: "BAN RPBI",
        creator: "BAN RPBI",
    });

    const filtro = data.filtro || "tratamientos";
    doc.save(`bitacora-${filtro}.pdf`);
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
