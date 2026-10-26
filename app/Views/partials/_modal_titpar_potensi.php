<div class="modal fade" id="modalPotensi" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="<?= base_url('admin/titpar/potensi/' . $titpar['id']) ?>">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Potensi Kendaraan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php
                    $hari = [
                        'senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu',
                        'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu', 'minggu' => 'Minggu',
                    ];
                    ?>
                    <?php foreach ($hari as $key => $label): ?>
                    <div class="mb-3">
                        <label class="form-label"><?= $label ?></label>
                        <input type="number" min="0" class="form-control"
                               name="potensi_<?= $key ?>"
                               value="<?= esc($titpar['potensi_' . $key]) ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>