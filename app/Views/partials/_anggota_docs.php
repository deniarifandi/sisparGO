<?php
$docs = [
    ['field' => 'anggota_foto', 'label' => 'Foto Selfie', 'folder' => 'anggota_foto'],
    ['field' => 'foto_ktp',     'label' => 'Foto KTP',    'folder' => 'foto_ktp'],
    ['field' => 'kk',           'label' => 'Foto KK',     'folder' => 'kk'],
];
?>

<hr>
<h6 class="mb-3"><i class="fa fa-images text-primary me-2"></i>Dokumen Anggota</h6>

<div class="row g-3">
    <?php foreach ($docs as $doc):
        $file = $anggota[$doc['field']] ?? '';
        $url  = $file ? base_url('uploads/' . $doc['folder'] . '/' . $file) : '';
    ?>
    <div class="col-md-4 col-sm-6">
        <div class="border rounded p-2 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong><?= $doc['label'] ?></strong>
                <button type="button"
                        class="btn btn-sm btn-outline-primary btn-upload-trigger"
                        data-id="<?= $anggota['id'] ?>"
                        data-field="<?= $doc['field'] ?>"
                        data-title="<?= $doc['label'] ?>">
                    <i class="fa fa-pencil"></i>
                </button>
            </div>

            <?php if ($file): ?>
                <a href="<?= $url ?>" target="_blank">
                    <img src="<?= $url ?>" class="img-fluid rounded border"
                         style="height:170px;width:100%;object-fit:cover;">
                </a>
            <?php else: ?>
                <div class="text-center py-5 border rounded bg-light text-muted">
                    <i class="fa fa-image fa-2x mb-2"></i><br>Belum diupload
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>