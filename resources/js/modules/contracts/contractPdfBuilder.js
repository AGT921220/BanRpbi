/**
 * Reproduce el CONTRATO_RPBI'S.doc (datos fijos y campos vacíos).
 * Por ahora no sustituye campos con información del sistema.
 */
export function buildContractPdf(doc, data = {}) {
    const M = {
        left: 54,
        right: 47,
        top: 36,
        bottom: 36,
        indent: 18,
        listIndent: 18,
        fontSize: 8.5,
        lineHeight: 9.2,
        pageWidth: 612,
        pageHeight: 792,
    };
    M.contentWidth = M.pageWidth - M.left - M.right;
    const client = data.client || {};
    const clientName = client.name;
    const clientStreet = String(client.street ?? "").trim() || "";
    const clientLabel = String(clientName ?? "").trim() || "NOMBRE DEL CLIENTE";
    const issuer = data.issuer || {};
    const issuerStreet = issuerStreetLine(issuer);
    const issuerColony = issuerValue(issuer, "colony", "Cd. Industrial");
    const issuerCityLine = [
        issuerValue(issuer, "city", "Tijuana"),
        issuerValue(issuer, "state", "Baja California"),
    ]
        .filter(Boolean)
        .join(", ");
    const issuerCountryLine = [
        issuerValue(issuer, "country", "México"),
        issuerValue(issuer, "postal_code", "22444"),
    ]
        .filter(Boolean)
        .join(" ");
    const issuerAddress = [
        issuerStreet,
        issuerColony,
        issuerCityLine,
        issuerCountryLine,
    ]
        .filter(Boolean)
        .join(", ");
    doc.setFont("helvetica", "normal");
    doc.setFontSize(M.fontSize);

    let y = M.top - 5;

    // —— Título ——
    doc.setFont("helvetica", "bold");
    doc.setFontSize(10);
    const title = "CONTRATO DE PRESTACION DE SERVICIOS";
    doc.text(title, M.pageWidth / 2, y, { align: "center" });
    const titleW = doc.getTextWidth(title);
    doc.setLineWidth(0.6);
    doc.line(
        (M.pageWidth - titleW) / 2,
        y + 1.5,
        (M.pageWidth + titleW) / 2,
        y + 1.5,
    );
    y += 28;

    doc.setFont("helvetica", "normal");
    doc.setFontSize(M.fontSize);

    // —— Introducción (hueco vacío tras RFC del cliente, como en el DOC) ——
    y = writeMixedPara(
        doc,
        y,
        M,
        [
            {text: "Que celebran, por una parte aquí en adelante: "},
            {
                text: "“EL CLIENTE“", bold: true},
            {
                text: ", y con Registro Federal de Contribuyentes ",
            },
            {
                text: (clientName || "NOMBRE DEL CLIENTE").toUpperCase(),
                bold: true,
            },
            {text: ", en lo sucesivo: "},
            {text: "“LA PRESTADORA DE SERVICIOS”", bold: true},
            {
                text: ", representada por el Ing. Wilber Galaviz en su carácter de Director General, de acuerdo a las siguientes:",
            },
        ],
        { firstIndent: M.indent },
    );

    y += M.lineHeight;
    y = writeSpacedHeader(doc, y, M, "DECLARACIONES");

    y += M.lineHeight * 0.5;
    //AQUI
    y = writeMixedPara(
        doc,
        y,
        M,
        [
            { text: "I.- Declara " },
            { text: "“LA PRESTADORA DE SERVICIOS”", bold: true },
            { text: " que:" },
        ],
        { firstIndent: M.indent+15 },
    );

    y+= M.lineHeight * 0.8;
    y = writeLetterItem(
        doc,
        y,
        M,
        "a)",
        "El Ing. Marcelo Hinojosa Ornelas, señala que su representada es una Sociedad Mexicana, legalmente constituida conforme a las leyes de la República Mexicana, como consta en la escritura pública No. 54706, volumen 1124 del 4 de Octubre de 2000, ante la fe del notario público Nº 6, Lic. Alma E. Andrade Marín De Tijuana, B.C. e inscrita en el Registro Público de la Propiedad y el Comercio de Tijuana, B.C., bajo partida 5231665 sec. Comercio.",
    );

    y = writeLetterItem(
        doc,
        y,
        M,
        "b)",
        "Que dentro del objetivo social de su representada, se encuentra la implantación y operación de sistemas de recolección, transporte, almacenamiento, tratamiento, transformación, importación, exportación y disposición de residuos peligrosos biológico-infecciosos y Patológicos, provenientes de actividades médicas, veterinarias, de investigación, analíticas, industriales y comerciales.",
    );

    y = writeLetterItem(doc, y, M, "c)", [
        {
            text: "Que cuenta con Autorización de operación ante el Secretaria de Medio Ambiente y Recursos Naturales ",
        },
        {
            text: issuerValue(issuer, "autorizacion_operacion", "No 02-004-GM-I-36 D/06"),
            bold: true,
        },
        { text: ", para la " },
        {
            text: issuerValue(issuer, "recoleccion_transporte", "recolección y transporte"),
            bold: true,
        },
        {
            text: " de los residuos peligrosos biológico-infecciosos, La Autorización No. ",
        },
        {
            text: issuerValue(issuer, "autorizacion_almacenamiento", "02-004-PS-II-13 D-05"),
            bold: true,
        },
        {
            text: " para el almacenamiento de residuos peligrosos biológicos-infecciosos y Autorización No, ",
        },
        {
            text: issuerValue(issuer, "autorizacion_tratamiento", "02-04-PS-V-71-2006"),
            bold: true,
        },
        { text: ", para el " },
        {
            text: issuerValue(
                issuer,
                "tratamiento_residuos",
                "tratamiento de residuos peligrosos biológico infecciosos",
            ),
            bold: true,
        },
        { text: ", descritos en el punto anterior." },
    ]);

    y = writeLetterItem(
        doc,
        y,
        M,
        "d)",
        "Que su representada cuenta con la experiencia, el equipo, instalaciones, vehículos, pólizas de seguro, personal capacitado para la prestación de los servicios, materia del presente contrato.",
    );

    y = writeLetterItem(
        doc,
        y,
        M,
        "e)",
        "El Ing. Marcelo Hinojosa Ornelas, afirma que en su carácter de representante debidamente autorizado, cuenta con las facultades necesarias para obligar a su representada y lo hace en los términos de este contrato.",
    );

    y += M.lineHeight * 0.4;
    y = writePara(doc, y, M, "II.- Declara “EL CLIENTE” que:", {
        firstIndent: M.indent,
    });

    y = writeMixedPara(
        doc,
        y,
        M,
        [
            { text: "El C. " },
            { text: clientLabel, bold: true },
            {
                text: ", señala que su representada, es una sociedad debidamente constituida conforme a las leyes de la República Mexicana, con Registro Federal de Contribuyentes ",
            },
            { text: "PENDIENTE", bold: true }, //PENDIENTE
            {
                text: ". Que el generador, tiene la necesidad de los servicios de recolección, transporte, almacenamiento, tratamiento, y/o disposición final, de sus residuos peligrosos con características biológico-infecciosas, provenientes de servicios médicos.",
            },
        ],
        { label: "a)" },
    );

    y = writeLetterItem(doc, y, M, "b)", [
        {
            text: "Que en su carácter de representante debidamente autorizado, cuenta con las facultades suficientes, para contratar con ",
        },
        { text: clientLabel, bold: true },
        {
            text: ", para la prestación de los servicios mencionados en el punto anterior, obligando a la Sociedad en virtud del mismo.",
        },
    ]);

    y += M.lineHeight * 0.4;
    y = writePara(
        doc,
        y,
        M,
        "III.- Las partes manifiestan tener conocimiento y estar conformes en que las declaraciones anteriores, son ciertas y que en Virtud de lo cual, es su voluntad celebrar el presente contrato, conforme a las siguientes:",
        { firstIndent: M.indent },
    );

    y += M.lineHeight * 0.6;
    y = writeSpacedHeader(doc, y, M, "CLAUSULAS");

    y += M.lineHeight * 0.5;
    y = writePara(doc, y, M, "POR PARTE DE “LA PRESTADORA DE SERVICIOS”", {
        firstIndent: M.indent,
    });

    y += M.lineHeight * 0.3;
    y = writeClause(
        doc,
        y,
        M,
        "PRIMERA.-",
        "Se compromete con “EL CLIENTE”, a prestar los servicios de recolección, transporte, tratamiento, almacenamiento y disposición final de sus residuos peligrosos biológico-infecciosos, de acuerdo a los lineamientos establecidos por las Normas Oficiales Mexicanas, Ley General del Equilibrio Ecológico y Protección al Medio Ambiente y las aplicables.",
    );

    y = writeClause(
        doc,
        y,
        M,
        "SEGUNDA.-",
        "Descripción de los servicios que serán proporcionados a “EL CLIENTE”:",
    );

    y = writeLetterItem(
        doc,
        y,
        M,
        "a)",
        "Orientación en la utilización de los contenedores, separación, clasificación y etiquetado de los residuos peligrosos con características biológico-infecciosas.",
    );

    y = writeLetterItem(
        doc,
        y,
        M,
        "b)",
        "Recolección de los residuos peligrosos biológico-infecciosos.",
    );

    y = writeMixedPara(
        doc,
        y,
        M,
        [
            {
                text: "Transporte de los residuos peligrosos biológico-infecciosos desde las instalaciones de “EL CLIENTE”, localizadas en calle ",
            },
            { text: clientStreet, bold: true },
            {text: ". de la ciudad de Tijuana, B.C, "},
            {text:"“LA PRESTADORA DE SERVICIOS”", bold: true},
            { text: ", hasta la planta de  en " },
            { text: issuerAddress, bold: true },
            {
                text: " y/o a cualquier otra instalación, previamente autorizada para tales fines por las autoridades competentes.",
            },
        ],
        { label: "c)" },
    );

    y = writeLetterItem(
        doc,
        y,
        M,
        "d)",
        "Tratamiento por esterilización a los residuos peligrosos con características biológicas infecciosas.",
    );

    y = writeLetterItem(
        doc,
        y,
        M,
        "e)",
        "Disposición final de los residuos, ya esterilizados y considerados en este punto como no peligrosos, para su envío al relleno sanitario de la ciudad de Tijuana, B. C.",
    );

    y = writeMixedPara(
        doc,
        y,
        M,
        [
            {
                text: "Otorgará en comodato el contenedor o los contenedores necesarios para que ahí almacenen sus bolsas rojas conteniendo los residuos peligrosos biológico-infecciosos, los contenedores rígidos rojos para depositar los residuos peligrosos biológico-infecciosos punzo cortantes, que se generen en sus instalaciones; para con esto cumplir con las Normas Oficiales Mexicanas aplicables. Se entregara en el transcurso del mes la cantidad de: ",
            },
            { text: "PENDIENTE", bold:true }, //PENDIENTE
            {
                text: " contenedor rojo de litro para residuos punzo cortantes y ",
            },
            { text: "PENDIENTE", bold:true }, //PENDIENTE
            { text: " bolsas rojas cal. 200 de " },
            { text: "PENDIENTE", bold:true }, //PENDIENTE
            {
                text: " galones para residuos no anatómicos, según lo indica la Norma Oficial Mexicana correspondiente.",
            },
        ],
        { clause: "TERCERA.-" },
    );

    y = writeClause(
        doc,
        y,
        M,
        "CUARTA.-",
        "Proporcionará a “ EL CLIENTE ”, el manifiesto de entrega, transporte, recepción de los residuos peligrosos biológico-infecciosos, debidamente firmado por el generador, el transportista y el destinatario, a los 30 días naturales contados a partir de la fecha de recolección, previo pago de la factura correspondiente.",
    );

    // —— Página 2 ——
    doc.addPage();
    y = 22;

    y = writeMixedPara(
        doc,
        y,
        M,
        [
            {
                text: "La frecuencia de recolección queda sujeta según la clasificación en la NOM-087-SEMARNAT-SSA1-2002, los establecimientos generadores de acuerdo al punto 5.1; y del periodo de almacenamiento temporal considerado con el criterio que se establece en la tabla del nivel ",
            },
            { text: " III ", bold: true }, //PENDIENTE
            {
                text: " indicado en el punto 6.3.3 y estableciendo como máximo de generación kg. Mensuales y un costo de ",
            },
            { text: "PENDIENTE", bold: true }, //PENDIENTE
            {
                text: " por cada kilogramo excedente; “LA PRESTADORA DE SERVICIOS” se compromete a realizar la recolección de los residuos peligrosos biológico-infecciosos dentro de esta Normatividad. “EL CLIENTE” deberá sujetarse a los puntos establecidos de acuerdo a la clasificación correspondiente. “LA PRESTADORA DE SERVICIOS” tiene la responsabilidad de asignar a “EL CLIENTE” los días de la semana en que por concepto de ruta le corresponda dicho servicio.",
            },
        ],
        { clause: "QUINTA.-" },
    );

    y += M.lineHeight * 0.4;
    y = writePara(doc, y, M, "POR PARTE DE “EL CLIENTE”:", {
        firstIndent: M.indent,
    });

    y = writeClause(
        doc,
        y,
        M,
        "SEXTA.-",
        "Se obliga como comodatario del contenedor o los contenedores para residuos peligrosos biológico-infecciosos mismos que le serán entregados para el almacenamiento de dichos residuos.",
    );

    y = writeClause(
        doc,
        y,
        M,
        "SEPTIMA.-",
        "Mantener los contenedores que le hayan sido proporcionados para almacenar los residuos peligrosos biológico-infecciosos, dentro de sus instalaciones, perfectamente cerrados, así como disponibles en un lugar de fácil acceso y dar las mayores facilidades al personal de “ LA PRESTADORA DE SERVICIOS ” para su carga y transporte.",
    );

    y = writeClause(
        doc,
        y,
        M,
        "OCTAVA.-",
        "Utilizar los contenedores proporcionados por “LA PRESTADORA DE SERVICIOS”, exclusivamente para depositar los residuos peligrosos biológico-infecciosos que se generen en sus instalaciones; esto excluye por consiguiente, los residuos corrosivos, reactivos, explosivos, tóxicos, inflamables, o de cualquier otra índole, lo cual ocasionaría la suspensión del servicio.",
    );

    y = writeClause(
        doc,
        y,
        M,
        "NOVENA.-",
        "Mantener los contenedores que se entregan en comodato en perfectas condiciones, esto es: sin deformaciones, perforaciones, raspaduras, fracturas, quemaduras, etc. en cuyo caso se obligan a cubrir el costo de reposición.",
    );

    y = writeClause(
        doc,
        y,
        M,
        "DECIMA.-",
        "Como contraprestación “EL CLIENTE”, se obliga a pagar a “LA PRESTADORA DE SERVICIOS”, una cuota mensual por la prestación de servicio de recolección y tratamiento la cantidad de: $ 80.00 (OCHENTA DOLARES 00/100) más IVA o su equivalente al tipo de cambio que rige el día. Nuestros servicios incluyen: recolección, transporte, almacenamiento y tratamiento de sus residuos peligrosos biológico-infecciosos.",
    );

    y += M.lineHeight * 0.3;
    y = writePara(doc, y, M, "GENERALES:", { firstIndent: M.indent });

    y = writeMixedPara(
        doc,
        y,
        M,
        [
            {
                text: "El presente contrato entrará en vigor, a partir del día ",
            },
            { text: data.start_date, bold: true }, 
            { text: ", venciendo el día " },
            { text: data.end_date, bold: true },
            { text: ", por el años" },
//            { text: "    ", highlight: true, blank: true }, //PENDIENTE
            {
                text: " años, se mantendrá el costo del servicio con las condiciones mencionadas en la cláusula Décima, salvo los arreglos económicos que por incrementos de insumos, devaluación, incrementos al salario, modificaciones en la frecuencia de recolección, etc., obliguen a incrementar el costo del servicio, para lo cual se haría una nueva negociación.",
            },
        ],
        { clause: "DECIMOPRIMERA.-" },
    );

    y = writeClause(
        doc,
        y,
        M,
        "DECIMOSEGUNDA.-",
        "Será causa de rescisión del presente contrato, además de las mencionadas por la ley, las siguientes:",
    );

    y = writeLetterItem(
        doc,
        y,
        M,
        "a)",
        "La violación de cualquiera de las cláusulas del presente contrato.",
    );
    y = writeLetterItem(
        doc,
        y,
        M,
        "b)",
        "La disolución, liquidación, la intervención y/o suspensión de pagos o quiebra de cualquiera de las partes.",
    );
    y = writeLetterItem(
        doc,
        y,
        M,
        "c)",
        "La prerrogativa por parte de “EL CLIENTE” de cancelar el contrato, antes del término establecido deberá hacerlo por escrito con acuse de recibo con 30 días de anticipación.",
    );

    y = writeClause(
        doc,
        y,
        M,
        "DECIMOTERCERA.-",
        "Todas las notificaciones y demás correspondencia entre las partes, deberán ser hechas por escrito y con firma de recibido, para lo cual, señalan como domicilios para recibir notificaciones, los siguientes:",
    );

    y += M.lineHeight * 0.4;
    y = writeAddressBlock(doc, y, M, "MAYPA", [
        ["", clientLabel, true],
        ["Nombre:", "Ing. Marcelo Hinojosa Ornelas."],
    ]);
    y = writeLabeledBold(doc, y, M, "Domicilio:", issuerStreet);
    y = writeLabeledBold(doc, y, M, "Fraccionamiento:", issuerColony);
    y = writeLabeledBold(doc, y, M, "Ciudad:", issuerCityLine);
    y = writeLabeledBold(doc, y, M, "País:", issuerCountryLine);

    y += M.lineHeight * 0.6;
    //PENDIENTE — domicilio EL CLIENTE (Nombre, Domicilio, Fraccionamiento, Ciudad)
    y = writeAddressBlock(
        doc,
        y,
        M,
        "EL CLIENTE",
        [
            ["Nombre:", "PENDIENTE"],
            ["Domicilio:", "PENDIENTE"],
            ["Fraccionamiento:", "PENDIENTE"],
            ["Ciudad:", "PENDIENTE"],
        ],
        { blankValues: true },
    );

    y += M.lineHeight * 0.5;
    y = writePara(
        doc,
        y,
        M,
        "En el caso de cambio de domicilio de alguna de las partes, deberá dar aviso a la otra parte, por escrito con 15 días de anticipación.",
        { firstIndent: M.indent },
    );

    y = writeClause(
        doc,
        y,
        M,
        "DECIMOCUARTA.-",
        "Para la interpretación y cumplimiento del presente contrato, “AMBAS PARTES” se someten a la jurisdicción de la ciudad de Tijuana, B.C. renunciando a cualquier otro fuero, que por razones de domicilio, pudiera corresponderles.",
    );

    y += M.lineHeight * 0.5;
    y = writeMixedPara(
        doc,
        y,
        M,
        [
            {
                text: "Se firma el presente contrato en la ciudad de Tijuana, B. C. ",
            },
            // { text: "______________________.", highlight: true, blank: true }, //PENDIENTE
        ],
        { firstIndent: M.indent },
    );

    // —— Firmas (como en el DOC: cliente izq. / Maypa der. / testigo centro) ——
    y += M.lineHeight * 1.8;
    doc.setFont("helvetica", "bold");
    doc.setFontSize(M.fontSize);
    doc.text(clientLabel, 460, y, {
        align: "center",
    });

    y += M.lineHeight * 2.8;
    const sigLeft = 90;
    const sigRight = 360;
    const sigW = 170;
    doc.setLineWidth(0.5);
    doc.line(sigLeft, y, sigLeft + sigW, y);
    doc.line(sigRight, y, sigRight + sigW, y);

    y += M.lineHeight;
    doc.setFont("helvetica", "normal");
    doc.text("Ing. Marcelo Hinojosa O.", sigRight + sigW / 2, y, {
        align: "center",
    });
    y += M.lineHeight;
    doc.text("Representante Legal", sigLeft + sigW / 2, y, { align: "center" });
    doc.text("Director General", sigRight + sigW / 2, y, { align: "center" });

    y += M.lineHeight * 2;
    doc.setFont("helvetica", "bold");
    doc.text("TESTIGO", M.pageWidth / 2, y, { align: "center" });
    y += M.lineHeight * 2;
    const tw = 150;
    doc.setFont("helvetica", "normal");
    doc.line((M.pageWidth - tw) / 2, y, (M.pageWidth + tw) / 2, y);
    y += M.lineHeight;
    doc.text("Ing. Gonzalo Colin Villanueva.", M.pageWidth / 2, y, {
        align: "center",
    });

    return doc;
}

function writeSpacedHeader(doc, y, M, word) {
    doc.setFont("helvetica", "bold");
    doc.setFontSize(M.fontSize);
    const spaced = word.split("").join(" ");
    doc.text(spaced, M.pageWidth / 2, y, { align: "center" });
    const w = doc.getTextWidth(spaced);
    doc.setLineWidth(0.5);
    doc.line((M.pageWidth - w) / 2, y + 1.2, (M.pageWidth + w) / 2, y + 1.2);
    doc.setFont("helvetica", "normal");
    return y + M.lineHeight;
}

function writeClause(doc, y, M, title, body) {
    return writeMixedPara(
        doc,
        y,
        M,
        [{ text: `${title} `, bold: true }, { text: body }],
        { firstIndent: M.indent },
    );
}

function issuerStreetLine(issuer) {
    const street = issuerValue(issuer, "street", "Av. Maquiladoras");
    const numExt = issuerValue(issuer, "num_ext", "3458-B");

    if (!numExt || street.includes(numExt)) {
        return street;
    }

    return `${street} ${numExt}`.trim();
}

function writeLabeledBold(doc, y, M, label, value) {
    return writeMixedPara(
        doc,
        y,
        M,
        [{ text: `${label} ` }, { text: value, bold: true }],
        { firstIndent: M.indent },
    );
}

function issuerValue(issuer, key, fallback) {
    const value = issuer?.[key];

    if (value === undefined || value === null || String(value).trim() === "") {
        return fallback;
    }

    return String(value);
}

function writeLetterItem(doc, y, M, label, body) {
    const segments = Array.isArray(body) ? body : [{ text: body }];

    return writeMixedPara(doc, y, M, segments, { label });
}

function writePara(doc, y, M, text, opts = {}) {
    return writeMixedPara(doc, y, M, [{ text }], opts);
}

function writeAddressBlock(doc, y, M, title, rows, opts = {}) {
    doc.setFont("helvetica", "bold");
    doc.setFontSize(M.fontSize);
    doc.text(title, M.left + M.indent, y);
    y += M.lineHeight;

    const labelW = 95;
    const valueX = M.left + M.indent + labelW;

    for (const [label, value, bold] of rows) {
        doc.setFont("helvetica", "normal");
        doc.setFontSize(M.fontSize);
        if (label) {
            doc.text(label, M.left + M.indent, y);
        }
        if (opts.blankValues && !value) {
            drawHighlight(doc, valueX, y - 7, 200, 9);
        } else if (value) {
            if (bold) {
                doc.setFont("helvetica", "bold");
            }
            doc.text(value, label ? valueX : M.left + M.indent, y);
        }
        y += M.lineHeight;
    }

    return y;
}

/**
 * Escribe un párrafo con segmentos (negrita / resaltado / texto) y justificación.
 */
function writeMixedPara(doc, y, M, segments, opts = {}) {
    const firstIndent = opts.firstIndent || 0;
    const label = opts.label || "";
    const clause = opts.clause || "";

    // Prefijo de cláusula o lista
    const prefixParts = [];
    if (clause) {
        prefixParts.push({ text: `${clause} `, bold: true });
    }
    const allSegs = [...prefixParts, ...segments];

    const words = flattenToWords(doc, M, allSegs);
    const labelW = label ? doc.getTextWidth(`${label}  `) : 0;
    const left0 = M.left + (label ? M.listIndent : 0);
    const leftCont = label ? left0 + labelW : M.left;
    const firstLeft = label ? leftCont : M.left + firstIndent;

    let lineWords = [];
    let lineWidth = 0;
    let isFirstLine = true;
    let lineLeft = firstLeft;
    let maxW = M.pageWidth - M.right - lineLeft;

    const flush = (justify) => {
        if (!lineWords.length) {
            return;
        }
        y = ensureY(doc, y, M);
        if (isFirstLine && label) {
            doc.setFont("helvetica", "normal");
            doc.setFontSize(M.fontSize);
            doc.text(label, left0, y);
        }
        paintLine(doc, y, lineLeft, lineWords, lineWidth, maxW, justify);
        y += M.lineHeight;
        lineWords = [];
        lineWidth = 0;
        isFirstLine = false;
        lineLeft = leftCont;
        maxW = M.pageWidth - M.right - lineLeft;
    };

    for (const word of words) {
        const space = lineWords.length ? doc.getTextWidth(" ") : 0;
        if (lineWords.length && lineWidth + space + word.width > maxW) {
            flush(true);
        }
        if (lineWords.length) {
            lineWidth += doc.getTextWidth(" ");
        }
        lineWords.push(word);
        lineWidth += word.width;
    }
    flush(false);

    return y + M.lineHeight * 0.15;
}

function flattenToWords(doc, M, segments) {
    const words = [];
    for (const seg of segments) {
        doc.setFont("helvetica", seg.bold ? "bold" : "normal");
        doc.setFontSize(M.fontSize);
        const parts = String(seg.text).split(/(\s+)/);
        for (const part of parts) {
            if (!part) {
                continue;
            }
            if (/^\s+$/.test(part)) {
                // spaces that are blank fields stay as their own "word"
                if (seg.blank || seg.highlight) {
                    const w =
                        doc.getTextWidth(part) ||
                        doc.getTextWidth(" ") * part.length;
                    words.push({
                        text: part,
                        width: Math.max(w, seg.blank ? 40 : w),
                        bold: !!seg.bold,
                        highlight: !!seg.highlight,
                        blank: !!seg.blank,
                    });
                }
                continue;
            }
            const width = doc.getTextWidth(part);
            words.push({
                text: part,
                width,
                bold: !!seg.bold,
                highlight: !!seg.highlight,
                blank: !!seg.blank,
            });
        }
    }
    return words;
}

function paintLine(doc, y, left, words, lineWidth, maxW, justify) {
    const gaps = Math.max(words.length - 1, 0);
    let extra = 0;
    if (justify && gaps > 0) {
        extra = (maxW - lineWidth) / gaps;
        // no estirar demasiado
        if (extra > 3.5) {
            extra = 0;
        }
    }

    let x = left;
    for (let i = 0; i < words.length; i++) {
        const w = words[i];
        if (w.highlight || w.blank) {
            drawHighlight(doc, x - 0.5, y - 7, w.width + 1, 9);
        }
        doc.setFont("helvetica", w.bold ? "bold" : "normal");
        doc.setFontSize(8.5);
        if (!w.blank) {
            doc.text(w.text, x, y);
        }
        x += w.width;
        if (i < words.length - 1) {
            x += doc.getTextWidth(" ") + extra;
        }
    }
}

function drawHighlight(doc, x, y, w, h) {
    doc.setFillColor(255, 255, 0);
    doc.rect(x, y, w, h, "F");
}

function ensureY(doc, y, M) {
    if (y > M.pageHeight - M.bottom) {
        doc.addPage();
        return M.top;
    }
    return y;
}
