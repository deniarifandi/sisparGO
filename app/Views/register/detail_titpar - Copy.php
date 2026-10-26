<?= view('header') ?>

<?php
$documents = [
    [
        'label' => 'Surat Kesanggupan',
        'field' => 'kesanggupan',
        'folder' => 'uploads/kesanggupan/'
    ],
    [
        'label' => 'Surat Keberatan',
        'field' => 'keberatan',
        'folder' => 'uploads/keberatan/'
    ],
    [
        'label' => 'Denah Lokasi',
        'field' => 'denah',
        'folder' => 'uploads/denah/'
    ],
   
    [
        'label' => 'Berita Acara',
        'field' => 'berita_acara',
        'folder' => 'uploads/berita_acara/'
    ],
    [
        'label' => 'Surat Penunjukan',
        'field' => 'penunjukan',
        'folder' => 'uploads/penunjukan/'
    ],

];

$adminDocs = [
    [
        'field' => 'foto_tampak_depan',
        'label' => 'Foto Tampak Depan',
        'folder'=> 'foto_tampak_depan',
    ],
    [
        'field' => 'foto_tampak_samping',
        'label' => 'Foto Tampak Samping',
        'folder'=> 'foto_tampak_samping',
    ],
];
?>



<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<style>
/* Bootstrap doesn't ship responsive width utilities by default —
   this gives buttons full width on phones, auto width from sm up. */
.btn-auto-sm { width: 100%; }
@media (min-width: 576px) {
    .btn-auto-sm { width: auto; }
}
</style>

<div class="card shadow-sm mx-1 mx-md-0">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="mb-0 text-wrap"><?= esc($titpar['titpar_namatempat']) ?></h5>
            <small class="text-muted"><?= esc($titpar['titpar_lokasi']) ?></small>
        </div>
        <div>
          <a href="<?= base_url('register/admin/detail/'.$titpar['register_id']) ?>"
   class="btn btn-outline-secondary btn-sm">
    <i class="fa fa-arrow-left"></i> Kembali
</a>
        </div>
    </div>

    <div class="card-body p-2 p-md-3">
        <div class="row g-3">

            <div class="col-12 col-lg-5">
                <div class="card border shadow-none h-100">
                    <div class="card-body">
                        <h6 class="mb-3 d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fa fa-parking text-primary me-2"></i>Informasi Titik Parkir
                            </span>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit">
                                <i class="fa fa-pencil"></i> Edit
                            </button>
                        </h6>

                        <div class="table-responsive">
                            <table class="table table-sm table-borderless mb-0 align-middle">
                                <tr>
                                    <th style="min-width: 130px; width: 35%;">Nama Tempat</th>
                                    <td><?= esc($titpar['titpar_namatempat']) ?></td>
                                </tr>
                                <tr>
                                    <th>Lokasi</th>
                                    <td><?= esc($titpar['titpar_lokasi']) ?></td>
                                </tr>
                                <tr>
                                    <th>Batas Parkir</th>
                                    <td><?= esc($titpar['batas_parkir']) ?></td>
                                </tr>
                                <tr>
                                    <th>Jam Operasional</th>
                                    <td><?= esc($titpar['jam_operasional']) ?></td>
                                </tr>
                                <tr>
                                    <th>Jenis Kendaraan</th>
                                    <?php
                                    $jenis = (int)($titpar['jenis_kendaraan'] ?? 0);
                                    $list = [];
                                    if ($jenis & 1) $list[] = 'Motor';
                                    if ($jenis & 2) $list[] = 'Mobil';
                                    if ($jenis & 4) $list[] = 'Truk';
                                    ?>
                                    <td><?= $list ? implode(', ', $list) : '-' ?></td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                       <?php
$status = (int)($titpar['status'] ?? 0);

switch ($status) {
    case 1:
        $badge = 'success';
        $text  = 'Layak';
        break;

    case 2:
        $badge = 'danger';
        $text  = 'Tidak Layak';
        break;

    default:
        $badge = 'warning';
        $text  = 'Pending';
        break;
}
?>

<span class="badge bg-<?= $badge ?>">
    <?= $text ?>
</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Tipe</th>
                                    <td>
                                        <?= $titpar['baru']
                                        ? '<span class="badge bg-info">Baru</span>'
                                        : '<span class="badge bg-secondary">Lama</span>' ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Approved By</th>
                                    <td><?= !empty($titpar['approved_by'])
                                        ? esc($titpar['approved_by'])
                                        : '-' ?></td>
                                </tr>
                                <tr>
                                    <th>Catatan</th>
                                    <td><?= !empty($titpar['catatan'])
                                        ? esc($titpar['catatan'])
                                        : '-' ?></td>
                                </tr>
                                <tr>
                                    <th>Dibuat</th>
                                    <td><?= !empty($titpar['created_at'])
                                        ? date('d M Y H:i', strtotime($titpar['created_at']))
                                        : '-' ?></td>
                                </tr>
                                <tr>
                                    <th>Diperbarui</th>
                                    <td><?= !empty($titpar['updated_at'])
    ? date('d M Y H:i', strtotime($titpar['updated_at']))
    : '-' ?></td>
                                </tr>
                            </table>

                            <?php
$adminDocs = [
    [
        'field'  => 'foto_tampak_depan',
        'label'  => 'Foto Tampak Depan',
        'folder' => 'foto_tampak_depan'
    ],
    [
        'field'  => 'foto_tampak_samping',
        'label'  => 'Foto Tampak Samping',
        'folder' => 'foto_tampak_samping'
    ],
];
?>

<hr>

<h6 class="mb-3">
    <i class="fa fa-camera text-primary me-2"></i>
    Dokumentasi Lapangan (Admin)
</h6>

<div class="row g-3">
    <?php foreach ($adminDocs as $doc): ?>
        <?php $file = $titpar[$doc['field']] ?? ''; ?>

        <div class="col-12">
            <div class="border rounded p-2">

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong><?= $doc['label'] ?></strong>

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-primary btn-upload-admin"
                        data-id="<?= $titpar['id'] ?>"
                        data-field="<?= $doc['field'] ?>"
                        data-title="<?= $doc['label'] ?>">
                        <i class="fa fa-upload"></i> Upload
                    </button>
                </div>

                <?php if ($file): ?>

                    <a href="<?= base_url('uploads/'.$doc['folder'].'/'.$file) ?>" target="_blank">
                        <img
                            src="<?= base_url('uploads/'.$doc['folder'].'/'.$file) ?>"
                            class="img-fluid rounded border"
                            style="max-height:220px;object-fit:cover;width:100%;">
                    </a>

                <?php else: ?>

                    <div class="text-center py-5 border rounded text-muted">
                        Belum ada foto
                    </div>

                <?php endif; ?>

            </div>
        </div>

    <?php endforeach; ?>
</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-7">
                <div class="card border shadow-none mb-3">
                    <div class="card-body">
                        <div class="card">
                            <div class="card-header">Kondisi Lapangan</div>
                            <div class="card-body">
                                <form method="post" action="<?= base_url('register/update_kondisi/'.$titpar['id']) ?>">
                                    <?= csrf_field() ?>

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">Melintang</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input kondisi-toggle" type="checkbox" id="melintang" name="melintang" value="1" data-target="#melintang_alasan_wrap" <?= !empty($titpar['melintang']) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="melintang">Tidak / Ya</label>
                                        </div>
                                        <div id="melintang_alasan_wrap" class="<?= empty($titpar['melintang']) ? 'd-none' : '' ?> mt-2">
                                            <textarea class="form-control" name="melintang_alasan" rows="2" placeholder="Alasan"><?= esc($titpar['melintang_alasan']) ?></textarea>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">Menyebabkan Kemacetan</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input kondisi-toggle" type="checkbox" id="macet" name="macet" value="1" data-target="#macet_alasan_wrap" <?= !empty($titpar['macet']) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="macet">Tidak / Ya</label>
                                        </div>
                                        <div id="macet_alasan_wrap" class="<?= empty($titpar['macet']) ? 'd-none' : '' ?> mt-2">
                                            <textarea class="form-control" name="macet_alasan" rows="2" placeholder="Alasan"><?= esc($titpar['macet_alasan']) ?></textarea>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Dampak Sosial</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input kondisi-toggle" type="checkbox" id="sosial" name="sosial" value="1" data-target="#sosial_alasan_wrap" <?= !empty($titpar['sosial']) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="sosial">Tidak / Ya</label>
                                        </div>
                                        <div id="sosial_alasan_wrap" class="<?= empty($titpar['sosial']) ? 'd-none' : '' ?> mt-2">
                                            <textarea class="form-control" name="sosial_alasan" rows="2" placeholder="Alasan"><?= esc($titpar['sosial_alasan']) ?></textarea>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary mt-2 btn-auto-sm">
                                            <i class="fa fa-save"></i> Simpan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if($titpar['latitude'] && $titpar['longitude']): ?>
                    <div class="card border shadow-none mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fa fa-map-marker text-primary me-2"></i>Lokasi Peta
                            </h6>

                            <button
                                type="button"
                                class="btn btn-sm btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEdit">
                                <i class="fa fa-pencil"></i> Edit Lokasi
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <iframe src="https://maps.google.com/maps?q=<?= $titpar['latitude'] ?>,<?= $titpar['longitude'] ?>&z=17&output=embed" width="100%" height="240" style="border:0; display:block;"></iframe>
                            <div class="p-3">
                                <small class="text-muted d-block mb-2">
                                    <?= $titpar['latitude'] ?> , <?= $titpar['longitude'] ?>
                                </small>
                                <a href="https://maps.google.com/?q=<?= $titpar['latitude'] ?>,<?= $titpar['longitude'] ?>" target="_blank" class="btn btn-sm btn-outline-primary btn-auto-sm">
                                    <i class="fa fa-external-link"></i> Buka Google Maps
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif ?>
            </div>

            <div class="col-12">
                <div class="card border shadow-none">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Potensi Kendaraan</h6>

                    <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#modalPotensi">
                        <i class="fa fa-pencil"></i> Edit
                    </button>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm text-center mb-0">
                            <thead>
                                <tr>
                                    <th>Senin</th>
                                    <th>Selasa</th>
                                    <th>Rabu</th>
                                    <th>Kamis</th>
                                    <th>Jumat</th>
                                    <th>Sabtu</th>
                                    <th>Minggu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?= esc($titpar['potensi_senin']) ?></td>
                                    <td><?= esc($titpar['potensi_selasa']) ?></td>
                                    <td><?= esc($titpar['potensi_rabu']) ?></td>
                                    <td><?= esc($titpar['potensi_kamis']) ?></td>
                                    <td><?= esc($titpar['potensi_jumat']) ?></td>
                                    <td><?= esc($titpar['potensi_sabtu']) ?></td>
                                    <td><?= esc($titpar['potensi_minggu']) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                </div>
            </div>

            <div class="card border shadow-none mb-3">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fa fa-folder-open me-2 text-primary"></i>
            Dokumen Pendukung
        </h6>
    </div>

    <div class="card-body">

        <div class="row g-3">

        <?php foreach($documents as $doc): ?>

            <?php
            $file = $titpar[$doc['field']] ?? '';
            $url  = $file ? base_url($doc['folder'].$file) : '';
            $ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $isImage = in_array($ext,['jpg','jpeg','png','gif','webp']);
            ?>

            <div class="col-md-4 col-sm-6">

                <div class="border rounded p-2 h-100">

                    <div class="d-flex justify-content-between align-items-center mb-2">

                        <strong><?= esc($doc['label']) ?></strong>

                        <button
                            class="btn btn-sm btn-outline-primary btn-upload-trigger"
                            data-id="<?= $titpar['id'] ?>"
                            data-field="<?= $doc['field'] ?>"
                            data-title="<?= $doc['label'] ?>">
                            <i class="fa fa-pencil"></i>
                        </button>

                    </div>

                    <?php if($file): ?>

                        <?php if($isImage): ?>

                            <a href="<?= $url ?>" target="_blank">
                                <img
                                    src="<?= $url ?>"
                                    class="img-fluid rounded border mb-2"
                                    style="height:170px;width:100%;object-fit:cover;">
                            </a>

                        <?php else: ?>

                            <div class="text-center border rounded py-4 mb-2 bg-light">

                                <i class="fa fa-file-pdf fa-3x text-danger"></i>

                            </div>

                        <?php endif; ?>

                        <div class="text-center">

                            <a
                                href="<?= $url ?>"
                                target="_blank"
                                class="btn btn-sm btn-success">

                                <i class="fa fa-eye"></i>
                                Lihat Berkas

                            </a>

                        </div>

                    <?php else: ?>

                        <div class="text-center py-5 border rounded bg-light">

                            <i class="fa fa-file-o fa-2x text-muted"></i>

                            <div class="small text-muted mt-2">
                                Belum diupload
                            </div>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        <?php endforeach; ?>

        </div>

    </div>

</div>

        </div>
    </div>
</div>

<!--
    NOTE: the original file had this entire modal + map script + toggle
    script block duplicated further down the page, both sharing
    id="modalEdit". That's invalid HTML (duplicate IDs) and caused the
    Leaflet map / event listeners to be initialized twice on the same
    #map element. The duplicate block has been removed — this is now
    the single source of truth.
-->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <form method="post" action="<?= base_url('register/titpar/updateAdmin/'.$titpar['id']) ?>" class="w-100">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Titik Parkir</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nama Tempat</label>
                                <input type="text" name="titpar_namatempat" class="form-control" value="<?= esc($titpar['titpar_namatempat']) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Lokasi</label>
                                <textarea class="form-control" name="titpar_lokasi" rows="2"><?= esc($titpar['titpar_lokasi']) ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Batas Parkir</label>
                                <input class="form-control" name="batas_parkir" value="<?= esc($titpar['batas_parkir']) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Jam Operasional</label>
                                <input class="form-control" name="jam_operasional" value="<?= esc($titpar['jam_operasional']) ?>">
                            </div>

                            <?php $jenis = (int)($titpar['jenis_kendaraan'] ?? 0); ?>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Jenis Kendaraan</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="jenis_kendaraan[]" id="motor" value="motor" <?= ($jenis & 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="motor">Motor</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="jenis_kendaraan[]" id="mobil" value="mobil" <?= ($jenis & 2) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="mobil">Mobil</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="jenis_kendaraan[]" id="truk" value="truk" <?= ($jenis & 4) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="truk">Truk</label>
                                </div>
                            </div>

                          <div class="mb-3">
                            <label class="form-label fw-semibold">Status</label>
                            <select class="form-select" name="status">
                                <option value="0" <?= (int)$titpar['status'] === 0 ? 'selected' : '' ?>>
                                    Pending
                                </option>
                                <option value="1" <?= (int)$titpar['status'] === 1 ? 'selected' : '' ?>>
                                    Layak
                                </option>
                                <option value="2" <?= (int)$titpar['status'] === 2 ? 'selected' : '' ?>>
                                    Tidak Layak
                                </option>
                            </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Catatan</label>
                                <textarea class="form-control" name="catatan"> <?= $titpar['catatan'] ?></textarea>
                            </div>

                            <input type="hidden" id="latitude" name="latitude" value="<?= $titpar['latitude'] ?>">
                            <input type="hidden" id="longitude" name="longitude" value="<?= $titpar['longitude'] ?>">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Pilih Lokasi di Peta</label>
                            <div id="map" class="w-100" style="min-height:280px; height:55vh; max-height:450px; border-radius:8px; border:1px solid #dee2e6;"></div>
                            <div class="mt-2 small text-muted">
                                Latitude: <span id="latText" class="fw-medium"><?= $titpar['latitude'] ?></span><br>
                                Longitude: <span id="lngText" class="fw-medium"><?= $titpar['longitude'] ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- modal potensi -->

<div class="modal fade" id="modalPotensi" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="<?= base_url('register/update_potensi/'.$titpar['id']) ?>">
            <?= csrf_field() ?>

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Potensi Kendaraan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <?php
                    $hari = [
                        'senin'  => 'Senin',
                        'selasa' => 'Selasa',
                        'rabu'   => 'Rabu',
                        'kamis'  => 'Kamis',
                        'jumat'  => 'Jumat',
                        'sabtu'  => 'Sabtu',
                        'minggu' => 'Minggu'
                    ];
                    ?>

                    <?php foreach ($hari as $key => $label): ?>
                        <div class="mb-3">
                            <label class="form-label"><?= $label ?></label>
                            <input
                                type="number"
                                min="0"
                                class="form-control"
                                name="potensi_<?= $key ?>"
                                value="<?= esc($titpar['potensi_'.$key]) ?>">
                        </div>
                    <?php endforeach; ?>

                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit"
                            class="btn btn-primary">
                        <i class="fa fa-save"></i> Simpan
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>

<!-- //modal  -->

<div class="modal fade" id="universalUploadModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="universalUploadForm" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalTitle"></h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" id="upload_record_id" name="id">

                    <input
                        type="file"
                        id="upload_file"
                        class="form-control"
                        required>

                    <small class="text-muted mt-2 d-block">
                        Format: PDF, JPG, JPEG, PNG
                    </small>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary">
                        Upload
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<!-- modal admin file -->

<div class="modal fade" id="uploadAdminModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="uploadAdminForm" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="modal-header">
                    <h5 id="uploadAdminTitle"></h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="id" id="uploadAdminId">

                    <input
                        type="file"
                        class="form-control"
                        id="uploadAdminFile"
                        required>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary w-100">
                        Upload
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<?= view('footer') ?>

<script>
    let map, marker;
    const lat = <?= $titpar['latitude'] ?: '-6.200000' ?>;
    const lng = <?= $titpar['longitude'] ?: '106.816666' ?>;

    $('#modalEdit').on('shown.bs.modal', function(){
        if (map) {
            map.invalidateSize();
            map.setView([
                $('#latitude').val(),
                $('#longitude').val()
            ], map.getZoom());
            marker.setLatLng([
                $('#latitude').val(),
                $('#longitude').val()
            ]);
            return;
        }

        map = L.map('map').setView([lat,lng], 17);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        marker = L.marker([lat,lng],{ draggable: true }).addTo(map);

        marker.on('dragend', function(){
            let p = marker.getLatLng();
            $('#latitude').val(p.lat);
            $('#longitude').val(p.lng);
            $('#latText').text(p.lat.toFixed(6));
            $('#lngText').text(p.lng.toFixed(6));
        });

        map.on('click', function(e){
            marker.setLatLng(e.latlng);
            $('#latitude').val(e.latlng.lat);
            $('#longitude').val(e.latlng.lng);
            $('#latText').text(e.latlng.lat.toFixed(6));
            $('#lngText').text(e.latlng.lng.toFixed(6));
        });
    });
</script>

<script>
    document.querySelectorAll('.kondisi-toggle').forEach(function(el){
        function update(){
            const target = document.querySelector(el.dataset.target);
            if(el.checked){
                target.classList.remove('d-none');
            }else{
                target.classList.add('d-none');
            }
        }
        update();
        el.addEventListener('change', update);
    });
</script>

<script>
    
    let currentUploadField='';

$(document).on('click','.btn-upload-trigger',function(){

    currentUploadField=$(this).data('field');

    $('#upload_record_id').val($(this).data('id'));

    $('#uploadModalTitle').text($(this).data('title'));

    $('#upload_file')
        .val('')
        .attr('name',currentUploadField);

    bootstrap.Modal.getOrCreateInstance(
        document.getElementById('universalUploadModal')
    ).show();

});


$('#universalUploadForm').submit(function(e){

    e.preventDefault();

    let fd=new FormData(this);

    $.ajax({

        url:"<?= site_url('register/uploadTitpar/') ?>"+currentUploadField,

        type:"POST",

        data:fd,

        processData:false,

        contentType:false,

        dataType:'json',

        success:function(res){

            if(res.status){

                location.reload();

            }else{

                alert(res.message);

            }

        }

    });

});
</script>


<script>
    let adminField = '';

$(document).on('click','.btn-upload-admin',function(){

    adminField = $(this).data('field');

    $('#uploadAdminId').val($(this).data('id'));

    $('#uploadAdminTitle').text($(this).data('title'));

    $('#uploadAdminFile')
        .val('')
        .attr('name',adminField);

    new bootstrap.Modal('#uploadAdminModal').show();

});

$('#uploadAdminForm').submit(function(e){

    e.preventDefault();

    let fd = new FormData(this);

    $.ajax({

        url : "<?= site_url('register/uploadTitpar/') ?>" + adminField,

        type : 'POST',

        data : fd,

        processData : false,

        contentType : false,

        success:function(res){

            if(res.status){
                location.reload();
            }else{
                alert(res.message);
            }

        }

    });

});
</script>