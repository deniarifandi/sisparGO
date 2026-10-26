<?php
/**
 * Variables expected:
 * $uploadFields  — ['fieldname' => 'Label', ...]
 * $existingData  — array of current record (titpar/anggota/register)
 * $isEdit        — bool
 */
helper('upload');
?>

<div class="card border border-light-subtle shadow-sm mb-4 rounded-3">
    <div class="card-header bg-light fw-semibold text-secondary d-flex justify-content-between align-items-center py-3">
        <span class="text-dark"><?= $uploadSectionTitle ?? 'Dokumen Pendukung' ?></span>
        <span class="badge bg-secondary-subtle text-secondary small fw-normal">
            Format: PDF/Gambar
        </span>
    </div>
    <div class="card-body p-3">
        <div class="row g-3">
            <?php foreach ($uploadFields as $field => $label):
                $hasFile = ($isEdit && !empty($existingData[$field]));
            ?>
            <div class="col-md-6">
                <div class="p-3 border rounded-3 h-100 <?= $hasFile ? 'border-success-subtle bg-success-subtle bg-opacity-10' : 'border-light-subtle' ?>">

                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <label class="form-label fw-semibold text-dark mb-0"><?= $label ?></label>
                        <?php if ($hasFile): ?>
                            <span class="badge bg-success text-white px-2 py-1 rounded-pill" style="font-size:10px;">
                                ✓ Sudah Ada
                            </span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark px-2 py-1 rounded-pill" style="font-size:10px;">
                                ⚠ Belum Diunggah
                            </span>
                        <?php endif; ?>
                    </div>

                    <?= showFileLink($existingData[$field] ?? null, $field) ?>

                    <div class="input-group input-group-sm">
                        <input type="file" name="<?= $field ?>"
                               class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                    <div class="form-text text-muted" style="font-size:11px;">
                        <?= $hasFile
                            ? 'Pilih berkas baru jika ingin mengubah.'
                            : 'Pilih berkas untuk mengunggah.' ?>
                    </div>

                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>