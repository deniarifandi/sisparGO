<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <form method="post" action="<?= base_url('admin/titpar/update/' . $titpar['id']) ?>" class="w-100">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Titik Parkir</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nama Tempat</label>
                                <input type="text" name="titpar_namatempat" class="form-control"
                                       value="<?= esc($titpar['titpar_namatempat']) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Lokasi</label>
                                <textarea class="form-control" name="titpar_lokasi" rows="2"
                                ><?= esc($titpar['titpar_lokasi']) ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Batas Parkir</label>
                                <input class="form-control" name="batas_parkir"
                                       value="<?= esc($titpar['batas_parkir']) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Jam Operasional</label>
                                <input class="form-control" name="jam_operasional"
                                       value="<?= esc($titpar['jam_operasional']) ?>">
                            </div>

                            <?php $jenis = (int)($titpar['jenis_kendaraan'] ?? 0); ?>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Jenis Kendaraan</label>
                                <?php foreach (['motor' => 1, 'mobil' => 2, 'truk' => 4] as $val => $bit): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="jenis_kendaraan[]" id="<?= $val ?>" value="<?= $val ?>"
                                           <?= ($jenis & $bit) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="<?= $val ?>"><?= ucfirst($val) ?></label>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Status</label>
                                <select class="form-select" name="status">
                                    <option value="0" <?= (int)$titpar['status'] === 0 ? 'selected' : '' ?>>Pending</option>
                                    <option value="1" <?= (int)$titpar['status'] === 1 ? 'selected' : '' ?>>Layak</option>
                                    <option value="2" <?= (int)$titpar['status'] === 2 ? 'selected' : '' ?>>Tidak Layak</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Virtual Account No.</label>
                                <input class="form-control" name="va"
                                       value="<?= esc($titpar['va']) ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Catatan</label>
                                <textarea class="form-control" name="catatan"><?= esc($titpar['catatan'] ?? '') ?></textarea>
                            </div>

                            <input type="hidden" id="latitude" name="latitude" value="<?= $titpar['latitude'] ?>">
                            <input type="hidden" id="longitude" name="longitude" value="<?= $titpar['longitude'] ?>">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Pilih Lokasi di Peta</label>
                            <div id="map" class="w-100"
                                 style="min-height:280px; height:55vh; max-height:450px; border-radius:8px; border:1px solid #dee2e6;">
                            </div>
                            <div class="mt-2 small text-muted">
                                Latitude: <span id="latText" class="fw-medium"><?= $titpar['latitude'] ?></span><br>
                                Longitude: <span id="lngText" class="fw-medium"><?= $titpar['longitude'] ?></span>
                            </div>
                        </div>

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