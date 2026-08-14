import { jsPDF } from "jspdf";

$(document).on("click", ".btn-invoice-pdf-test", function (e) {
    e.preventDefault();
    createInvoiceTest();
});

function createInvoiceTest() {
    var doc = new jsPDF({
        unit: "mm",
        format: [320, 250],
    });

    if (typeof doc.setFontType !== "function") {
        doc.setFontType = function (style) {
            doc.setFont("helvetica", style);
        };
    }

    var img = new Image();
    img.onload = function () {
        var canvas = document.createElement("canvas");
        canvas.width = this.width;
        canvas.height = this.height;
        var ctx = canvas.getContext("2d");
        ctx.fillStyle = "#ffffff";
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(this, 0, 0);
        var imgData = canvas.toDataURL("image/jpeg");
        doc.setFontType("bold");
        doc.setFontSize(16);
        doc.addImage(imgData, "JPEG", 20, 10, 40, 20);

        let firstTableLeftMargin = 120;
        doc.setFontType("normal");
        doc.setFontSize(8);

        doc.rect(firstTableLeftMargin + 0, 5, 60, 7, "S");
        doc.rect(firstTableLeftMargin + 10 + 50, 5, 60, 7, "S");
        doc.text(firstTableLeftMargin + 2, 10, "Tipo de Comprobante: I - Ingreso");
        doc.text(firstTableLeftMargin + 62, 10, "T.C.: 1.00000");

        doc.rect(firstTableLeftMargin + 0, 12, 60, 7, "S");
        doc.rect(firstTableLeftMargin + 10 + 50, 12, 60, 7, "S");
        doc.text(firstTableLeftMargin + 2, 17, "Serie y Folio:");
        doc.text(firstTableLeftMargin + 62, 17, "MXN 8036");

        doc.rect(firstTableLeftMargin + 0, 19, 60, 7, "S");
        doc.rect(firstTableLeftMargin + 60, 19, 60, 7, "S");
        doc.text(firstTableLeftMargin + 2, 24, "Orden de compra: ");
        doc.text(firstTableLeftMargin + 62, 24, "63626627 ON");

        doc.rect(firstTableLeftMargin + 0, 26, 60, 7, "S");
        doc.rect(firstTableLeftMargin + 60, 26, 60, 7, "S");
        doc.text(firstTableLeftMargin + 2, 31, "Fecha: ");
        doc.text(firstTableLeftMargin + 62, 31, "01/Ago/2023  11:49:14");

        doc.rect(firstTableLeftMargin + 0, 33, 60, 7, "S");
        doc.rect(firstTableLeftMargin + 60, 33, 60, 7, "S");
        doc.text(firstTableLeftMargin + 2, 38, "Lugar de expedición (C.P.): ");
        doc.text(firstTableLeftMargin + 62, 38, "22444");

        doc.setFillColor(50, 50, 50);
        doc.rect(10, 50, 230, 20, "F");

        doc.setFontSize(10);
        doc.setTextColor(255, 255, 255);
        doc.text(12, 55, "Emisor: GRUPO AMBIENTAL DEL NOROESTE");
        doc.text(12, 60, "RFC: GAN010409210");
        doc.text(12, 65, "Régimen Fiscal: 601        General deLev PersonasMorales");

        doc.text(130, 55, "CALLE MAQUILADORAS No. 1469");
        doc.text(130, 60, "CIUDAD INDUSTRIAL");
        doc.text(130, 65, "Tijuana Baja California");

        doc.setTextColor(0, 0, 0);
        doc.setFontType("bold");

        doc.rect(10, 75, 230, 30, "S");
        doc.text(12, 79, "Cliente:");
        doc.text(12, 79 + 5, "RFC:");
        doc.text(12, 79 + 10, "Regimen:");
        doc.text(12, 79 + 15, "Domicilio:");

        doc.text(130, 79, "Método de Pago:");
        doc.text(130, 79 + 5, "Forma de Pago:");
        doc.text(130, 79 + 10, "Uso CFDI:");
        doc.text(130, 79 + 15, "Moneda:");

        doc.setFontType("normal");
        doc.text(12, 79 + 20, "Col. CENTRO C.P. 66600, Apodaca Nuevo León, México");
        doc.text(25, 79, "PARKER INDUSTRIAL");
        doc.text(25, 79 + 5, "PIN040713FL9");
        doc.text(30, 79 + 10, "601 - General de Ley Personas Morales");
        doc.text(30, 79 + 15, "VIA DE FERROCARRIL A MATAMOROS No. 730");

        doc.text(160, 79, "PPD - Pago en parcialidades o diferido");
        doc.text(160, 79 + 5, "99 - Por definir");
        doc.text(150, 79 + 10, "G03 Gastos en general");
        doc.text(150, 79 + 15, "MXN - Peso Mexicano");

        doc.setFillColor(50, 50, 50);
        doc.setTextColor(0, 0, 0);
        doc.setFontType("bold");

        doc.rect(10, 120, 230, 60, "S");

        let unit = 125;
        doc.text(12, unit, "Cantidad");
        doc.text(12 + 25, unit, "Perfil");
        doc.text(12 + 50, unit, "Descripción");
        doc.text(12 + 90, unit, "Manifiesto");
        doc.text(12 + 120, unit, "Unidad");
        doc.text(12 + 120, unit + 2, "de medida");

        doc.text(12 + 150, unit, "Precio");
        doc.text(12 + 150, unit + 2, "unitario");

        doc.text(12 + 170, unit, "Descuento");
        doc.text(12 + 190, unit, "Impuestos");
        doc.text(12 + 210, unit, "Importe");

        doc.setFontType("normal");
        unit = unit + 10;
        doc.setFontSize(6);

        doc.rect(10, 130, 230, 0, "S");
        doc.text(12, unit, "3.0000");
        doc.text(12 + 25, unit, "PARK -011");
        doc.text(12 + 50, unit, "76121900 - AGUA CON ACEITE");
        doc.text(12 + 90, unit, "30786");
        doc.text(12 + 120, unit, "18 - TIBOR 200 LTS");
        doc.text(12 + 150, unit, "1437.1400");
        doc.text(12 + 170, unit, "0.00");
        doc.text(12 + 190, unit, "IVA, - Importe: 334.91");
        doc.text(12 + 215, unit, "4311.42");
        unit = unit + 10;

        doc.rect(10, 130, 230, 0, "S");
        doc.text(12, unit, "3.0000");
        doc.text(12 + 25, unit, "PARK -011");
        doc.text(12 + 50, unit, "76121900 - AGUA CON ACEITE");
        doc.text(12 + 90, unit, "30786");
        doc.text(12 + 120, unit, "18 - TIBOR 200 LTS");
        doc.text(12 + 150, unit, "1437.1400");
        doc.text(12 + 170, unit, "0.00");
        doc.text(12 + 190, unit, "IVA, - Importe: 334.91");
        doc.text(12 + 215, unit, "4311.42");
        unit = unit + 10;

        doc.rect(10, 130, 230, 0, "S");
        doc.text(12, unit, "3.0000");
        doc.text(12 + 25, unit, "PARK -011");
        doc.text(12 + 50, unit, "76121900 - AGUA CON ACEITE");
        doc.text(12 + 90, unit, "30786");
        doc.text(12 + 120, unit, "18 - TIBOR 200 LTS");
        doc.text(12 + 150, unit, "1437.1400");
        doc.text(12 + 170, unit, "0.00");
        doc.text(12 + 190, unit, "IVA, - Importe: 334.91");
        doc.text(12 + 215, unit, "4311.42");
        unit = unit + 10;

        doc.rect(10, 130, 230, 0, "S");
        doc.text(12, unit, "3.0000");
        doc.text(12 + 25, unit, "PARK -011");
        doc.text(12 + 50, unit, "76121900 - AGUA CON ACEITE");
        doc.text(12 + 90, unit, "30786");
        doc.text(12 + 120, unit, "18 - TIBOR 200 LTS");
        doc.text(12 + 150, unit, "1437.1400");
        doc.text(12 + 170, unit, "0.00");
        doc.text(12 + 190, unit, "IVA, - Importe: 334.91");
        doc.text(12 + 215, unit, "4311.42");
        unit = unit + 10;

        doc.rect(10, 130, 230, 0, "S");
        doc.text(12, unit, "3.0000");
        doc.text(12 + 25, unit, "PARK -011");
        doc.text(12 + 50, unit, "76121900 - AGUA CON ACEITE");
        doc.text(12 + 90, unit, "30786");
        doc.text(12 + 120, unit, "18 - TIBOR 200 LTS");
        doc.text(12 + 150, unit, "1437.1400");
        doc.text(12 + 170, unit, "0.00");
        doc.text(12 + 190, unit, "IVA, - Importe: 334.91");
        doc.text(12 + 215, unit, "4311.42");
        unit = unit + 10;

        let height = 50;
        doc.rect(12 + 24, 130, 0, height, "S");
        doc.rect(12 + 50 - 1, 130, 0, height, "S");
        doc.rect(12 + 90 - 1, 130, 0, height, "S");
        doc.rect(12 + 120 - 1, 130, 0, height, "S");
        doc.rect(12 + 150 - 1, 130, 0, height, "S");
        doc.rect(12 + 170 - 1, 130, 0, height, "S");
        doc.rect(12 + 190 - 1, 130, 0, height, "S");
        doc.rect(12 + 215 - 1, 130, 0, height, "S");

        doc.setFontSize(10);
        doc.setFontType("bold");
        doc.text(12, 200, "Total con letra");
        doc.setFontType("normal");
        doc.text(12, 204, "vintidos mil ochocientos noventa y ocho Pesos 12/100 M.N.");

        doc.rect(170, 190, 70, 30, "S");

        doc.setFontType("bold");
        doc.text(172, 195, "Subtotal:");
        doc.text(172, 195 + 5, "Descuento:");
        doc.text(172, 195 + 10, "I.V.A. 8%:");
        doc.text(172, 195 + 15, "Ret. I.V.A.:");
        doc.text(172, 195 + 20, "Total:");

        doc.setFontType("normal");
        doc.text(200, 195, "$21,201.97");
        doc.text(200, 195 + 5, "$0.00");
        doc.text(200, 195 + 10, "$1,696.15");
        doc.text(200, 195 + 15, "$0.00");
        doc.text(200, 195 + 20, "");

        doc.setFontSize(9);
        doc.setFontType("bold");
        doc.text(20, 220, "Tipo Relación");

        doc.text(120, 225, "Este documento es una representación impresa de un CFDI");

        doc.text(100, 230 + 4, "Serie del Certificado del emisor:");
        doc.text(100, 230 + 8, "Folio Fiscal:");
        doc.text(100, 230 + 12, "No. de serie del Certificado del SAT:");
        doc.text(100, 230 + 16, "Fecha y hora de certificación:");

        doc.setFontType("normal");
        doc.text(160, 230 + 4, "00001000000506064372");
        doc.text(125, 230 + 8, "8CDA9550-FE17-48FB-9CDA-693AE28C22F5");
        doc.text(165, 230 + 12, "00001000000505142236");
        doc.text(155, 230 + 16, "2023-08-01T12:55:27");

        doc.setFillColor(50, 50, 50);
        doc.rect(10, 250, 230, 10, "F");

        doc.setFontType("bold");
        doc.setFontSize(10);
        doc.setTextColor(255, 255, 255);
        doc.text(100, 256, "Sello digital del CFDI");

        doc.setTextColor(0, 0, 0);
        doc.setFontSize(7);
        doc.setFontType("normal");
        doc.text(12, 264, "R+UPlqX7vCYmImQQ4nfAhjUAKYkZnGBELT8nopz9zMHQhmu1+r17oCdKD0M1sTlPqbTC2bBE1ewgbIKffcKf2NzAn1uABaKXF9sAPILWEBRGUN7brhQoVvip0/fvgye2i1h/nrZh9iazkgV");
        doc.text(12, 267, "kEVKauEt+dNSQ8IAZB4sXNGKOewuBONfTLLzXysp60gx72D1DR6TVYXzJcUXCPd/+DV6hcOzUZ97KmaxPsCFImlRu2MMmLOrUoM5lGrQCydbGm16hvrVCYmuYCKJQjlkPaQps7KH/+oRcc0");
        doc.text(12, 270, "0BCOBPMwvjL4mBfpE0W6cD+hRiCyV3TQBDFyPxx2PKjW4oKAojSeKylw==");

        doc.setFillColor(50, 50, 50);
        doc.rect(10, 280, 230, 10, "F");

        doc.setFontType("bold");
        doc.setFontSize(10);
        doc.setTextColor(255, 255, 255);
        doc.text(100, 286, "Sello del SAT");

        doc.setTextColor(0, 0, 0);
        doc.setFontSize(7);
        doc.setFontType("normal");
        doc.text(12, 294, "R+UPlqX7vCYmImQQ4nfAhjUAKYkZnGBELT8nopz9zMHQhmu1+r17oCdKD0M1sTlPqbTC2bBE1ewgbIKffcKf2NzAn1uABaKXF9sAPILWEBRGUN7brhQoVvip0/fvgye2i1h/nrZh9iazkgV");
        doc.text(12, 297, "kEVKauEt+dNSQ8IAZB4sXNGKOewuBONfTLLzXysp60gx72D1DR6TVYXzJcUXCPd/+DV6hcOzUZ97KmaxPsCFImlRu2MMmLOrUoM5lGrQCydbGm16hvrVCYmuYCKJQjlkPaQps7KH/+oRcc0");
        doc.text(12, 300, "0BCOBPMwvjL4mBfpE0W6cD+hRiCyV3TQBDFyPxx2PKjW4oKAojSeKylw==");

        doc.save("factura.pdf");
    };
    img.src = "/images/logo.png";
}
