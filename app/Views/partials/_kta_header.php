<div class="kta-header">
    <div class="d-flex align-items-center gap-3 position-relative">
        <div class="hdr-shield">
            <svg width="27" height="27" viewBox="0 0 24 24" fill="none">
                <path d="M12 2L3 6v6c0 5.25 3.75 10.15 9 11.4C17.25 22.15 21 17.25 21 12V6L12 2z"
                      fill="rgba(255,255,255,.9)"/>
                <path d="M9 12l2 2 4-4" stroke="#0d47a1" stroke-width="2.2"
                      stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div>
            <h1 class="text-white fw-bold mb-0" style="font-size:15px;line-height:1.3">
                Dishub Kota Malang
            </h1>
            <div class="mt-1" style="font-size:11px;color:rgba(255,255,255,.55);letter-spacing:.04em">
                Dinas Perhubungan · Kota Malang
            </div>
        </div>
    </div>

    <div class="d-flex align-items-center justify-content-between mt-3">
        <div class="kta-pill">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 14H4V10h16v8zm0-10H4V6h16v2z"/>
            </svg>
            <?= $headerTitle ?? 'Pengurusan KTA Digital' ?>
        </div>

        <?php if ($showLogout ?? false): ?>
        <a href="<?= base_url('register/logout') ?>"
           class="kta-pill text-decoration-none bg-danger text-white"
           style="cursor:pointer;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2.5"
                 stroke-linecap="round" stroke-linejoin="round" class="me-1">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            Keluar
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- STEP INDICATOR -->
<div class="kta-steps">
    <?php
    $steps  = ['Isi Form', 'Verifikasi', 'KTA Terbit'];
    $active = $activeStep ?? 1;
    ?>
    <?php foreach ($steps as $i => $label): ?>
        <?php if ($i > 0): ?><div class="step-line"></div><?php endif; ?>
        <div class="step <?= ($i + 1) === $active ? 'is-active' : '' ?>">
            <div class="step-dot"><?= $i + 1 ?></div>
            <span class="step-lbl"><?= $label ?></span>
        </div>
    <?php endforeach; ?>
</div>