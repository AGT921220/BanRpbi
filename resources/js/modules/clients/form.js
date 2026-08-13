import { setOptions, importLibrary } from '@googlemaps/js-api-loader';

/**
 * Lectura dinámica para evitar que Vite elimine el código
 * cuando VITE_GOOGLE_MAPS_API_KEY está vacío en tiempo de build.
 */
function readViteEnv(name, fallback = '') {
    const value = import.meta.env[name];

    if (value === undefined || value === null || value === '') {
        return fallback;
    }

    return String(value);
}

function showAddressError(message) {
    const errorBox = document.getElementById('client-address-search-error');

    if (!errorBox) {
        return;
    }

    errorBox.textContent = message;
    errorBox.classList.remove('d-none');
}

function hideAddressError() {
    document.getElementById('client-address-search-error')?.classList.add('d-none');
}

function componentValue(components, type, useShortName = false) {
    const match = components.find((component) => component.types.includes(type));

    if (!match) {
        return '';
    }

    return useShortName
        ? (match.shortText || match.short_name || '')
        : (match.longText || match.long_name || '');
}

function firstComponentValue(components, types) {
    for (const type of types) {
        const value = componentValue(components, type);

        if (value) {
            return value;
        }
    }

    return '';
}

function fillMapsLocation(place) {
    const mapsUrlInput = document.getElementById('client-maps-url');
    const placeIdInput = document.getElementById('client-maps-place-id');
    const latitudeInput = document.getElementById('client-latitude');
    const longitudeInput = document.getElementById('client-longitude');

    if (!mapsUrlInput || !placeIdInput || !latitudeInput || !longitudeInput) {
        return;
    }

    const location = place?.geometry?.location;
    const latitude = typeof location?.lat === 'function' ? location.lat() : location?.lat;
    const longitude = typeof location?.lng === 'function' ? location.lng() : location?.lng;
    const mapsUrl = place?.url
        || (Number.isFinite(latitude) && Number.isFinite(longitude)
            ? `https://www.google.com/maps/search/?api=1&query=${latitude},${longitude}`
            : (place?.place_id
                ? `https://www.google.com/maps/search/?api=1&query=place_id:${place.place_id}`
                : ''));

    mapsUrlInput.value = mapsUrl;
    placeIdInput.value = place?.place_id || '';
    latitudeInput.value = Number.isFinite(latitude) ? String(latitude) : '';
    longitudeInput.value = Number.isFinite(longitude) ? String(longitude) : '';
}

function fillAddressFields(place) {
    const streetInput = document.getElementById('client-street');
    const numExtInput = document.getElementById('client-num-ext');
    const numIntInput = document.getElementById('client-num-int');
    const postalCodeInput = document.getElementById('client-postal-code');

    if (!streetInput || !numExtInput || !numIntInput || !postalCodeInput) {
        return;
    }

    const components = place?.address_components || [];
    const route = componentValue(components, 'route');
    const streetNumber = componentValue(components, 'street_number');
    const subpremise = componentValue(components, 'subpremise');
    const postalCode = componentValue(components, 'postal_code');
    const premiseOrNeighborhood = firstComponentValue(components, [
        'premise',
        'neighborhood',
        'sublocality_level_1',
        'sublocality',
        'colloquial_area',
    ]);

    // Residenciales / fraccionamientos a menudo no traen "route".
    streetInput.value = route
        || premiseOrNeighborhood
        || place?.name
        || streetInput.value;

    if (streetNumber) {
        numExtInput.value = streetNumber;
    }

    numIntInput.value = subpremise || '';

    if (postalCode) {
        postalCodeInput.value = postalCode;
    }

    streetInput.dispatchEvent(new Event('input', { bubbles: true }));
    numExtInput.dispatchEvent(new Event('input', { bubbles: true }));
    numIntInput.dispatchEvent(new Event('input', { bubbles: true }));
    postalCodeInput.dispatchEvent(new Event('input', { bubbles: true }));
}

async function initAddressAutocomplete() {
    const searchInput = document.getElementById('client-address-search');

    if (!searchInput) {
        return;
    }

    const apiKey = readViteEnv('VITE_GOOGLE_MAPS_API_KEY').trim();

    if (!apiKey) {
        showAddressError('No se configuró VITE_GOOGLE_MAPS_API_KEY.');
        searchInput.disabled = true;
        return;
    }

    try {
        setOptions({
            key: apiKey,
            v: 'weekly',
            language: 'es',
            region: 'mx',
        });

        const { Autocomplete } = await importLibrary('places');

        // Sin types: ['address'] para incluir residenciales, fraccionamientos y POIs.
        const autocomplete = new Autocomplete(searchInput, {
            fields: ['address_components', 'formatted_address', 'name', 'geometry', 'place_id', 'url'],
            componentRestrictions: { country: 'mx' },
        });

        autocomplete.addListener('place_changed', () => {
            const place = autocomplete.getPlace();
            const components = place?.address_components || [];

            if (components.length === 0 && !place?.name) {
                showAddressError('No se pudo obtener la dirección seleccionada. Intenta otra sugerencia.');
                return;
            }

            hideAddressError();
            fillAddressFields(place);
            fillMapsLocation(place);

            if (place.formatted_address) {
                searchInput.value = place.formatted_address;
            } else if (place.name) {
                searchInput.value = place.name;
            }
        });
    } catch (error) {
        console.error(error);
        showAddressError('No se pudo inicializar Google Maps Places. Verifica la API Key y que Places API esté habilitada.');
        searchInput.disabled = true;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    initAddressAutocomplete();
});
