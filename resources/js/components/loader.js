export function showLoader(text = "Cargando...") {
    console.log("showLoader");
    let loader = document.querySelector("#global-loader");

    if (!loader) {
        loader = document.createElement("div");
        loader.id = "global-loader";
        loader.className = "global-loader";

        loader.innerHTML = `
            <div class="global-loader-content">
                <div
                    class="spinner-border text-primary"
                    role="status"
                    aria-hidden="true"
                ></div>

                <span class="global-loader-text"></span>
            </div>
        `;

        document.body.appendChild(loader);
    }

    const textElement = loader.querySelector(".global-loader-text");

    if (textElement) {
        textElement.textContent = text;
    }

    loader.classList.add("is-visible");
}

export function hideLoader() {
    document
        .querySelector("#global-loader")
        ?.classList.remove("is-visible");
}