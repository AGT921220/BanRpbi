import axios from "axios";
import $ from "jquery";
import { showLoader, hideLoader } from "./components/loader";

window.$ = window.jQuery = $;

const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute("content");

axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
axios.defaults.headers.common["Accept"] = "application/json";

if (csrfToken) {
    axios.defaults.headers.common["X-CSRF-TOKEN"] = csrfToken;
}

export function readViteEnv(name, fallback = "") {
    const value = import.meta.env[name];

    if (value === undefined || value === null || value === "") {
        return fallback;
    }

    return String(value);
}

/**
 * GET
 */
export async function get(
    url,
    loaderText = "Cargando...",
    params = {},
    headers = {},
    loader = true,
    timeout = 500,
) {
    if (loader) {
        showLoader(loaderText);
    }

    try {
        const response = await axios.get(url, {
            params,
            headers,
            timeout,
        });

        return response.data;
    } finally {
        if (loader) {
            setTimeout(hideLoader, timeout);
        }
    }
}
/**
 * POST
 */
export async function post(
    url,
    data = {},
    loaderText = "Guardando...",
    { params = {}, headers = {}, loader = true, timeout = 5000 } = {},
) {
    if (loader) {
        showLoader(loaderText);
    }

    try {
        const response = await axios.post(url, data, {
            params,
            headers,
            timeout,
        });

        return response.data;
    } finally {
        if (loader) {
            hideLoader();
        }
    }
}

/**
 * PUT
 */
export async function put(
    url,
    data = {},
    loaderText = "Guardando...",
    { params = {}, headers = {}, loader = true, timeout = 5000 } = {},
) {
    if (loader) {
        showLoader(loaderText);
    }

    try {
        const response = await axios.put(url, data, {
            params,
            headers,
            timeout,
        });

        return response.data;
    } finally {
        if (loader) {
            hideLoader();
        }
    }
}

window.BanHttp = { get, post, put };

export default window.BanHttp;

export function showToast(
    type,
    text,
    textType = null,
    time = 5000,
    persist = false,
) {
    if (type != "success" && type != "danger") {
        type = "success";
    }
    let typeText = type == "success" ? "Éxito" : "Error";
    if (textType) {
        typeText = textType;
    }
    let toast = $(
        ' <div class="toast toast-' +
            type +
            ' success"><div class="container-1"><i class="fas fa-check-circle"></i></div><div class="container-2">    <p>' +
            typeText +
            "</p>    <p>" +
            text +
            '</p></div>   <span><button type="button" class="close close_toast" data-dismiss="alert"aria-hidden="true">&times;</button></i>   </div>',
    );

    $(".toast-container").append(toast);

    if (!persist) {
        toast
            .fadeIn(500)
            .delay(time)
            .fadeOut(500, function () {
                $(this).remove();
            });
    }
}

export function splitTextAndAddToDocument(
    doc,
    text,
    initialPosition,
    maxLength = 50,
    initialX = 25,
    spacing = 2,
) {
    let start = 0;
    let end = maxLength;
    let lineIndex = 0;

    while (start < text.length) {
        if (end < text.length) {
            while (text[end] !== " " && end > start) {
                end--;
            }
        }
        addTextLine(
            doc,
            text.substring(start, end).trim(),
            initialPosition,
            lineIndex,
            initialX,
            spacing,
        );
        start = end + 1; // Move the start to the next character after the space
        end = start + maxLength;
        lineIndex++;
    }
}


function addTextLine(doc, text, initialPosition, lineIndex, initialX, spacing) {
  doc.text(initialX, initialPosition + lineIndex * spacing, text);
}
