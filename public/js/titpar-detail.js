$(document).ready(function () {

    const BASE_URL = document.querySelector('meta[name="base-url"]').content;

    // ─── Kondisi toggle ────────────────────────────────────────────────
    document.querySelectorAll('.kondisi-toggle').forEach(function (el) {
        function update() {
            const target = document.querySelector(el.dataset.target);
            target.classList.toggle('d-none', !el.checked);
        }
        update();
        el.addEventListener('change', update);
    });

    // ─── Modal Edit Map ────────────────────────────────────────────────
    let map, marker;
    const lat = parseFloat($('#latitude').val()) || -6.2;
    const lng = parseFloat($('#longitude').val()) || 106.816666;

    $('#modalEdit').on('shown.bs.modal', function () {
        if (map) {
            map.invalidateSize();
            const curLat = $('#latitude').val();
            const curLng = $('#longitude').val();
            map.setView([curLat, curLng], map.getZoom());
            marker.setLatLng([curLat, curLng]);
            return;
        }

        map = L.map('map').setView([lat, lng], 17);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        marker = L.marker([lat, lng], { draggable: true }).addTo(map);

        function setCoords(p) {
            $('#latitude').val(p.lat);
            $('#longitude').val(p.lng);
            $('#latText').text(p.lat.toFixed(6));
            $('#lngText').text(p.lng.toFixed(6));
        }

        marker.on('dragend', () => setCoords(marker.getLatLng()));
        map.on('click', e => {
            marker.setLatLng(e.latlng);
            setCoords(e.latlng);
        });
    });

    // ─── Public Upload Modal ───────────────────────────────────────────
    let currentUploadField = '';

    $(document).on('click', '.btn-upload-trigger', function () {
        currentUploadField = $(this).data('field');
        $('#upload_record_id').val($(this).data('id'));
        $('#uploadModalTitle').text($(this).data('title'));
        $('#upload_file').val('').attr('name', currentUploadField);
        bootstrap.Modal.getOrCreateInstance(document.getElementById('universalUploadModal')).show();
    });

    $('#universalUploadForm').on('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(this);

        $.ajax({
            url: BASE_URL + '/register/uploadTitpar/' + currentUploadField,
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: res => res.status ? location.reload() : alert(res.message)
        });
    });

    // ─── Admin Upload Modal ─────────────────────────────────────────────
    let adminField = '';

    $(document).on('click', '.btn-upload-admin', function () {
        adminField = $(this).data('field');
        $('#uploadAdminId').val($(this).data('id'));
        $('#uploadAdminTitle').text($(this).data('title'));
        $('#uploadAdminFile').val('').attr('name', adminField);
        new bootstrap.Modal('#uploadAdminModal').show();
    });

    $('#uploadAdminForm').on('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(this);

        $.ajax({
            url: BASE_URL + '/register/uploadTitpar/' + adminField,
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: res => res.status ? location.reload() : alert(res.message)
        });
    });

});