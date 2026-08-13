import { get, showToast, readViteEnv } from "../../admin";
import { jsPDF } from "jspdf";
import { buildManifest } from "./manifestPdfBuilder";

const semarnatLogo = readViteEnv("VITE_SEMARNAT_LOGO");

$(document).on("click", ".btn-manifest-pdf", async function (e) {
    e.preventDefault();

    const id = $(this).data("id");
    const url = `/admin/manifests/${id}`;

    try {
        const manifestData = await getManifest(url);
        console.log(manifestData);

        createManifest(manifestData);
    } catch (error) {
        console.error(error);
        showToast("danger", "Error al generar el manifiesto");
        return;
    }
});

async function getManifest(url) {
    return get(url, "Creando Manifiesto!").then((response) => {
        if (response && response.data) {
            return response.data;
        }
        throw new Error("No se pudo obtener el manifiesto.");
    });
}

function createManifest(manifest) {
    var doc = new jsPDF();

    var img = new Image();
    img.onload = function () {
        doc = buildManifest(doc, manifest, img);

        doc.setProperties({
            title: "Manifiesto " + manifest.folio,
            author: "BAN RPBI",
            creator: "BAN RPBI",
        });
        var pdfBlob = doc.output("blob");
        var url = URL.createObjectURL(pdfBlob);
        setTimeout(() => {
            window.open(url, "_blank");
        }, 500);
    };

    img.src = semarnatLogo;
}
