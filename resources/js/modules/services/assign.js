const DATE_INPUT = "#service-date";

$(function () {
    initServiceDate();
    initServiceDateEvents();
    initSelectAll();
});

function initServiceDate() {
    const $date = $(DATE_INPUT);
    const params = new URLSearchParams(window.location.search);
    const queryDate = params.get("date");

    if (queryDate) {
        $date.val(queryDate);
    } else if (!$date.val()) {
        $date.val(formatDate(new Date()));
    }

    updateServiceDateLabel();
}

function initServiceDateEvents() {
    $(DATE_INPUT).on("change", function () {
        navigateToDate($(this).val());
    });

    $("#service-prev-day").on("click", function () {
        changeServiceDate(-1);
    });

    $("#service-next-day").on("click", function () {
        changeServiceDate(1);
    });

    $("#service-today").on("click", function () {
        navigateToDate(formatDate(new Date()));
    });
}

function initSelectAll() {
    const $selectAll = $("#select-all-services");
    const $checkboxes = $(".service-checkbox");

    if (!$selectAll.length || !$checkboxes.length) {
        return;
    }

    $selectAll.on("change", function () {
        $checkboxes.prop("checked", $(this).is(":checked"));
    });

    $checkboxes.on("change", function () {
        const total = $checkboxes.length;
        const checked = $checkboxes.filter(":checked").length;

        $selectAll.prop("checked", total > 0 && total === checked);
    });
}

function changeServiceDate(days) {
    const value = $(DATE_INPUT).val();
    const date = value ? parseDate(value) : new Date();

    date.setDate(date.getDate() + days);
    navigateToDate(formatDate(date));
}

function navigateToDate(date) {
    const url = new URL(window.location.href);

    url.searchParams.set("date", date);
    window.location.href = url.toString();
}

function parseDate(value) {
    const [year, month, day] = value.split("-").map(Number);

    return new Date(year, month - 1, day);
}

function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");

    return `${year}-${month}-${day}`;
}

function updateServiceDateLabel() {
    const value = $(DATE_INPUT).val();

    if (!value) {
        return;
    }

    const selectedDate = parseDate(value);
    const today = new Date();

    today.setHours(0, 0, 0, 0);
    selectedDate.setHours(0, 0, 0, 0);

    const difference = Math.round(
        (selectedDate.getTime() - today.getTime()) / (1000 * 60 * 60 * 24),
    );

    let title;

    if (difference === 0) {
        title = "Hoy";
    } else if (difference === 1) {
        title = "Mañana";
    } else if (difference === -1) {
        title = "Ayer";
    } else {
        title = selectedDate.toLocaleDateString("es-MX", {
            weekday: "long",
        });

        title = title.charAt(0).toUpperCase() + title.slice(1);
    }

    $("#service-date-title").text(title);

    $("#service-date-text").text(
        selectedDate.toLocaleDateString("es-MX", {
            day: "numeric",
            month: "long",
            year: "numeric",
        }),
    );
}
