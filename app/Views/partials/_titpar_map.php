<div class="mb-3">
    <label class="form-label">Lokasi Titik Parkir</label>
    <div class="alert alert-warning py-2 mt-2">
        <small>Izinkan akses lokasi. Geser pin atau klik pada peta untuk menentukan lokasi parkir.</small>
    </div>
    <div id="map" class="rounded border shadow-sm" style="height:400px;"></div>
</div>

<button type="button" class="btn btn-outline-primary btn-sm mb-3" id="btnMyLocation">
    📍 Gunakan Lokasi Saya
</button>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Latitude</label>
        <input type="text" class="form-control" id="latitude" name="latitude"
               value="<?= esc($titpar['latitude'] ?? '') ?>" readonly>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Longitude</label>
        <input type="text" class="form-control" id="longitude" name="longitude"
               value="<?= esc($titpar['longitude'] ?? '') ?>" readonly>
    </div>
</div>


<div id="mapMeta"
     data-lat="<?= esc($titpar['latitude'] ?? '') ?>"
     data-lng="<?= esc($titpar['longitude'] ?? '') ?>">
</div>