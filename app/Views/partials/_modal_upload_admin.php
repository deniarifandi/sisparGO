<div class="modal fade" id="uploadAdminModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="uploadAdminForm" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 id="uploadAdminTitle"></h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="uploadAdminId">
                    <input type="file" class="form-control" id="uploadAdminFile" required>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary w-100">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>