<?php if(session()->getFlashdata('success')): ?>
<div class="kta-alert kta-alert-success mb-3">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
    </svg>
    <span><?= session()->getFlashdata('success') ?></span>
</div>
<?php endif; ?>

<?php if(session()->getFlashdata('error') || session()->getFlashdata('errors')): ?>
<div class="kta-alert kta-alert-error mb-3">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
    </svg>
    <span>
        <?php if(session()->getFlashdata('errors')): ?>
            Terjadi kesalahan:
            <ul>
                <?php foreach(session()->getFlashdata('errors') as $e): ?>
                    <li><?= esc($e) ?></li>
                <?php endforeach ?>
            </ul>
        <?php endif; ?>
        <?php if(session()->getFlashdata('error')): ?>
            <p><?= esc(session()->getFlashdata('error')) ?></p>
        <?php endif; ?>
    </span>
</div>
<?php endif; ?>