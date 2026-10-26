<div class="modal fade" id="modalEditAnggota" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="<?= base_url('admin/anggota/update/' . $anggota['id']) ?>">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Data Anggota</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama</label>
                        <input type="text" class="form-control" name="anggota_nama"
                               value="<?= esc($anggota['anggota_nama']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">No. KTP</label>
                        <input type="text" class="form-control bg-light text-muted"
                               value="<?= esc($anggota['anggota_noktp']) ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tanggal Lahir</label>
                        <input type="date" class="form-control" name="tgl_lahir"
                               value="<?= esc($anggota['tgl_lahir'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alamat</label>
                        <textarea class="form-control" name="alamat" rows="3"
                        ><?= esc($anggota['alamat'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select class="form-select" name="status">
                            <option value="0" <?= (int)$anggota['status'] === 0 ? 'selected' : '' ?>>Pending</option>
                            <option value="1" <?= (int)$anggota['status'] === 1 ? 'selected' : '' ?>>Layak</option>
                            <option value="2" <?= (int)$anggota['status'] === 2 ? 'selected' : '' ?>>Tidak Layak</option>
                        </select>
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