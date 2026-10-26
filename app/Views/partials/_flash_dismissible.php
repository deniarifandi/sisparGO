<?php foreach (['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $key => $color): ?>
    <?php if (session()->getFlashdata($key)): ?>
    <div class="col-12">
        <div class="alert alert-<?= $color ?> alert-dismissible fade show mb-0">
            <?= session()->getFlashdata($key) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>
<?php endforeach; ?>