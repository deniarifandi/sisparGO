<?php
$documents = [
    ['label' => 'Surat Kesanggupan', 'field' => 'kesanggupan',  'folder' => 'uploads/kesanggupan/'],
    ['label' => 'Surat Keberatan',   'field' => 'keberatan',    'folder' => 'uploads/keberatan/'],
    ['label' => 'Denah Lokasi',      'field' => 'denah',        'folder' => 'uploads/denah/'],
    ['label' => 'Berita Acara',      'field' => 'berita_acara', 'folder' => 'uploads/berita_acara/'],
    ['label' => 'Surat Penunjukan',  'field' => 'penunjukan',   'folder' => 'uploads/penunjukan/'],
];
?>

<div class="card border shadow-none mb-3">
    <div class="card-header">
        <h6 class="mb-0"><i class="fa fa-folder-open me-2 text-primary"></i>Dokumen Pendukung</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?php foreach ($documents as $doc):
                $file    = $titpar[$doc['field']] ?? '';
                $url     = $file ? base_url($doc['folder'] . $file) : '';
                $ext     = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp']);
            ?>
            <div class="col-md-4 col-sm-6">
                <div class="border rounded p-2 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong><?= esc($doc['label']) ?></strong>
                        <button class="btn btn-sm btn-outline-primary btn-upload-trigger"
                                data-id="<?= $titpar['id'] ?>"
                                data-field="<?= $doc['field'] ?>"
                                data-title="<?= $doc['label'] ?>">
                            <i class="fa fa-pencil"></i>
                        </button>
                    </div>

                    <?php if ($file): ?>
                        <?php if ($isImage): ?>
                            <a href="<?= $url ?>" target="_blank">
                                <img src="<?= $url ?>" class="img-fluid rounded border mb-2"
                                     style="height:170px;width:100%;object-fit:cover;">
                            </a>
                        <?php else: ?>
                            <div class="text-center border rounded py-4 mb-2 bg-light">
                                <i class="fa fa-file-pdf fa-3x text-danger"></i>
                            </div>
                        <?php endif; ?>
                        <div class="text-center">
                            <a href="<?= $url ?>" target="_blank" class="btn btn-sm btn-success">
                                <i class="fa fa-eye"></i> Lihat Berkas
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 border rounded bg-light">
                            <i class="fa fa-file-o fa-2x text-muted"></i>
                            <div class="small text-muted mt-2">Belum diupload</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>