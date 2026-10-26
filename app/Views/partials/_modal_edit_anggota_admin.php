<div class="modal fade" id="modalEditAnggota" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="post" action="<?= base_url('admin/anggota/update/' . $anggota['id']) ?>" class="modal-content">

            <?= csrf_field() ?>

            <div class="modal-header">
                <h5 class="modal-title">Edit Data Anggota</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Nama Anggota</label>
                        <input
                            type="text"
                            name="anggota_nama"
                            class="form-control"
                            value="<?= esc($anggota['anggota_nama']) ?>"
                            required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">No. KTP</label>
                        <input
                            type="text"
                            name="anggota_noktp"
                            class="form-control"
                            maxlength="16"
                            value="<?= esc($anggota['anggota_noktp']) ?>"
                            required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tanggal Lahir</label>
                        <input
                            type="date"
                            name="tgl_lahir"
                            class="form-control"
                            value="<?= esc($anggota['tgl_lahir']) ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="0" <?= ($anggota['status'] == 0) ? 'selected' : '' ?>>
                                Pending
                            </option>
                            <option value="1" <?= ($anggota['status'] == 1) ? 'selected' : '' ?>>
                                Disetujui
                            </option>
                            <option value="2" <?= ($anggota['status'] == 2) ? 'selected' : '' ?>>
                                Ditolak
                            </option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Alamat</label>
                        <textarea
                            name="alamat"
                            class="form-control"
                            rows="4"
                            required><?= esc($anggota['alamat']) ?></textarea>
                    </div>

                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    Batal
                </button>

                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save me-1"></i>
                    Simpan
                </button>
            </div>

        </form>
    </div>
</div>