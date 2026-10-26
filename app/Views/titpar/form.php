<?php echo view('header'); ?>
<?php if ($error = session()->getFlashdata('error')): ?>
<div class="alert alert-danger" role="alert"><?= esc($error) ?></div>
<?php endif; ?>

<?php
$selected_kelurahan = (string) old('titpar_klrhnid', $titpar['titpar_klrhnid'] ?? '');
$selected_kecamatan = (string) old('kecamatan', substr($selected_kelurahan, 0, 6));

$latitude  = $titpar['titpar_latitude'] ?? '';
$longitude = $titpar['titpar_longitude'] ?? '';

$status = $titpar['titpar_status'] ?? 3;
$jenisKendaraan = $titpar['titpar_jenis_kendaraan'] ?? [];
if (is_string($jenisKendaraan)) {
    $jenisKendaraan = json_decode($jenisKendaraan, true) ?: [];
}
$jenisKendaraan = is_array($jenisKendaraan) ? $jenisKendaraan : [];
?>

<!-- Leaflet -->
<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
/>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    #map {
        width: 100%;
        height: 400px;
        border-radius: 8px;
        border: 1px solid #ddd;
    }
</style>

<div class="card">

    <div class="card-header">
        Catat Titik Parkir

        <a
            class="btn btn-danger btn-sm float-end"
            href="<?= esc(base_url(!empty($titpar['titpar_id']) ? 'titpar/manage/' . (int) $titpar['titpar_id'] : 'titpar'), 'attr') ?>"
        >
            Cancel
        </a>
    </div>

    <div class="card-body">

        <div class="container mt-4">

            <h4 class="mb-4">
                Pencatatan Titik Parkir
            </h4>

         <?php
$isEdit = !empty($titpar['titpar_id']);
?>

<form
    action="<?= $isEdit
        ? base_url('/titpar/update/' . $titpar['titpar_id'])
        : base_url('/titpar/store')
    ?>"
    method="post"
    enctype="multipart/form-data"
>

                <?= csrf_field() ?>

                <input
                    type="hidden"
                    name="register_id"
                    value="<?= $_GET['register_id'] ?? '' ?>"
                >


                <!-- NAMA TITIK -->
                <div class="row mb-3">

                    <label class="col-sm-3 col-form-label">
                        Nama Titik Parkir
                    </label>

                    <div class="col-sm-9">

                        <input
                            type="text"
                            id="titpar_namatempat"
                            name="titpar_namatempat"
                            value="<?= esc($titpar['titpar_namatempat'] ?? '') ?>"
                            class="form-control"
                            required
                        >

                    </div>

                </div>

                <div class="row mb-3">
                    <label for="titpar_retritypeid" class="col-sm-3 col-form-label">Jenis Titik Parkir</label>
                    <div class="col-sm-9">
                        <select id="titpar_retritypeid" name="titpar_retritypeid" class="form-select" required>
                            <option value="">-- Pilih Jenis Titik Parkir --</option>
                            <?php foreach (($jenisTitikParkir ?? []) as $jenis): ?>
                                <option value="<?= esc($jenis['retritype_id']) ?>" <?= (string) old('titpar_retritypeid', $titpar['titpar_retritypeid'] ?? '') === (string) $jenis['retritype_id'] ? 'selected' : '' ?>><?= esc($jenis['retrytipe_nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- LOKASI -->
                <div class="row mb-3">

                    <label class="col-sm-3 col-form-label">
                        Lokasi / Alamat
                    </label>

                    <div class="col-sm-9">

                        <input
                            type="text"
                            id="titpar_lokasi"
                            name="titpar_lokasi"
                            value="<?= esc($titpar['titpar_lokasi'] ?? '') ?>"
                            class="form-control"
                            maxlength="75"
                            required
                        >

                        <small class="text-muted">
                            Max 75 character
                        </small>

                    </div>

                </div>


                <!-- KECAMATAN / KELURAHAN -->
                <div class="row mb-3">

                    <label class="col-sm-3 col-form-label">
                        Kecamatan / Kelurahan
                    </label>

                    <div class="col-sm-9">

                        <div class="row">

                            <div class="col-md-6 mb-2">

                                <select
                                    id="kecamatan"
                                    name="kecamatan"
                                    class="form-select"
                                    required
                                >

                                    <option value="">
                                        -- Pilih Kecamatan --
                                    </option>

                                    <?php $kecamatanOptions = []; foreach (($kelurahan ?? []) as $item) $kecamatanOptions[$item['kecamatan_id']] = $item['kecamatan_nama']; ?>
                                    <?php foreach ($kecamatanOptions as $id => $nama): ?>
                                        <option value="<?= esc($id) ?>" <?= $selected_kecamatan == $id ? 'selected' : '' ?>><?= esc($nama) ?></option>
                                    <?php endforeach; ?>

                                </select>

                            </div>


                            <div class="col-md-6 mb-2">

                                <select
                                    id="kelurahan"
                                    name="titpar_klrhnid"
                                    class="form-select"
                                    required
                                >

                                    <option value="">
                                        -- Pilih Kelurahan --
                                    </option>
                                    <?php foreach (($kelurahan ?? []) as $item): ?>
                                        <option value="<?= esc($item['id']) ?>" data-kecamatan="<?= esc($item['kecamatan_id']) ?>" <?= $selected_kelurahan == $item['id'] ? 'selected' : '' ?>><?= esc($item['kelurahan_nama']) ?> (<?= esc($item['id']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- ZONA -->
                <div class="row mb-3">
                    <label for="zona_id" class="col-sm-3 col-form-label">Zona kecamatan</label>
                    <div class="col-sm-9">
                        <select name="zona_id" id="zona_id" class="form-select" disabled>
                            <option value="">-- Pilih kecamatan terlebih dahulu --</option>
<?php foreach ($kecamatanOptions as $id => $nama): ?>
<option value="<?= esc($id) ?>" <?= $selected_kecamatan == $id ? 'selected' : '' ?>><?= esc($nama) ?></option>
<?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- JENIS KENDARAAN -->
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Jenis Kendaraan</label>
                    <div class="col-sm-9">
                        <div class="d-flex gap-4 flex-wrap">
                            <?php foreach (['motor' => 'Motor', 'mobil' => 'Mobil', 'truk' => 'Truk'] as $value => $label): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="titpar_jenis_kendaraan[]"
                                           id="jenis_kendaraan_<?= $value ?>"
                                           value="<?= $value ?>"
                                           <?= in_array($value, $jenisKendaraan, true) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="jenis_kendaraan_<?= $value ?>"><?= $label ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- KOORDINAT -->
                <div class="row mb-3">

                    <label class="col-sm-3 col-form-label">
                        Koordinat
                    </label>

                    <div class="col-sm-9">

                        <div class="row">

                            <div class="col-md-6 mb-2">

                                <label class="form-label">
                                    Latitude
                                </label>

                                <input
                                    type="text"
                                    id="titpar_latitude"
                                    name="titpar_latitude"
                                    value="<?= esc($latitude) ?>"
                                    class="form-control"
                                    placeholder="-7.983908"
                                >

                            </div>


                            <div class="col-md-6 mb-2">

                                <label class="form-label">
                                    Longitude
                                </label>

                                <input
                                    type="text"
                                    id="titpar_longitude"
                                    name="titpar_longitude"
                                    value="<?= esc($longitude) ?>"
                                    class="form-control"
                                    placeholder="112.621391"
                                >

                            </div>

                        </div>

                        <small class="text-muted">
                            Koordinat dapat diisi manual atau pilih lokasi pada peta.
                        </small>

                    </div>

                </div>


                <!-- MAP -->
                <div class="row mb-3">

                    <label class="col-sm-3 col-form-label">
                        Peta Lokasi
                    </label>

                    <div class="col-sm-9">

                        <div id="map"></div>

                        <small class="text-muted d-block mt-2">
                            Klik pada peta untuk menentukan koordinat.
                        </small>

                    </div>

                </div>

                <!-- DENAH -->
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Denah</label>
                    <div class="col-sm-9">
                        <?php if (!empty($titpar['titpar_denah'])): ?>
                            <div class="mb-2">
                                <img src="<?= base_url('uploads/denah/' . basename($titpar['titpar_denah'])) ?>"
                                     alt="Denah saat ini"
                                     class="img-thumbnail"
                                     style="max-width: 260px; max-height: 180px; object-fit: contain;">
                            </div>
                            <small class="text-muted d-block mb-2">Denah saat ini. Pilih file baru jika ingin menggantinya.</small>
                        <?php else: ?>
                            <small class="text-muted d-block mb-2">Belum ada Denah.</small>
                        <?php endif; ?>
                        <input type="file" name="titpar_denah" class="form-control" accept="image/jpeg,image/png,image/webp">
                        <small class="text-muted">Format JPG, PNG, atau WEBP. Maksimal 5 MB.</small>
                    </div>
                </div>


                <!-- STATUS -->
                <div class="row mb-3">

                    <label class="col-sm-3 col-form-label">
                        Status
                    </label>

                    <div class="col-sm-9">

                        <select
                            name="titpar_status"
                            class="form-select"
                            required
                        >

                            <option
                                value="3"
                                <?= $status == 3 ? 'selected' : '' ?>
                            >
                                Aktif
                            </option>

                            <option
                                value="2"
                                <?= $status == 2 ? 'selected' : '' ?>
                            >
                                Nonaktif
                            </option>

                        </select>

                    </div>

                </div>


                <!-- BUTTON -->
                <div class="text-end">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <?= isset($titpar)
                            ? 'Update'
                            : 'Submit'
                        ?>
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<script>

const kecamatanSelect =
    document.getElementById("kecamatan");

const kelurahanSelect =
    document.getElementById("kelurahan");

const selectedKecamatan =
    "<?= esc($selected_kecamatan) ?>";

const selectedKelurahan =
    "<?= esc($selected_kelurahan) ?>";


function loadKelurahan(
    kecId,
    selectedId = null
) {
    Array.from(kelurahanSelect.options).forEach(opt => {
        if (!opt.value) return;
        opt.hidden = opt.dataset.kecamatan !== kecId;
    });
    kelurahanSelect.value = selectedId || '';

}


kecamatanSelect.addEventListener(
    "change",
    function () {

        loadKelurahan(this.value);

    }
);


if (selectedKecamatan) {

    loadKelurahan(
        selectedKecamatan,
        selectedKelurahan
    );

}


/*
|--------------------------------------------------------------------------
| OPENSTREETMAP / LEAFLET
|--------------------------------------------------------------------------
*/

const defaultLat = -6.5971;
const defaultLng = 106.8060;

const latitudeInput =
    document.getElementById(
        'titpar_latitude'
    );

const longitudeInput =
    document.getElementById(
        'titpar_longitude'
    );


let initialLat =
    parseFloat(latitudeInput.value);

let initialLng =
    parseFloat(longitudeInput.value);


// Jika belum ada koordinat,
// gunakan pusat Kota Bogor
if (
    isNaN(initialLat) ||
    isNaN(initialLng)
) {

    initialLat = defaultLat;
    initialLng = defaultLng;

}


const map =
    L.map('map').setView(
        [initialLat, initialLng],
        15
    );


L.tileLayer(
    'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    {
        maxZoom: 19,
        attribution:
            '&copy; OpenStreetMap contributors'
    }
).addTo(map);


let marker =
    L.marker(
        [initialLat, initialLng],
        {
            draggable: true
        }
    ).addTo(map);


// Update input
function updateCoordinates(
    lat,
    lng
) {

    latitudeInput.value =
        lat.toFixed(7);

    longitudeInput.value =
        lng.toFixed(7);

}


// Klik peta
map.on(
    'click',
    function(e) {

        const lat =
            e.latlng.lat;

        const lng =
            e.latlng.lng;

        marker.setLatLng(
            [lat, lng]
        );

        updateCoordinates(
            lat,
            lng
        );

    }
);


// Drag marker
marker.on(
    'dragend',
    function() {

        const position =
            marker.getLatLng();

        updateCoordinates(
            position.lat,
            position.lng
        );

    }
);


// Jika input manual berubah,
// pindahkan marker
function updateMapFromInput() {

    const lat =
        parseFloat(
            latitudeInput.value
        );

    const lng =
        parseFloat(
            longitudeInput.value
        );

    if (
        !isNaN(lat) &&
        !isNaN(lng)
    ) {

        marker.setLatLng(
            [lat, lng]
        );

        map.setView(
            [lat, lng]
        );

    }

}


latitudeInput.addEventListener(
    'change',
    updateMapFromInput
);

longitudeInput.addEventListener(
    'change',
    updateMapFromInput
);

</script>

<script>
const zonaSelect = document.getElementById('zona_id');
function syncZona() { zonaSelect.value = kecamatanSelect.value; }
kecamatanSelect.addEventListener('change', syncZona);
syncZona();
</script>
<?php echo view('footer'); ?>

