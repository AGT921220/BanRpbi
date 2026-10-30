import { get, showToast } from "../../admin";
import { hideLoader } from "../../components/loader";
import { createManifest } from "./manifestPdf";

$(function () {
    const root = document.getElementById("public-manifest");
    const url = root?.dataset.url;
    const loaderText = root?.dataset.loader || "Creando tu manifiesto";

    if (!url) {
        return;
    }

    loadManifest(url, loaderText);
});

async function loadManifest(url, loaderText) {
    try {
        const response = await get(
            url,
            loaderText,
            {},
            {},
            true,
            15000,
        );
        const manifest = response?.data;

        if (!manifest) {
            throw new Error("No se pudo obtener el manifiesto.");
        }

        await createManifest(manifest);
        hideLoader();
    } catch (error) {
        console.error(error);
        hideLoader();
        showToast("danger", "Error al generar el manifiesto");
    }
}
