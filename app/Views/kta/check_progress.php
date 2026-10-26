<?php
/**
* Helper to display current file link inside the form
*/
$isEdit = !empty($user);

$uploadFields = [
    'anggota_foto'      => 'Swafoto',
    'kk'                => 'Foto KK',
    'foto_ktp'          => 'foto KTP',
];

function showFileLink($file, $folder) {
    if ($file) {
        return '
        <div class="d-flex align-items-center justify-content-between bg-light p-2 rounded-2 border border-success-subtle mb-2">
            <span class="text-success small fw-medium">
                 <small>
                <svg width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill me-1" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                </svg>
                Tersimpan
                </small>
            </span>
            <a href="'.base_url('uploads/'.$folder.'/'.$file).'" target="_blank" class="btn btn-sm btn-link text-decoration-none p-0 text-primary small text-small fw-semibold">
                <small>Lihat Berkas →</small>
            </a>
        </div>';
    }
    return '';
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   
    <title>Dishub Malang – <?= $isEdit ? 'Ubah' : 'Tambah' ?> KTA Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="<?php echo base_url() ?>css/pendaftaran.css">
</head>
<body>


    <?php
    $status_list = [
        '0' => ['label' => 'Verifikasi Berkas', 'class' => 'bg-warning text-dark'],
        '1' => ['label' => 'Disetujui', 'class' => 'bg-success text-white'],
        '2' => ['label' => 'Ditolak', 'class' => 'bg-danger text-white']
    ];

    $current_status = $user['anggota_status'] ?? '0';
    $display = $status_list[$current_status] ?? $status_list['0'];
    $isApproved = ($user['anggota_status'] ?? '0') == '1';
    ?>

    <div class="kta-card card">

<!-- ── HEADER ── -->
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
    <div class="kta-pill mt-3">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
            <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 14H4V10h16v8zm0-10H4V6h16v2z"/>
        </svg>
        KTA Digital
    </div>

    <a href="<?= base_url('pendaftaran') ?>" class="kta-pill mt-3 float-end text-decoration-none bg-danger text-white" style="cursor: pointer;">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="me-1">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16 17 21 12 16 7"></polyline>
            <line x1="21" y1="12" x2="9" y2="12"></line>
        </svg>
        Keluar / Logout
    </a>
</div>

<!-- ── PROCESS STEPS ── -->
<div class="kta-steps">
    <div class="step">
        <div class="step-dot">1</div>
        <span class="step-lbl">Isi Form</span>
    </div>
    <div class="step-line"></div>
    <div class="step is-active">
        <div class="step-dot">2</div>
        <span class="step-lbl">Verifikasi</span>
    </div>
    <div class="step-line"></div>
    <div class="step">
        <div class="step-dot">3</div>
        <span class="step-lbl">KTA Terbit</span>
    </div>
</div>

<!-- ── BODY ── -->
<div class="card-body p-4">

<!-- Bootstrap nav-pills tabs -->
<ul class="nav nav-pills kta-tabs mb-4" id="ktaTabs" role="tablist">
    <li class="nav-item flex-grow-1 text-center" role="presentation">
       <button class="nav-link active w-100"
        id="tab-daftar-btn"
        data-bs-toggle="pill"
        data-bs-target="#tab-koordinator"
        type="button" role="tab">
        Koordinator
    </button>
</li>
<li class="nav-item flex-grow-1 text-center" role="presentation">
    <button class="nav-link w-100"
    id="tab-anggota-btn"
    data-bs-toggle="pill"
    data-bs-target="#tab-anggota"
    type="button" role="tab">
    Anggota
</button>
</li>
<li class="nav-item flex-grow-1 text-center" role="presentation">
    <button class="nav-link w-100"
    id="tab-status-btn"
    data-bs-toggle="pill"
    data-bs-target="#tab-titpar"
    type="button" role="tab">
    Titik Parkir
</button>
</li>
</ul>

<!-- Flash: success -->
<?php if(session()->getFlashdata('success')): ?>
<div class="kta-alert kta-alert-success mb-3">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
    </svg>
    <span><?= session()->getFlashdata('success') ?></span>
</div>
<?php endif; ?>

<!-- Flash: error -->
<?php if(session()->getFlashdata('error') || session()->getFlashdata('errors')): ?>
<div class="kta-alert kta-alert-error mb-3">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
    </svg>
    <span>
        Terjadi kesalahan. Silakan cek form kembali.
        <?php if(session()->getFlashdata('errors')): ?>
        <ul><?php foreach(session()->getFlashdata('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach ?></ul>
    <?php endif; ?>
    <?php if(session()->getFlashdata('error')): ?>
    <ul><?php echo session()->getFlashdata('error') ?></ul>
    <?php endif; ?>
    </span>
</div>
<?php endif; ?>

<!-- Tab panes -->
<div class="tab-content" id="ktaTabContent">

<!-- ── PENDAFTARAN BARU ── -->
<div class="tab-pane fade show active" id="tab-koordinator" role="tabpanel">

    <div class="row g-3 mb-4">


        <div class="d-flex align-items-center gap-2">
            <span class="sec-badge">SP</span>
            <span class="sec-title">Status Pendaftaran</span>
        </div>
<!-- Status -->
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

    <div class="small text-muted mb-1">
        ID Anggota
    </div>

    <div class="fw-semibold fs-5">
        <?= !empty($user['register_anggotaid'])
        ? esc($user['register_anggotaid'])
        : '<span class="text-secondary fs-6">Belum diterbitkan</span>' ?>
    </div>

</div>
</div>

<!-- Catatan -->
<div class="col-md-7">
    <div class="border rounded-3 p-3 h-100 bg-white shadow-sm">

        <div class="d-flex align-items-center gap-2 mb-3">
            <svg xmlns="http://www.w3.org/2000/svg"
            width="18"
            height="18"
            fill="currentColor"
            class="text-warning"
            viewBox="0 0 16 16">
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

    <div class="small text-muted">
        Tidak ada catatan dari admin.
    </div>

<?php endif; ?>

</div>
</div>

</div>

<form id="" action="<?= base_url('register/update') ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= $user['id'] ?>">
    <input type="hidden" name="anggota_noktp" value="<?= $user['anggota_noktp'] ?>">

    <div class="d-flex align-items-center gap-2 mb-3">
        <span class="sec-badge">A</span>
        <span class="sec-title">Data Koordinator</span>
    </div>

    <fieldset <?= $isApproved ? 'disabled' : '' ?>>

        <div class="row g-3">
            <div class="col-12 col-sm-6">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" name="anggota_nama" value="<?= $user['anggota_nama'] ?>" required>
            </div>
            <div class="col-12 col-sm-6">
                <label class="form-label fw-semibold ">NIK (Tidak dapat diubah)</label>
                <input type="text" class="form-control bg-light text-muted" value="<?= $user['anggota_noktp'] ?>" disabled>
            </div>
        </div>

        <div class="mt-3">
            <label class="form-label fw-semibold ">Nomor HP / WhatsApp</label>
            <input type="text" class="form-control" name="hp" value="<?= $user['hp'] ?>" placeholder="Contoh: 08123456789" required>
        </div>

        <div class="mt-3">
            <label class="form-label fw-semibold ">Alamat Lengkap</label>
            <textarea class="form-control" name="anggota_alamat" rows="3" required><?= $user['anggota_alamat'] ?></textarea>
        </div>

        <div class="mt-3">
            <label class="form-label fw-semibold ">Tanggal Lahir</label>
            <input type="date" class="form-control" name="anggota_tgllhr" value="<?= $user['anggota_tgllhr'] ?>" required>
        </div>

        <h3 class="h5 text-primary mt-5 pt-3 border-top mb-3">Unggah Berkas (PDF/JPG)</h3>

      <div class="card-body p-3">
                    <div class="row g-3">
                        <?php foreach($uploadFields as $field => $label): 
                            $hasFile = ($isEdit && !empty($user[$field]));
                        ?>
                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 h-100 <?= $hasFile ? 'border-success-subtle bg-success-subtle bg-opacity-10' : 'border-light-subtle' ?>">
                                    
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="form-label fw-semibold text-dark mb-0">
                                            <?= $label ?>
                                        </label>
                                        <?php if($hasFile): ?>
                                            <span class="badge bg-success text-white px-2 py-1 rounded-pill" style="font-size: 10px;">
                                                ✓ Sudah Ada
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark px-2 py-1 rounded-pill" style="font-size: 10px;">
                                                ⚠ Belum Diunggah
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if($hasFile): ?>
                                        <?= showFileLink($user[$field], $field) ?>
                                    <?php endif; ?>

                                    <div class="input-group input-group-sm">
                                        <input type="file" name="<?= $field ?>" class="form-control auto-upload" accept=".pdf,.jpg,.jpeg,.png">
                                    </div>
                                    <div class="form-text text-muted style-opacity" style="font-size: 11px;">
                                        <?= $hasFile ? 'Pilih berkas baru jika ingin mengubah berkas saat ini.' : 'Pilih berkas untuk mengunggah.' ?>
                                    </div>

                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

    </fieldset>

    <?php if (!$isApproved): ?>
        <button type="submit" class="btn btn-success w-100 fw-bold py-3 fs-6 mt-4 shadow-sm">
            Simpan & Perbarui Data
        </button>
    <?php endif; ?>
</form>
</div><!-- /tab-daftar -->

<!-- ── CEK STATUS ── -->
<div class="tab-pane fade" id="tab-titpar" role="tabpanel">

    <div class="status-note d-flex align-items-start gap-2 mb-3">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="#1a4699"
        class="flex-shrink-0 mt-1">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
        </svg>
        <span>Daftarkan titik parkir</span>
    </div>

<?php if (!empty($titpar)): ?>
   <div class="list-group mb-3">
    <?php foreach ($titpar as $row): ?>
        <div class="list-group-item d-flex justify-content-between align-items-center">

            <div>
                <small>
                    <div class="fw-semibold">
                        <?= esc($row['titpar_namatempat']) ?>
                    </div>
                </small>

                <small class="text-muted">
                    <?= esc(mb_strimwidth($row['titpar_lokasi'], 0, 25, '...')) ?>
                </small>
            </div>

            <div class="d-flex gap-2">
              
                <a href="<?= base_url('register/titpar/edit/'.$row['id']) ?>"
                   class="btn btn-sm btn-outline-primary">
                    Edit
                </a>

                <a href="<?= base_url('register/titpar/delete/'.$row['id']) ?>"
                   class="btn btn-sm btn-outline-danger"
                   onclick="return confirm('Hapus titik parkir ini?')">
                    Hapus
                </a>


            </div>

        </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
    <div class="alert alert-light border">
        Belum ada titik parkir yang terdaftar.
    </div>
<?php endif; ?>

<a href="<?= base_url('register/titpar/add?nik=' . urlencode($user['anggota_noktp'])) ?>"
    class="btn btn-primary w-100">
    + Tambah Titik Parkir
</a>

</div>

<div class="tab-pane fade" id="tab-anggota" role="tabpanel">

    <div class="status-note d-flex align-items-start gap-2 mb-3">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="#1a4699"
        class="flex-shrink-0 mt-1">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
        </svg>
        <span>Daftar Anggota Juru Parkir</span>
    </div>

<?php if (!empty($anggota)): ?>
    <div class="list-group mb-3">
        <?php foreach ($anggota as $row): ?>
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-semibold"><?= esc($row['anggota_nama']) ?></div>
                    <small class="text-muted">
                        <?= esc($row['anggota_noktp']) ?>
                    </small>
                </div>

                <span class="badge bg-success">
                    Terdaftar
                </span>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="alert alert-light border">
        Belum ada Anggota yang terdaftar.
    </div>
<?php endif; ?>

<a href="<?= base_url('register/anggota/add?nik=').$_GET['nik'] ?>"
    class="btn btn-primary w-100">
    + Tambah Anggota Parkir
</a>

</div>

</div><!-- /tab-content -->
</div><!-- /card-body -->
</div><!-- /kta-card -->

<div class="modal fade" id="modalTitpar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <form action="<?= base_url('register/titpar/save') ?>" method="post">

                <?= csrf_field() ?>

                <input
                    type="hidden"
                    name="anggota_noktp"
                    value="<?= esc($user['anggota_noktp']) ?>">

                <div class="modal-header">
                    <h5 class="modal-title">
                        Tambah Titik Parkir
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">
                            Nama Titik Parkir
                        </label>

                        <input
                            type="text"
                            name="titpar_namatempat"
                            class="form-control"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Alamat
                        </label>

                        <textarea
                            name="titpar_lokasi"
                            rows="3"
                            class="form-control"
                            required></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit"
                            class="btn btn-success">
                        Simpan
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// NIK: numbers-only + live counter
    function onNik(el) {
        el.value = el.value.replace(/\D/g, '');
        const n   = el.value.length;
        const ctr = document.getElementById('nikCtr');
        ctr.textContent = n + '/16';
        ctr.classList.toggle('ok', n === 16);
    }

// File upload zone feedback
    function onFile(zoneId, nameId, input) {
        if (input.files && input.files[0]) {
            const name = input.files[0].name;
            document.getElementById(nameId).textContent =
            name.length > 20 ? name.slice(0, 17) + '…' : name;
            document.getElementById(zoneId).classList.add('filled');
        }
    }

// Re-apply NIK counter on page reload (flash session)
    window.addEventListener('DOMContentLoaded', () => {
        const nik = document.getElementById('nikInput');
        if (nik && nik.value.length > 0) onNik(nik);
    });

</script>




<script>
   document.addEventListener("DOMContentLoaded", function () {

    const activeTab = localStorage.getItem("activeTab");

    if (activeTab) {
        const trigger = document.querySelector(
            `[data-bs-target="${activeTab}"]`
        );

        if (trigger) {
            bootstrap.Tab.getOrCreateInstance(trigger).show();
        } else {
            bootstrap.Tab.getOrCreateInstance(
                document.querySelector('[data-bs-target="#tab-koordinator"]')
            ).show();
        }
    }

    document.querySelectorAll('[data-bs-toggle="pill"]').forEach(tab => {
        tab.addEventListener("shown.bs.tab", function(e) {
            localStorage.setItem(
                "activeTab",
                e.target.dataset.bsTarget
            );
        });
    });

});
</script>
</body>
</html>