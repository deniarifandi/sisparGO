<?php
$statusList = [
    '0' => ['label' => 'Verifikasi Berkas', 'class' => 'bg-warning text-dark'],
    '1' => ['label' => 'Disetujui',         'class' => 'bg-success text-white'],
    '2' => ['label' => 'Ditolak',           'class' => 'bg-danger text-white'],
];

$current = (string)($user['anggota_status'] ?? '0');
$display = $statusList[$current] ?? $statusList['0'];
?>

<div class="d-flex align-items-center gap-2 mb-3">
    <span class="sec-badge">SP</span>
    <span class="sec-title">Status Pendaftaran</span>
</div>

<div class="row g-3 mb-4">

    <!-- Status badge + ID Anggota -->
    <div class="col-md-5">
        <div class="border rounded-3 p-3 h-100 bg-white shadow-sm">
            <div class="text-uppercase text-muted fw-semibold mb-2"
                 style="font-size:11px;letter-spacing:.08em">
                Status Pendaftaran
            </div>
            <span class="badge <?= $display['class'] ?> rounded-pill px-3 py-2 mb-3">
                <?= esc($display['label']) ?>
            </span>
            <hr class="my-3">
            <div class="small text-muted mb-1">ID Anggota</div>
            <div class="fw-semibold fs-5">
                <?= !empty($user['register_anggotaid'])
                    ? esc($user['register_anggotaid'])
                    : '<span class="text-secondary fs-6">Belum diterbitkan</span>' ?>
            </div>
        </div>
    </div>

    <!-- Catatan admin -->
    <div class="col-md-7">
        <div class="border rounded-3 p-3 h-100 bg-white shadow-sm">
            <div class="d-flex align-items-center gap-2 mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                     fill="currentColor" class="text-warning" viewBox="0 0 16 16">
                    <path d="M8 1a7 7 0 1 0 0 14A7 7 0 0 0 8 1zm.93 10.588-2.29-2.29.708-.708 1.082 1.082 2.543-3.815.832.555-2.875 4.312z"/>
                </svg>
                <div class="text-uppercase text-muted fw-semibold"
                     style="font-size:11px;letter-spacing:.08em">
                    Catatan Admin
                </div>
            </div>
            <?php if (!empty($user['catatan'])): ?>
                <div class="small text-dark lh-lg">
                    <?= nl2br(esc(trim($user['catatan']))) ?>
                </div>
            <?php else: ?>
                <div class="small text-muted">Tidak ada catatan dari admin.</div>
            <?php endif; ?>
        </div>
    </div>

</div>