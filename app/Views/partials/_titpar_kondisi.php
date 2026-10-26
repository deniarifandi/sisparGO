<div class="card border shadow-none mb-3">
    <div class="card-body">
        <div class="card">
            <div class="card-header">Kondisi Lapangan</div>
            <div class="card-body">
                <form method="post" action="<?= base_url('admin/titpar/kondisi/' . $titpar['id']) ?>">
                    <?= csrf_field() ?>

                    <?php
                    $kondisi = [
                        'melintang' => 'Melintang',
                        'macet'     => 'Menyebabkan Kemacetan',
                        'sosial'    => 'Dampak Sosial',
                    ];
                    ?>

                    <?php foreach ($kondisi as $key => $label): ?>
                    <div class="mb-4">
                        <label class="form-label fw-semibold"><?= $label ?></label>
                        <div class="form-check form-switch">
                            <input class="form-check-input kondisi-toggle" type="checkbox"
                                   id="<?= $key ?>" name="<?= $key ?>" value="1"
                                   data-target="#<?= $key ?>_alasan_wrap"
                                   <?= !empty($titpar[$key]) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="<?= $key ?>">Tidak / Ya</label>
                        </div>
                        <div id="<?= $key ?>_alasan_wrap"
                             class="<?= empty($titpar[$key]) ? 'd-none' : '' ?> mt-2">
                            <textarea class="form-control" name="<?= $key ?>_alasan" rows="2"
                                      placeholder="Alasan"><?= esc($titpar[$key . '_alasan'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <?php endforeach; ?>

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