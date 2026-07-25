import { setOptions, importLibrary } from '@googlemaps/js-api-loader';
import {
    TerraDraw,
    TerraDrawPolygonMode,
    TerraDrawSelectMode,
} from 'terra-draw';
import { TerraDrawGoogleMapsAdapter } from 'terra-draw-google-maps-adapter';

const FALLBACK_LAT = 25.6866;
const FALLBACK_LNG = -100.3161;
const FALLBACK_ZOOM = 10;

let initialized = false;

/**
 * Lectura dinámica para evitar que Vite elimine el código del mapa
 * cuando VITE_GOOGLE_MAPS_API_KEY está vacío en tiempo de build.
 */
function readViteEnv(name, fallback = '') {
    const value = import.meta.env[name];

    if (value === undefined || value === null || value === '') {
        return fallback;
    }

    return String(value);
}

function readJsonScript(id) {
    const element = document.getElementById(id);

    if (!element) {
        return null;
    }

    try {
        return JSON.parse(element.textContent || 'null');
    } catch {
        return null;
    }
}

function showMapError(message) {
    const errorBox = document.getElementById('zone-map-error');

    if (!errorBox) {
        return;
    }

    errorBox.textContent = message;
    errorBox.classList.remove('d-none');
}

function parseNumber(value, fallback) {
    const parsed = Number(value);

    return Number.isFinite(parsed) ? parsed : fallback;
}

const DEFAULT_ZONE_COLOR = '#206bc4';

function normalizeHexColor(hex) {
    const value = String(hex || DEFAULT_ZONE_COLOR).trim();
    const withHash = value.startsWith('#') ? value : `#${value}`;

    if (!/^#[0-9A-Fa-f]{6}$/.test(withHash)) {
        return DEFAULT_ZONE_COLOR;
    }

    return withHash.toLowerCase();
}

function polygonDrawStyles(color) {
    const fillColor = normalizeHexColor(color);

    return {
        fillColor,
        fillOpacity: 0.35,
        outlineColor: fillColor,
        outlineOpacity: 0.95,
        outlineWidth: 2,
        closingPointColor: fillColor,
        closingPointOutlineColor: '#ffffff',
        editedPointColor: fillColor,
        editedPointOutlineColor: '#ffffff',
        coordinatePointColor: fillColor,
        coordinatePointOutlineColor: '#ffffff',
    };
}

function polygonSelectStyles(color) {
    const fillColor = normalizeHexColor(color);

    return {
        selectedPolygonColor: fillColor,
        selectedPolygonFillOpacity: 0.4,
        selectedPolygonOutlineColor: fillColor,
        selectedPolygonOutlineOpacity: 1,
        selectedPolygonOutlineWidth: 3,
        selectionPointColor: fillColor,
        selectionPointOutlineColor: '#ffffff',
        midPointColor: fillColor,
        midPointOutlineColor: '#ffffff',
    };
}

function geoJsonToLatLngLiteral(geometry) {
    const ring = geometry?.coordinates?.[0];

    if (!Array.isArray(ring)) {
        return [];
    }

    return ring.map(([lng, lat]) => ({ lat, lng }));
}

function pointInRing(point, ring) {
    const [x, y] = point;
    let inside = false;

    for (let i = 0, j = ring.length - 1; i < ring.length; j = i++) {
        const [xi, yi] = ring[i];
        const [xj, yj] = ring[j];
        const intersect =
            yi > y !== yj > y &&
            x < ((xj - xi) * (y - yi)) / (yj - yi + Number.EPSILON) + xi;

        if (intersect) {
            inside = !inside;
        }
    }

    return inside;
}

function ringsPossiblyOverlap(ringA, ringB) {
    if (!Array.isArray(ringA) || !Array.isArray(ringB) || ringA.length < 4 || ringB.length < 4) {
        return false;
    }

    return (
        ringA.some((point) => pointInRing(point, ringB)) ||
        ringB.some((point) => pointInRing(point, ringA))
    );
}

function extractEditablePolygon(snapshot) {
    const feature = snapshot.find(
        (item) => item?.geometry?.type === 'Polygon' && item?.properties?.mode === 'polygon',
    );

    return feature?.geometry ?? null;
}

function isValidPolygonGeometry(geometry) {
    if (!geometry || geometry.type !== 'Polygon') {
        return false;
    }

    const ring = geometry.coordinates?.[0];

    if (!Array.isArray(ring) || ring.length < 4) {
        return false;
    }

    const first = ring[0];
    const last = ring[ring.length - 1];

    if (!first || !last) {
        return false;
    }

    return Number(first[0]) === Number(last[0]) && Number(first[1]) === Number(last[1]);
}

function countVertices(geometry) {
    const ring = geometry?.coordinates?.[0];

    if (!Array.isArray(ring) || ring.length === 0) {
        return 0;
    }

    const first = ring[0];
    const last = ring[ring.length - 1];
    const closed =
        first &&
        last &&
        Number(first[0]) === Number(last[0]) &&
        Number(first[1]) === Number(last[1]);

    return closed ? Math.max(ring.length - 1, 0) : ring.length;
}

async function initZoneMapForm() {
    if (initialized) {
        return;
    }

    const mapElement = document.getElementById('zone-map');
    const geometryInput = document.getElementById('zone-geometry');
    const submitButton = document.getElementById('zone-submit-btn');
    const vertexCountLabel = document.getElementById('zone-vertex-count');
    const overlapWarning = document.getElementById('zone-overlap-warning');

    if (!mapElement || !geometryInput) {
        return;
    }

    initialized = true;

    const apiKey = readViteEnv('VITE_GOOGLE_MAPS_API_KEY').trim();

    if (!apiKey) {
        showMapError('No se configuró VITE_GOOGLE_MAPS_API_KEY.');
        return;
    }

    const defaultLat = parseNumber(readViteEnv('VITE_MAP_DEFAULT_LAT'), FALLBACK_LAT);
    const defaultLng = parseNumber(readViteEnv('VITE_MAP_DEFAULT_LNG'), FALLBACK_LNG);
    const defaultZoom = parseNumber(readViteEnv('VITE_MAP_DEFAULT_ZOOM'), FALLBACK_ZOOM);

    const existingZones = readJsonScript('existing-zones-data') || [];
    const currentGeometry =
        readJsonScript('current-zone-geometry') ||
        (() => {
            try {
                return geometryInput.value ? JSON.parse(geometryInput.value) : null;
            } catch {
                return null;
            }
        })();

    const drawBtn = document.getElementById('zone-draw-btn');
    const editBtn = document.getElementById('zone-edit-btn');
    const deleteBtn = document.getElementById('zone-delete-polygon-btn');
    const centerBtn = document.getElementById('zone-center-btn');
    const colorInput = document.getElementById('zone-color');

    let draw = null;
    let map = null;
    let overlayPolygons = [];

    const getSelectedColor = () => normalizeHexColor(colorInput?.value || DEFAULT_ZONE_COLOR);

    const applySelectedColorStyles = () => {
        if (!draw) {
            return;
        }

        const color = getSelectedColor();

        draw.updateModeOptions('polygon', {
            styles: polygonDrawStyles(color),
        });

        draw.updateModeOptions('select', {
            styles: polygonSelectStyles(color),
        });
    };

    const syncFormState = () => {
        if (!draw) {
            return;
        }

        const snapshot = draw.getSnapshot();
        const polygonFeatures = snapshot.filter(
            (feature) => feature?.geometry?.type === 'Polygon' && feature?.properties?.mode === 'polygon',
        );

        if (polygonFeatures.length > 1) {
            const removable = polygonFeatures.slice(1).map((feature) => feature.id);
            draw.removeFeatures(removable);
        }

        const geometry = extractEditablePolygon(draw.getSnapshot());
        const valid = isValidPolygonGeometry(geometry);
        const vertices = countVertices(geometry);

        if (vertexCountLabel) {
            vertexCountLabel.textContent = String(vertices);
        }

        if (valid) {
            geometryInput.value = JSON.stringify(geometry);
        } else {
            geometryInput.value = '';
        }

        if (submitButton) {
            submitButton.disabled = !valid;
        }

        if (overlapWarning) {
            const overlaps = valid
                ? existingZones.some((zone) =>
                      ringsPossiblyOverlap(
                          geometry.coordinates[0],
                          zone?.geometry?.coordinates?.[0],
                      ),
                  )
                : false;

            overlapWarning.classList.toggle('d-none', !overlaps);
        }
    };

    const renderExistingZones = () => {
        overlayPolygons.forEach((polygon) => polygon.setMap(null));
        overlayPolygons = [];

        existingZones.forEach((zone) => {
            if (!zone?.geometry || zone.geometry.type !== 'Polygon') {
                return;
            }

            const path = geoJsonToLatLngLiteral(zone.geometry);

            if (path.length < 3) {
                return;
            }

            const color = normalizeHexColor(zone.color || '#6c7a91');
            const polygon = new google.maps.Polygon({
                paths: path,
                strokeColor: color,
                strokeOpacity: 0.85,
                strokeWeight: 2,
                fillColor: color,
                fillOpacity: 0.25,
                clickable: true,
                editable: false,
                draggable: false,
                map,
            });

            const info = new google.maps.InfoWindow({
                content: `<strong>${String(zone.name || 'Zona')}</strong>`,
            });

            polygon.addListener('mouseover', (event) => {
                info.setPosition(event.latLng);
                info.open({ map });
            });

            polygon.addListener('mouseout', () => info.close());

            overlayPolygons.push(polygon);
        });
    };

    const centerMap = () => {
        if (!map) {
            return;
        }

        const geometry = extractEditablePolygon(draw?.getSnapshot?.() || []);

        if (isValidPolygonGeometry(geometry)) {
            const bounds = new google.maps.LatLngBounds();
            geoJsonToLatLngLiteral(geometry).forEach((point) => bounds.extend(point));
            map.fitBounds(bounds, 48);
            return;
        }

        if (overlayPolygons.length > 0) {
            const bounds = new google.maps.LatLngBounds();
            overlayPolygons.forEach((polygon) => {
                polygon.getPath().forEach((latLng) => bounds.extend(latLng));
            });
            map.fitBounds(bounds, 48);
            return;
        }

        map.setCenter({ lat: defaultLat, lng: defaultLng });
        map.setZoom(defaultZoom);
    };

    try {
        setOptions({
            key: apiKey,
            v: 'weekly',
            language: 'es',
        });

        await importLibrary('maps');

        map = new google.maps.Map(mapElement, {
            center: { lat: defaultLat, lng: defaultLng },
            zoom: defaultZoom,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: true,
            clickableIcons: false,
        });

        map.addListener('projection_changed', () => {
            if (draw) {
                return;
            }

            const initialColor = getSelectedColor();

            draw = new TerraDraw({
                adapter: new TerraDrawGoogleMapsAdapter({
                    map,
                    lib: google.maps,
                    coordinatePrecision: 9,
                }),
                modes: [
                    new TerraDrawPolygonMode({
                        modeName: 'polygon',
                        styles: polygonDrawStyles(initialColor),
                    }),
                    new TerraDrawSelectMode({
                        modeName: 'select',
                        styles: polygonSelectStyles(initialColor),
                        flags: {
                            polygon: {
                                feature: {
                                    draggable: true,
                                    coordinates: {
                                        midpoints: true,
                                        draggable: true,
                                        deletable: true,
                                    },
                                },
                            },
                        },
                    }),
                ],
            });

            draw.start();

            draw.on('ready', () => {
                renderExistingZones();
                applySelectedColorStyles();

                if (isValidPolygonGeometry(currentGeometry)) {
                    draw.addFeatures([
                        {
                            type: 'Feature',
                            geometry: currentGeometry,
                            properties: {
                                mode: 'polygon',
                            },
                        },
                    ]);
                    draw.setMode('select');
                } else {
                    draw.setMode('polygon');
                }

                syncFormState();
                centerMap();
            });

            draw.on('change', () => syncFormState());
            draw.on('finish', () => {
                syncFormState();
                draw.setMode('select');
            });
        });
    } catch (error) {
        initialized = false;
        showMapError('No se pudo cargar Google Maps. Verifica la API key y las restricciones del proyecto.');
        return;
    }

    drawBtn?.addEventListener('click', () => {
        if (!draw) {
            return;
        }

        const geometry = extractEditablePolygon(draw.getSnapshot());

        if (geometry) {
            const confirmed = window.confirm(
                'Ya existe un polígono. ¿Deseas eliminarlo y dibujar uno nuevo?',
            );

            if (!confirmed) {
                return;
            }

            draw.clear();
        }

        draw.setMode('polygon');
        syncFormState();
    });

    editBtn?.addEventListener('click', () => {
        if (!draw) {
            return;
        }

        draw.setMode('select');
    });

    deleteBtn?.addEventListener('click', () => {
        if (!draw) {
            return;
        }

        draw.clear();
        draw.setMode('polygon');
        syncFormState();
    });

    centerBtn?.addEventListener('click', () => centerMap());

    colorInput?.addEventListener('input', () => {
        applySelectedColorStyles();
    });

    colorInput?.addEventListener('change', () => {
        applySelectedColorStyles();
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initZoneMapForm, { once: true });
} else {
    initZoneMapForm();
}
