<div class="modal fade" id="universalUploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="universalUploadForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalTitle">Upload File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="upload_record_id">
                    <div class="mb-3">
                        <label class="form-label" id="uploadLabelText">Pilih File</label>
                        <input type="file" class="form-control" required>
                        <small class="text-danger" id="uploadError"></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100" id="btnSubmitUpload">
                        Mulai Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>