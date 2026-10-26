<?php
$adminDocs = [
    ['field' => 'foto_tampak_depan',   'label' => 'Foto Tampak Depan',   'folder' => 'foto_tampak_depan'],
    ['field' => 'foto_tampak_samping', 'label' => 'Foto Tampak Samping', 'folder' => 'foto_tampak_samping'],
];
?>

<hr>
<h6 class="mb-3">
    <i class="fa fa-camera text-primary me-2"></i>Dokumentasi Lapangan (Admin)
</h6>

<div class="row g-3">
    <?php foreach ($adminDocs as $doc):
        $file = $titpar[$doc['field']] ?? '';
    ?>
    <div class="col-12">
        <div class="border rounded p-2">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong><?= $doc['label'] ?></strong>
                <button type="button"
                        class="btn btn-sm btn-outline-primary btn-upload-admin"
                        data-id="<?= $titpar['id'] ?>"
                        data-field="<?= $doc['field'] ?>"
                        data-title="<?= $doc['label'] ?>">
                    <i class="fa fa-upload"></i> Upload
                </button>
            </div>

            <?php if ($file): ?>
                <a href="<?= base_url('uploads/' . $doc['folder'] . '/' . $file) ?>" target="_blank">
                    <img src="<?= base_url('uploads/' . $doc['folder'] . '/' . $file) ?>"
                         class="img-fluid rounded border" style="max-height:220px;object-fit:cover;width:100%;">
                </a>
            <?php else: ?>
                <div class="text-center py-5 border rounded text-muted">Belum ada foto</div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>