import { get, showToast, readViteEnv } from "../../admin";
import { jsPDF } from "jspdf";
import { buildManifest } from "./manifestPdfBuilder";
import QRCode from 'qrcode';

const semarnatLogo = readViteEnv("VITE_SEMARNAT_LOGO");

function semarnatLogoUrl() {
    if (!semarnatLogo) {
        return "";
    }

    return new URL(semarnatLogo, `${window.location.origin}/`).href;
}

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

export function createManifest(manifest) {
    return new Promise((resolve, reject) => {
        const doc = new jsPDF();

        QRCode.toDataURL(String(manifest.folio), {
            width: 300,
            margin: 1,
        })
            .then((qrImage) => {
                const img = new Image();
                let finished = false;

                const finish = () => {
                    if (finished) {
                        return;
                    }

                    finished = true;
                    const built = buildManifest(doc, manifest, img, qrImage);

                    built.setProperties({
                        title: "Manifiesto " + manifest.folio,
                        author: "BAN RPBI",
                        creator: "BAN RPBI",
                    });

                    const pdfBlob = built.output("blob");
                    const url = URL.createObjectURL(pdfBlob);
                    const frame = document.getElementById("manifest-frame");

                    if (frame) {
                        frame.src = url;
                        resolve(url);
                        return;
                    }

                    setTimeout(() => {
                        window.open(url, "_blank");
                    }, 500);
                    resolve(url);
                };

                img.onload = finish;
                img.onerror = finish;

                const logoUrl = semarnatLogoUrl();

                if (!logoUrl) {
                    finish();
                    return;
                }

                img.src = logoUrl;
            })
            .catch(reject);
    });
}
