const FALLBACK_LAT = -7.981894;
const FALLBACK_LNG = 112.626503;

const map    = L.map('map').setView([FALLBACK_LAT, FALLBACK_LNG], 15);
const marker = L.marker([FALLBACK_LAT, FALLBACK_LNG], { draggable: true }).addTo(map);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

function updateLocation(lat, lng, center = false) {
    const pos = L.latLng(lat, lng);
    marker.setLatLng(pos);
    if (center) map.flyTo(pos, 16);
    document.getElementById('latitude').value  = parseFloat(lat).toFixed(8);
    document.getElementById('longitude').value = parseFloat(lng).toFixed(8);
}

function getMyLocation(onSuccess, onError) {
    if (!navigator.geolocation) {
        onError('Browser Anda tidak mendukung geolokasi.');
        return;
    }
    navigator.geolocation.getCurrentPosition(
        pos => onSuccess(pos.coords.latitude, pos.coords.longitude),
        err => {
            const isSecure = location.protocol === 'https:'
                || location.hostname === 'localhost'
                || location.hostname === '127.0.0.1';
            const msgs = {
                1: isSecure
                    ? 'Akses lokasi ditolak. Izinkan akses lokasi di pengaturan browser.'
                    : '⚠️ Geolokasi memerlukan HTTPS.',
                2: 'Informasi lokasi tidak tersedia.',
                3: 'Permintaan lokasi habis waktu (timeout).',
            };
            onError(msgs[err.code] ?? 'Gagal mendapatkan lokasi.');
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}

document.addEventListener('DOMContentLoaded', function () {



    // Init map from saved coords or geolocation
    const meta = document.getElementById('mapMeta');
    const savedLat = meta?.dataset.lat;
    const savedLng = meta?.dataset.lng;

    if (savedLat && savedLng) {
        updateLocation(parseFloat(savedLat), parseFloat(savedLng), true);
    } else {
        getMyLocation(
            (lat, lng) => updateLocation(lat, lng, true),
            ()         => updateLocation(FALLBACK_LAT, FALLBACK_LNG)
        );
    }

    // Click on map
    map.on('click', e => updateLocation(e.latlng.lat, e.latlng.lng));

    // Drag marker
    marker.on('dragend', e => {
        const pos = e.target.getLatLng();
        updateLocation(pos.lat, pos.lng);
        
    });

    // "Use my location" button
    const btn = document.getElementById('btnMyLocation');
    btn.addEventListener('click', function () {
        btn.disabled     = true;
        btn.textContent  = '⏳ Mendeteksi lokasi...';
        getMyLocation(
            (lat, lng) => {
                updateLocation(lat, lng, true);
                btn.disabled    = false;
                btn.textContent = '📍 Gunakan Lokasi Saya';
            },
            msg => {
                btn.disabled    = false;
                btn.textContent = '📍 Gunakan Lokasi Saya';
                alert('⚠️ ' + msg);
            }
        );
    });

    // Validate before submit
    document.querySelector('form').addEventListener('submit', function (e) {
        const lat = document.getElementById('latitude').value.trim();
        const lng = document.getElementById('longitude').value.trim();
        if (!lat || !lng) {
            e.preventDefault();
            alert('⚠️ Harap tentukan lokasi parkir pada peta terlebih dahulu.');
            document.getElementById('map').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
});