<?php if (!empty($titpar['latitude']) && !empty($titpar['longitude'])): ?>
<div class="card border shadow-none mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fa fa-map-marker text-primary me-2"></i>Lokasi Peta</h6>
        <button type="button" class="btn btn-sm btn-primary"
                data-bs-toggle="modal" data-bs-target="#modalEdit">
            <i class="fa fa-pencil"></i> Edit Lokasi
        </button>
    </div>
    <div class="card-body p-0">
        <iframe src="https://maps.google.com/maps?q=<?= $titpar['latitude'] ?>,<?= $titpar['longitude'] ?>&z=17&output=embed"
                width="100%" height="240" style="border:0; display:block;"></iframe>
        <div class="p-3">
            <small class="text-muted d-block mb-2">
                <?= $titpar['latitude'] ?>, <?= $titpar['longitude'] ?>
            </small>
            <a href="https://maps.google.com/?q=<?= $titpar['latitude'] ?>,<?= $titpar['longitude'] ?>"
               target="_blank" class="btn btn-sm btn-outline-primary btn-auto-sm">
                <i class="fa fa-external-link"></i> Buka Google Maps
            </a>
        </div>
    </div>
</div>
<?php endif; ?>