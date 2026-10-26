<!-- Edit Register Modal -->
<div class="modal fade" id="modalEditRegister" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="<?= base_url('admin/register/update-informasi') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $register['id'] ?>">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa fa-user-edit me-2"></i>Edit Informasi Umum
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-12 col-md-6">
                            <label class="form-label">Nama</label>
                            <input type="text" class="form-control" name="anggota_nama"
                                   value="<?= esc($register['anggota_nama']) ?>">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">No. KTP</label>
                            <input type="text" class="form-control"
                                   value="<?= esc($register['anggota_noktp']) ?>" readonly>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" class="form-control" name="anggota_tgllhr"
                                   value="<?= $register['anggota_tgllhr'] ?? '' ?>">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">No. HP</label>
                            <input type="text" class="form-control" name="hp"
                                   value="<?= esc($register['hp'] ?? '') ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Alamat</label>
                            <textarea class="form-control" name="anggota_alamat"
                                      rows="3"><?= esc($register['anggota_alamat'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">ID Anggota</label>
                            <div class="input-group">
                                <input type="text" class="form-control"
                                       value="<?= esc($register['register_anggotaid'] ?? '') ?>" readonly>
                                <?php if (empty($register['register_anggotaid'])): ?>
                                    <?php if ((int)$register['anggota_status'] === 1): ?>
                                        <button type="submit"
                                                form="formRegisterAnggota"
                                                class="btn btn-success"
                                                onclick="return confirm('Register ID Anggota?')">
                                            Register
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-secondary" disabled>Register</button>
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
                            <input type="date" class="form-control" name="masa_berlaku"
                                   value="<?= $register['masa_berlaku'] ?? '' ?>">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="anggota_status">
                                <option value="0" <?= $register['anggota_status']==0 ? 'selected':'' ?>>Pending</option>
                                <option value="1" <?= $register['anggota_status']==1 ? 'selected':'' ?>>Layak</option>
                                <option value="2" <?= $register['anggota_status']==2 ? 'selected':'' ?>>Tidak Layak</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Catatan</label>
                            <textarea class="form-control" name="catatan"
                                      rows="3"><?= esc($register['catatan'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Dibuat</label>
                            <input type="text" class="form-control"
                                   value="<?= date('d M Y H:i', strtotime($register['created_at'])) ?>"
                                   readonly>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Diperbarui</label>
                            <input type="text" class="form-control"
                                   value="<?= date('d M Y H:i', strtotime($register['updated_at'] ?? '')) ?>"
                                   readonly>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Hidden form for Register Anggota -->
<form id="formRegisterAnggota"
      method="post"
      action="<?= base_url('admin/register/approve') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= $register['id'] ?>">
</form>