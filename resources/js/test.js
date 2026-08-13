const apiKey = readViteEnv('VITE_GOOGLE_MAPS_API_KEY').trim();

function readViteEnv(name, fallback = '') {
    const value = import.meta.env[name];

    if (value === undefined || value === null || value === '') {
        return fallback;
    }

    return String(value);
}
window.initMap = function () {
    const locationsInput = document.getElementById('route-locations');
    const mapElement = document.getElementById('map');

    if (!locationsInput || !mapElement) {
        return;
    }

    const locations = JSON.parse(locationsInput.value);

    const points = locations.map((location) => ({
        lat: Number(location.latitude),
        lng: Number(location.longitude),
    }));

    if (!points.length) {
        return;
    }

    const map = new google.maps.Map(mapElement, {
        center: points[0],
        zoom: 12,
    });

    if (points.length === 1) {
        new google.maps.Marker({
            position: points[0],
            map,
        });

        return;
    }

    const directionsService = new google.maps.DirectionsService();

    const directionsRenderer = new google.maps.DirectionsRenderer({
        map,
    });

    directionsService.route(
        {
            origin: points[0],
            destination: points[points.length - 1],

            waypoints: points
                .slice(1, -1)
                .map((point) => ({
                    location: point,
                    stopover: true,
                })),

            travelMode: google.maps.TravelMode.DRIVING,
        },
        (result, status) => {
            if (status === 'OK') {
                directionsRenderer.setDirections(result);
            } else {
                console.error('Error calculando ruta:', status);
            }
        }
    );
};

const script = document.createElement('script');

script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(apiKey)}&callback=initMap`;
script.async = true;
script.defer = true;

document.head.appendChild(script);