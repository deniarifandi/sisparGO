<div class="modal fade" id="modal_catatan" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formCatatan">
                <div class="modal-header">
                    <h5 class="modal-title">Catatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="catatan_record_id">
                    <div class="mb-3">
                        <label class="form-label">Masukkan Catatan</label>
                        <textarea class="form-control" id="catatan_text"
                                  name="catatan" rows="4" required
                                  placeholder="Tulis catatan di sini..."></textarea>
                        <small class="text-danger" id="catatanError"></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100">
                        Simpan Catatan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>