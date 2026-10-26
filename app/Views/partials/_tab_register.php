<?php helper('admin'); ?>

<div class="row g-3">

    <!-- Info Umum -->
    <div class="col-12 col-md-5">
        <div class="card border shadow-none bg-light h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="section-header mb-0">
                        <i class="fa fa-info-circle me-2 text-primary"></i>Informasi Umum
                    </p>
                    <button class="btn btn-sm btn-outline-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEditRegister">
                        <i class="fa fa-pencil"></i> Edit
                    </button>
                </div>

                <table class="table table-borderless table-sm mb-0 lv-table" style="font-size:13px;">
                    <?php
                    $rows = [
                        'Nama'            => esc($register['anggota_nama']),
                        'No. KTP'         => esc($register['anggota_noktp']),
                        'Tgl. Lahir'      => !empty($register['anggota_tgllhr'])
                                                ? date('d M Y', strtotime($register['anggota_tgllhr']))
                                                : '-',
                        'No. HP'          => esc($register['hp'] ?? '-'),
                        'Alamat'          => esc($register['anggota_alamat'] ?? '-'),
                        'Virtual Account' => '<span class="fw-semibold text-primary">' . esc($register['register_va'] ?? '-') . '</span>',
                        'ID Anggota'      => esc($register['register_anggotaid'] ?? '-'),
                        'Masa Berlaku'    => esc($register['masa_berlaku'] ?? '-'),
                    ];
                    foreach ($rows as $label => $value): ?>
                    <tr>
                        <td class="lbl" width="42%"><?= $label ?></td>
                        <td class="val"><?= $value ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td class="lbl">Status</td>
                        <td class="val"><?= statusBadge((int)($register['anggota_status'] ?? 0)) ?></td>
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
                        <td class="val"><?= date('d M Y H:i', strtotime($register['updated_at'] ?? '')) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Dokumen + Peta -->
    <div class="col-12 col-md-7">

        <!-- Dokumen -->
        <!-- <div class="card border shadow-none bg-light mb-3">
            <div class="card-body">
                <p class="section-header mb-3">
                    <i class="fa fa-images me-2 text-primary"></i>Dokumen
                </p>
                <div class="row g-3">
                    <?php
                    $documents = [
                        ['label' => 'Foto KTP',       'field' => 'foto_ktp', 'folder' => 'uploads/foto_ktp/'],
                        ['label' => 'Kartu Keluarga', 'field' => 'kk',       'folder' => 'uploads/kk/'],
                    ];
                    foreach ($documents as $doc):
                        $file = $register[$doc['field']] ?? '';
                    ?>
                    <div class="col-12 col-sm-6">
                        <div class="border rounded p-2 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong><?= $doc['label'] ?></strong>
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary btn-upload-trigger"
                                        data-id="<?= $register['id'] ?>"
                                        data-field="<?= $doc['field'] ?>"
                                        data-title="<?= $doc['label'] ?>">
                                    <i class="fa fa-pencil"></i>
                                </button>
                            </div>
                            <?php if ($file): ?>
                                <a href="<?= base_url($doc['folder'] . $file) ?>" target="_blank">
                                    <img src="<?= base_url($doc['folder'] . $file) ?>"
                                         class="doc-thumb img-fluid rounded border"
                                         alt="<?= $doc['label'] ?>">
                                </a>
                            <?php else: ?>
                                <div class="text-center text-muted py-5 border rounded">
                                    <i class="fa fa-image fa-2x mb-2"></i><br>Belum diupload
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div> -->
        <form method="post" action="<?= base_url('admin/register/update-foto') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $register['id'] ?>">

        <?= view('partials/_upload_fields', [
                'uploadFields'       => [
                    'foto_ktp'      => 'Foto KTP',
                    'kk'            => 'Foto KK',
                    'anggota_foto'  => 'Swafoto'
                ],
                'existingData'       => $register ?? [],
                'isEdit'             => true,
                'uploadSectionTitle' => '2. Dokumen Pendukung',
            ]) ?>

         <div class="d-flex justify-content-end gap-4">
           
            <button type="submit" class="btn btn-success">
                <?= 'Simpan' ?>
            </button>
        </div>
        </form>

        <!-- upload new -->

        <!-- Peta -->
        <?php if (!empty($register['latitude']) && !empty($register['longitude'])): ?>
        <div class="card border shadow-none bg-light">
            <div class="card-body p-0 overflow-hidden" style="border-radius:8px;">
                <div class="px-3 pt-3 pb-2">
                    <p class="section-header mb-0">
                        <i class="fa fa-map-marker-alt me-2 text-danger"></i>Koordinat Lokasi
                    </p>
                </div>
                <iframe src="https://maps.google.com/maps?q=<?= $register['latitude'] ?>,<?= $register['longitude'] ?>&z=16&output=embed"
                        width="100%" height="180" style="border:0; display:block;" loading="lazy">
                </iframe>
                <div class="px-3 py-2 small text-muted">
                    <?= $register['latitude'] ?>, <?= $register['longitude'] ?>
                    <a href="https://maps.google.com/?q=<?= $register['latitude'] ?>,<?= $register['longitude'] ?>"
                       target="_blank" class="ms-2">
                        <i class="fa fa-external-link-alt"></i> Buka Maps
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>