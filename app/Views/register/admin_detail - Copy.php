<?php echo view('header'); ?>

<style>
.profile-img {
    width: 90px; height: 90px;
    object-fit: cover; border-radius: 50%;
    border: 3px solid #dee2e6;
}
.profile-placeholder {
    width: 90px; height: 90px;
    border-radius: 50%; border: 3px solid #dee2e6;
    background: #f8f9fa;
    display: flex; align-items: center; justify-content: center;
}
@media (min-width: 768px) {
    .profile-img, .profile-placeholder { width: 110px; height: 110px; }
}
.nav-tabs { flex-wrap: nowrap; overflow-x: auto; -webkit-overflow-scrolling: touch; }
.nav-tabs .nav-link { font-size: 13px; font-weight: 500; white-space: nowrap; }
.nav-tabs .nav-link.active { font-weight: 600; }
.lbl {
    font-size: 11px; font-weight: 600;
    color: #6c757d; text-transform: uppercase; letter-spacing: 0.4px;
}
.val { font-size: 13px; color: #212529; word-break: break-word; }
.doc-thumb {
    width: 100%; height: 160px;
    object-fit: cover; border-radius: 6px;
    border: 1px solid #dee2e6; cursor: pointer;
    transition: opacity .2s;
}
@media (min-width: 768px) {
    .doc-thumb { height: 250px; }
}
.doc-thumb:hover { opacity: .85; }
.doc-empty {
    height: 130px; border-radius: 6px;
    border: 1px dashed #ced4da; background: #f8f9fa;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; color: #adb5bd;
}
.potensi-box {
    text-align: center; padding: 12px 6px;
    border-radius: 8px; background: #f8f9fa;
    border: 1px solid #e9ecef;
}
.potensi-box .day  { font-size: 11px; color: #6c757d; font-weight: 600; }
.potensi-box .cnt  { font-size: 22px; font-weight: 700; color: #0d6efd; line-height: 1.2; }
.cond-badge        { min-width: 38px; text-align: center; }
.section-header    { font-size: 13px; font-weight: 600; }

/* Responsive label/value table: stack on very small screens */
@media (max-width: 420px) {
    .lv-table td.lbl, .lv-table td.val {
        display: block;
        width: 100% !important;
        padding-top: 2px;
        padding-bottom: 2px;
    }
    .lv-table td.lbl { padding-bottom: 0; }
    .lv-table tr { display: block; margin-bottom: 6px; }
}

/* Created-at / catatan block: stacked under header on mobile instead of hidden */
.meta-block {
    text-align: left;
    margin-top: 10px;
}
@media (min-width: 768px) {
    .meta-block {
        text-align: right;
        margin-top: 0;
    }
}
</style>

<div class="card shadow-sm mb-3">

    <!-- ── Top Header ── -->
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Detail Pendaftaran KTA</h5>
        <a class="btn btn-outline-secondary btn-sm" href="<?= base_url('/perijinan') ?>">
            <i class="fa fa-arrow-left me-1"></i><span class="d-none d-sm-inline">Kembali</span>
        </a>
    </div>

    <!-- ── Profile Summary ── -->
    <div class="card-body border-bottom pb-3">
        <div class="row align-items-center g-3">

            <?php if (session()->getFlashdata('success')): ?>
                <div class="col-12">
                    <div class="alert alert-success alert-dismissible fade show mb-0">
                        <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('warning')): ?>
                <div class="col-12">
                    <div class="alert alert-warning alert-dismissible fade show mb-0">
                        <?= session()->getFlashdata('warning') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="col-12">
                    <div class="alert alert-danger alert-dismissible fade show mb-0">
                        <?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            <?php endif; ?>

            <div class="col-auto">
                <?php if (!empty($register['anggota_foto'])): ?>
                    <img src="<?= base_url('uploads/anggota_foto/'.$register['anggota_foto']) ?>" class="profile-img" alt="Foto">
                <?php else: ?>
                    <div class="profile-placeholder">
                        <i class="fa fa-user fa-2x text-secondary"></i>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col">
                <h5 class="mb-1 fw-bold"><?= esc($register['anggota_nama']) ?></h5>
                <div class="text-muted small mb-1">
                    <i class="fa fa-id-card me-1"></i><?= esc($register['anggota_noktp']) ?>
                    <br class="d-sm-none">
                    <span class="d-none d-sm-inline">&nbsp;·&nbsp;</span>
                    <i class="fa fa-phone me-1"></i><?= esc($register['hp'] ?? '-') ?>
                </div>
                <div class="text-muted small mb-2">
                    <i class="fa fa-credit-card me-1"></i>VA:
                    <strong class="text-primary"><?= esc($register['register_va'] ?? '-') ?></strong>
                </div>
                <div class="d-flex flex-wrap gap-1">
                     <?php
                        $status = (int) ($register['anggota_status'] ?? 0);

                        switch ($status) {
                            case 1:
                                $statusText = 'Layak';
                                $statusClass = 'success';
                                break;

                            case 2:
                                $statusText = 'Tidak Layak';
                                $statusClass = 'danger';
                                break;

                            default:
                                $statusText = 'Pending';
                                $statusClass = 'warning';
                                break;
                        }
                        ?>

                    <span class="badge bg-<?= $statusClass ?>">
                        <?= ucfirst(esc($statusText ?? '-')) ?>
                    </span>
                    <?php if (!empty($register['baru'])): ?>
                        <span class="badge bg-info text-dark">Baru</span>
                    <?php endif; ?>
                    <?php if (!empty($register['masa_berlaku'])): ?>
                        <span class="badge bg-light text-dark border">
                            <i class="fa fa-calendar me-1"></i>Berlaku: <?= esc($register['masa_berlaku']) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <!--
                    Moved out of d-none d-md-block so this info is no longer
                    hidden/lost on mobile — it now stacks under the profile
                    block instead of disappearing.
                -->
                <div class="meta-block text-muted small">
                    <div>Didaftarkan: <span class="fw-semibold text-dark"><?= date('d M Y', strtotime($register['created_at'])) ?></span></div>
                    <?php if (!empty($register['catatan'])): ?>
                        <div class="mt-1 text-danger"><i class="fa fa-sticky-note me-1"></i><?= esc($register['catatan']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Tabs ── -->
    <div class="card-body pt-3">

        <ul class="nav nav-tabs mb-3" id="detailTabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#tab-register">
                    <i class="fa fa-file-alt me-1"></i>Pendaftaran
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-anggota">
                    <i class="fa fa-user me-1"></i>Anggota
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-titpar">
                    <i class="fa fa-map-marker-alt me-1"></i>Titik Parkir
                </a>
            </li>
        </ul>

        <div class="tab-content">

            <!-- ════════ TAB 1 : DATA PENDAFTARAN ════════ -->
            <div class="tab-pane fade show active" id="tab-register">
                <div class="row g-3">

                    <!-- Info Umum -->
                    <div class="col-12 col-md-5">
                        <div class="card border shadow-none bg-light h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <p class="section-header mb-0"><i class="fa fa-info-circle me-2 text-primary"></i>Informasi Umum</p>
                                    <button
                                        class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEditRegister">
                                        <i class="fa fa-pencil"></i> Edit
                                    </button>
                                </div>

                                <table class="table table-borderless table-sm mb-0 lv-table" style="font-size:13px;">
                                    <tr>
                                        <td class="lbl" width="42%">Nama</td>
                                        <td class="val"><?= esc($register['anggota_nama']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="lbl">No. KTP</td>
                                        <td class="val"><?= esc($register['anggota_noktp']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="lbl">Tgl. Lahir</td>
                                        <td class="val"><?= !empty($register['anggota_tgllhr']) ? date('d M Y', strtotime($register['anggota_tgllhr'])) : '-' ?></td>
                                    </tr>
                                    <tr>
                                        <td class="lbl">No. HP</td>
                                        <td class="val"><?= esc($register['hp'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="lbl">Alamat</td>
                                        <td class="val"><?= esc($register['anggota_alamat'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="lbl">Virtual Account</td>
                                        <td class="val fw-semibold text-primary"><?= esc($register['register_va'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="lbl">ID Anggota</td>
                                        <td class="val"><?= esc($register['register_anggotaid'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="lbl">Masa Berlaku</td>
                                        <td class="val"><?= esc($register['masa_berlaku'] ?? '-') ?></td>
                                    </tr>
                                    <?php
                                        $status = (int) ($register['anggota_status'] ?? 0);

                                        switch ($status) {
                                            case 1:
                                                $statusText = 'Layak';
                                                $statusClass = 'success';
                                                break;

                                            case 2:
                                                $statusText = 'Tidak Layak';
                                                $statusClass = 'danger';
                                                break;

                                            default:
                                                $statusText = 'Pending';
                                                $statusClass = 'warning';
                                                break;
                                        }
                                        ?>

                                        <tr>
                                            <td class="lbl">Status</td>
                                            <td class="val">
                                                <span class="badge bg-<?= $statusClass ?>">
                                                    <?= $statusText ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php if (!empty($register['catatan'])): ?>
                                    <tr>
                                        <td class="lbl">Catatan</td>
                                        <td class="val text-danger"><?= esc($register['catatan']) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td class="lbl">Dibuat</td>
                                        <td class="val"><?= date('d M Y H:i', strtotime($register['created_at'])) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="lbl">Diperbarui</td>
                                        <td class="val"><?= date('d M Y H:i', strtotime($register['updated_at'])) ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Dokumen + Peta -->
                    <div class="col-12 col-md-7">

                        <!-- Dokumen -->
                        <div class="card border shadow-none bg-light mb-3">
                            <div class="card-body">
                                <p class="section-header mb-3">
                                    <i class="fa fa-images me-2 text-primary"></i>Dokumen
                                </p>

                                <?php
                                $documents = [
                                    [
                                        'label'  => 'Foto KTP',
                                        'field'  => 'foto_ktp',
                                        'folder' => 'uploads/foto_ktp/'
                                    ],
                                    [
                                        'label'  => 'Kartu Keluarga',
                                        'field'  => 'kk',
                                        'folder' => 'uploads/kk/'
                                    ]
                                ];
                                ?>

                                <div class="row g-3">
                                    <?php foreach ($documents as $doc): ?>
                                        <?php $file = $register[$doc['field']] ?? ''; ?>

                                        <div class="col-12 col-sm-6">
                                            <div class="border rounded p-2 bg-light">

                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <strong><?= $doc['label'] ?></strong>

                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-primary btn-upload-trigger"
                                                        data-id="<?= $register['id'] ?>"
                                                        data-field="<?= $doc['field'] ?>"
                                                        data-title="<?= $doc['label'] ?>">
                                                        <i class="fa fa-pencil"></i>
                                                    </button>
                                                </div>

                                                <?php if ($file): ?>
                                                    <a href="<?= base_url($doc['folder'] . $file) ?>" target="_blank">
                                                        <img
                                                            src="<?= base_url($doc['folder'] . $file) ?>"
                                                            class="doc-thumb img-fluid rounded border"
                                                            alt="<?= $doc['label'] ?>">
                                                    </a>
                                                <?php else: ?>
                                                    <div class="text-center text-muted py-5 border rounded">
                                                        <i class="fa fa-image fa-2x mb-2"></i><br>
                                                        Belum diupload
                                                    </div>
                                                <?php endif; ?>

                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Peta -->
                        <?php if (!empty($register['latitude']) && !empty($register['longitude'])): ?>
                        <div class="card border shadow-none bg-light">
                            <div class="card-body p-0 overflow-hidden" style="border-radius:8px;">
                                <div class="px-3 pt-3 pb-2">
                                    <p class="section-header mb-0"><i class="fa fa-map-marker-alt me-2 text-danger"></i>Koordinat Lokasi</p>
                                </div>
                                <iframe
                                    src="https://maps.google.com/maps?q=<?= $register['latitude'] ?>,<?= $register['longitude'] ?>&z=16&output=embed"
                                    width="100%" height="180" style="border:0; display:block;" loading="lazy">
                                </iframe>
                                <div class="px-3 py-2 small text-muted">
                                    <span class="d-block d-sm-inline"><?= $register['latitude'] ?>, <?= $register['longitude'] ?></span>
                                    <a href="https://maps.google.com/?q=<?= $register['latitude'] ?>,<?= $register['longitude'] ?>" target="_blank" class="ms-sm-2">
                                        <i class="fa fa-external-link-alt"></i> Buka Maps
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div><!-- /col -->
                </div><!-- /row -->
            </div>

            <!-- ════════ TAB 2 : DATA ANGGOTA ════════ -->
            <div class="tab-pane fade" id="tab-anggota">
                <?php if (!empty($anggota)): ?>
                <div class="row g-3">
                    <div class="col-12 col-md-5">
                        <div class="card border shadow-none bg-light h-100">
                            <div class="card-body">
                                <p class="section-header mb-3"><i class="fa fa-user-circle me-2 text-primary"></i>Data Diri Anggota</p>
                                <table class="table table-borderless table-sm mb-0 lv-table" style="font-size:13px;">
                                    <tr>
                                        <td class="lbl" width="44%">Nama</td>
                                        <td class="val"><?= esc($anggota['anggota_nama']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="lbl">No. KTP</td>
                                        <td class="val"><?= esc($anggota['anggota_noktp']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="lbl">Tgl. Lahir</td>
                                        <td class="val"><?= !empty($anggota['tgl_lahir']) ? date('d M Y', strtotime($anggota['tgl_lahir'])) : '-' ?></td>
                                    </tr>
                                    <tr>
                                        <td class="lbl">Alamat</td>
                                        <td class="val"><?= esc($anggota['alamat'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="lbl">No. KTP Koordinator</td>
                                        <td class="val"><?= esc($anggota['koordinator_noktp'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="lbl">Status</td>
                                        <td class="val">
                                            <?php
                                            $aStClass = $stMap[strtolower($anggota['status'] ?? '')] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?= $aStClass ?>"><?= ucfirst(esc($anggota['status'] ?? '-')) ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="lbl">Dibuat</td>
                                        <td class="val"><?= date('d M Y H:i', strtotime($anggota['created_at'])) ?></td>
                                    </tr>
                                    <?php if (!empty($anggota['deleted_at'])): ?>
                                    <tr>
                                        <td class="lbl">Dihapus</td>
                                        <td class="val text-danger"><?= date('d M Y H:i', strtotime($anggota['deleted_at'])) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-7">
                        <div class="card border shadow-none bg-light">
                            <div class="card-body">
                                <p class="section-header mb-3"><i class="fa fa-images me-2 text-primary"></i>Dokumen Anggota</p>
                                <div class="row g-2">
                                    <?php
                                    $aDocs = ['Foto' => $anggota['foto'] ?? '', 'KTP' => $anggota['ktp'] ?? '', 'KK' => $anggota['kk'] ?? ''];
                                    foreach ($aDocs as $lbl => $path): ?>
                                    <div class="col-4">
                                        <div class="lbl mb-1"><?= $lbl ?></div>
                                        <?php if (!empty($path)): ?>
                                            <a href="<?= base_url($path) ?>" target="_blank">
                                                <img src="<?= base_url($path) ?>" class="doc-thumb" style="height:120px;" alt="<?= $lbl ?>">
                                            </a>
                                        <?php else: ?>
                                            <div class="doc-empty" style="height:120px;"><i class="fa fa-image me-1"></i>Kosong</div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-warning mt-2">
                    <i class="fa fa-exclamation-triangle me-2"></i>Data anggota tidak ditemukan.
                </div>
                <?php endif; ?>
            </div>

            <!-- ════════ TAB 3 : TITIK PARKIR ════════ -->
            <div class="tab-pane fade" id="tab-titpar">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="card border shadow-none mb-3">
                            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <span>
                                    <i class="fa fa-map-marker-alt me-2 text-primary"></i>
                                    Titik Parkir
                                </span>

                             <!--    <a href="<?= base_url('titpar/create?register_id='.$register['id']) ?>"
                                   class="btn btn-sm btn-primary">
                                    <i class="fa fa-plus"></i> Tambah
                                </a> -->
                            </div>

                            <div class="card-body">

                                <?php if (empty($titpars)): ?>

                                    <div class="text-muted">
                                        Belum ada titik parkir.
                                    </div>

                                <?php else: ?>

                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Nama Tempat</th>
                                                    <th>Lokasi</th>
                                                    <th>Jam Operasional</th>
                                                    <th>Status</th>
                                                    <th width="150">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                            <?php foreach ($titpars as $t): ?>

                                                <tr>
                                                    <td><?= esc($t['titpar_namatempat']) ?></td>
                                                    <td><?= esc($t['titpar_lokasi']) ?></td>
                                                    <td><?= esc($t['jam_operasional']) ?></td>

                                                    <td>
                                                        <?php
$status = (int)($t['status'] ?? 0);

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

                                                    <td>
                                                        <a href="<?= base_url('titpar/detail/'.$t['id']) ?>"
                                                           class="btn btn-sm btn-primary">
                                                            Detail
                                                        </a>
                                                    </td>
                                                </tr>

                                            <?php endforeach; ?>

                                            </tbody>
                                        </table>
                                    </div>

                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </div><!-- /row titpar -->
            </div><!-- /tab-pane titpar -->

        </div><!-- /tab-content -->
    </div><!-- /card-body -->
</div><!-- /card -->

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
                    <input type="hidden" name="id" id="upload_record_id">

                    <input type="file"
                           class="form-control"
                           id="upload_file"
                           required>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary w-100" id="btnSubmitUpload">
                        Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditRegister" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="<?= base_url('register/admin/update') ?>">
            <?= csrf_field() ?>

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa fa-user-edit me-2"></i>Edit Informasi Umum
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="id" value="<?= $register['id'] ?>">

                    <div class="row g-3">

                        <div class="col-12 col-md-6">
                            <label class="form-label">Nama</label>
                            <input type="text"
                                   class="form-control"
                                   name="anggota_nama"
                                   value="<?= esc($register['anggota_nama']) ?>">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">No. KTP</label>
                            <input type="text"
                                   class="form-control"
                                   value="<?= esc($register['anggota_noktp']) ?>"
                                   readonly>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date"
                                   class="form-control"
                                   name="anggota_tgllhr"
                                   value="<?= $register['anggota_tgllhr']  ?? ''?>">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">No. HP</label>
                            <input type="text"
                                   class="form-control"
                                   name="hp"
                                   value="<?= esc($register['hp'] ?? '') ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Alamat</label>
                            <textarea class="form-control"
                                      name="anggota_alamat"
                                      rows="3"><?= esc($register['anggota_alamat'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Virtual Account</label>
                            <input type="text"
                                   class="form-control"
                                   value="<?= esc($register['register_va'] ?? '') ?>"
                                   readonly>
                        </div>

                      <div class="col-12 col-md-6">
                        <label class="form-label">ID Anggota</label>

                        <div class="input-group flex-wrap">
                            <input type="text"
                                   class="form-control"
                                   value="<?= esc($register['register_anggotaid'] ?? '') ?>"
                                   readonly>

                            <?php if (empty($register['register_anggotaid'])): ?>

                                <?php if ((int)$register['anggota_status'] === 1): ?>
                                        <button
                                        type="submit"
                                        form="formRegisterAnggota"
                                        class="btn btn-success"
                                        onclick="return confirm('Register ID Anggota?')">
                                        Register
                                    </button>
                                <?php else: ?>

                                    <button class="btn btn-secondary" disabled>
                                        Register
                                    </button>

                                <?php endif; ?>

                            <?php endif; ?>
                        </div>

                        <?php if ((int)$register['anggota_status'] !== 1 && empty($register['register_anggotaid'])): ?>
                            <small class="text-muted">
                                ID Anggota hanya dapat dibuat jika status <strong>Layak</strong>.
                            </small>
                        <?php endif; ?>
                    </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Masa Berlaku</label>
                            <input type="date"
                                   class="form-control"
                                   name="masa_berlaku"
                                   value="<?= $register['masa_berlaku']  ?? ''?>">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="anggota_status">
                                <option value="0" <?= $register['anggota_status']==0?'selected':'' ?>>Pending</option>
                                <option value="1" <?= $register['anggota_status']==1?'selected':'' ?>>Layak</option>
                                <option value="2" <?= $register['anggota_status']==2?'selected':'' ?>>Tidak Layak</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Catatan</label>
                            <textarea class="form-control"
                                      name="catatan"
                                      rows="3"><?= esc($register['catatan']) ?></textarea>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Dibuat</label>
                            <input type="text"
                                   class="form-control"
                                   value="<?= date('d M Y H:i', strtotime($register['created_at'])) ?>"
                                   readonly>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Diperbarui</label>
                            <input type="text"
                                   class="form-control"
                                   value="<?= date('d M Y H:i', strtotime($register['updated_at'] ?? '')) ?>"
                                   readonly>
                        </div>

                    </div>

                </div>

                 <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit" class="btn btn-primary">
                        Simpan Perubahan
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<form id="formRegisterAnggota"
      method="post"
      action="<?= base_url('register/registeranggota') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= $register['id'] ?>">
</form>

<?= view('footer'); ?>

<script>
    let currentUploadField = '';

$(document).on('click','.btn-upload-trigger',function(){

    currentUploadField = $(this).data('field');

    $('#upload_record_id').val($(this).data('id'));
    $('#uploadModalTitle').text($(this).data('title'));

    $('#upload_file')
        .val('')
        .attr('name', currentUploadField);

    new bootstrap.Modal('#universalUploadModal').show();
});

$('#universalUploadForm').submit(function(e){

    e.preventDefault();

    let fd = new FormData(this);

    $.ajax({
        url: "<?= site_url('api/upload/') ?>" + currentUploadField,
        type: "POST",
        data: fd,
        processData:false,
        contentType:false,
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