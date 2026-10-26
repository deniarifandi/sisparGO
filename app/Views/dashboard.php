<?php echo view('header'); ?>
<div class="dashboard-home">


  

<?php if ( session('role') < 4): ?>
  <div class="card shadow-sm border-0 mb-4 smart-banner">
    <div class="card-body d-flex align-items-center justify-content-between py-2 px-3">
      <div class="d-flex align-items-center">
        <div class="icon-box me-2">
          <i class="bi bi-speedometer2 text-primary fs-5"></i>
        </div>
        <div>
          <small class="fw-bold d-block">Pusat informasi parkir</small>
          <small class="text-muted">Pantau & kelola data parkir</small>
        </div>
      </div>
      <a href="commandcenter" aria-label="Buka pusat informasi parkir" class="btn btn-primary btn-sm btn-smart rounded-pill px-3">
        <i class="bi bi-arrow-right-circle"></i>
      </a>
    </div>
  </div>

<?php endif ?>

  
  <div class="row g-3 mb-4">

    <div class="col-sm-6 col-lg-3">
      <div class="card smart-card text-center p-3 shadow-sm border-0 animate-card">
        <i class="bi bi-qr-code-scan smart-icon mb-2 text-success fs-2"></i>
        <h6 class="fw-bold mb-1">QRIS</h6>
        <p class="text-muted small mb-3">Kelola QRIS</p>
        <a href="va-owner" class="btn btn-smart btn-sm w-100">Lihat Akun</a>
      </div>
    </div>

<?php if ( session('role') < 4): ?>
    <div class="col-sm-6 col-lg-3">
      <div class="card smart-card text-center p-3 shadow-sm border-0 animate-card">
        <i class="bi bi-graph-up smart-icon mb-2 text-warning fs-2"></i>
        <h6 class="fw-bold mb-1">Potensi</h6>
        <p class="text-muted small mb-3">Kelola Potensi Parkir</p>
        <a href="potensi" class="btn btn-smart btn-sm w-100">Lihat Potensi</a>
      </div>
    </div>

  <?php endif ?>
    <div class="col-sm-6 col-lg-3">
      <div class="card smart-card text-center p-3 shadow-sm border-0 animate-card">
        <i class="bi bi-folder-fill smart-icon mb-2 text-primary fs-2"></i>
        <h6 class="fw-bold mb-1">Data Transaksi</h6>
        <p class="text-muted small mb-3">Manajemen Data Transaksi Parkir</p>
        <a href="transaksi" class="btn btn-smart btn-sm w-100">Lihat Data</a>
      </div>
    </div>

<?php if ( session('role') < 4): ?>
   <div class="col-sm-6 col-lg-3">
  <div class="card smart-card text-center p-3 shadow-sm border-0 animate-card">
    <i class="bi bi-headset smart-icon mb-2 text-info fs-2"></i>
    <h6 class="fw-bold mb-1">Layanan Chat</h6>
    <p class="text-muted small mb-3">
      Media komunikasi jukir dengan petugas
    </p>
    <a href="chat-room" class="btn btn-smart btn-sm w-100">Akses Layanan</a>
  </div>
</div>

<?php endif ?>


  </div>

  <hr class="my-4">

  
  <div class="row g-3">

<?php if ( session('role') < 4): ?>
  <div class="col-sm-6 col-lg-3">
    <div class="card smart-card text-center p-3 shadow-sm border-0 animate-card">
      <i class="bi bi-people-fill smart-icon mb-2 text-danger fs-4"></i>
      <h6 class="fw-bold mb-1">Anggota</h6>
      <p class="text-muted small mb-2">Data & perubahan anggota</p>
      <div class="row g-2">
        <div class="col-6"><a href="anggota" class="btn btn-primary btn-sm btn-smart w-100"><i class="bi bi-people me-1"></i> Anggota</a></div>
        <div class="col-6"><a href="admin/anggota-perubahan" class="btn btn-warning btn-sm w-100 text-dark"><i class="bi bi-person-gear me-1"></i> Perubahan</a></div>
      </div>
      <a href="<?= base_url('admin/pendaftaran-anggota') ?>" class="btn btn-outline-success btn-sm w-100 mt-2"><i class="bi bi-person-check me-1"></i> Cek Pengajuan Pendaftaran</a>
    </div>
  </div>

  <div class="col-sm-6 col-lg-3">
    <div class="card smart-card text-center p-3 shadow-sm border-0 animate-card">
      <i class="bi bi-pin-map-fill smart-icon mb-2 text-primary fs-4"></i>
      <h6 class="fw-bold mb-1">Titik Parkir</h6>
      <p class="text-muted small mb-2">Kelola Titik Parkir</p>
      <a href="titpar" class="btn btn-primary btn-smart btn-sm w-100">
        <i class="bi bi-box-arrow-up-right me-1"></i> Kelola
      </a>
      <a href="<?= base_url('admin/kta-perpanjangan') ?>" class="btn btn-outline-success btn-sm w-100 mt-2"><i class="bi bi-arrow-repeat me-1"></i> Perpanjangan KTA</a>
    </div>
  </div>


<?php endif ?>

    <div class="col-sm-6 col-lg-3">
      <div class="card smart-card text-center p-3 shadow-sm border-0 animate-card">
        <i class="bi bi-cash-stack smart-icon mb-2 text-danger fs-4"></i>
        <h6 class="fw-bold mb-1">Potensi X Realisasi</h6>
        <p class="text-muted small mb-2">Data Tagihan</p>
         
          <div class="row">
            <div class="col-md-12">
             <a href="potensi/realisasi" class="btn btn-smart btn-sm w-100">
              <i class="bi bi-box-arrow-up-right me-1"></i> Kelola
             </a>
            </div>
          </div>

      </div>
    </div>
<div class="col-sm-6 col-lg-3">
  <div class="card smart-card text-center p-2 shadow-sm border-0 animate-card bg-light">
    <i class="bi bi-exclamation-triangle-fill smart-icon mb-2 text-danger fs-4"></i>
    <h6 class="fw-bold mb-1">Laporan Kendala</h6>
    <p class="text-muted small mb-2">Laporan kendala dari jukir</p>
    <a href="<?= base_url('kendalaindex') ?>" class="btn btn-danger btn-smart btn-sm w-100">
      <i class="bi bi-box-arrow-up-right me-1"></i> Lihat Kendala
    </a>
  </div>
</div>


<?php if ( session('role') < 4): ?>
  <div class="col-sm-6 col-lg-3">
    <div class="card smart-card text-center p-2 shadow-sm border-0 animate-card">
      <i class="bi bi-ticket-perforated smart-icon mb-2 text-primary fs-4"></i>
      <h6 class="fw-bold mb-1">Karcis</h6>
      <p class="text-muted small mb-2">Kelola Karcis</p>
      <a href="tiket" class="btn btn-primary btn-smart btn-sm w-100">
        <i class="bi bi-box-arrow-up-right me-1"></i> Kelola
      </a>
    </div>
  </div>

  <div class="col-sm-6 col-lg-3">
  <div class="card smart-card text-center p-2 shadow-sm border-0 animate-card bg-light">
    <i class="bi bi-clock-history smart-icon mb-2 text-info fs-4"></i>
    <h6 class="fw-bold mb-1">Riwayat Karcis</h6>
    <p class="text-muted small mb-2">Riwayat Karcis</p>
    <a href="tiket/histori" class="btn btn-primary btn-smart btn-sm w-100">
        <i class="bi bi-box-arrow-up-right me-1"></i> Kelola
      </a>
  </div>
</div>

<?php endif ?>

<?php if ( session('role') < 4): ?>
  
<div class="col-sm-6 col-lg-3">
  <div class="card smart-card text-center p-2 shadow-sm border-0 animate-card bg-light">
    <i class="bi bi-people-fill smart-icon mb-2 text-success fs-4"></i>
    <h6 class="fw-bold mb-1">Manajemen Pengguna</h6>
    <p class="text-muted small mb-2">Kelola akun pengguna</p>
    <div class="row g-2">
      <div class="col-6"><a href="<?= base_url('user/list') ?>" class="btn btn-primary btn-smart btn-sm w-100"><i class="bi bi-people me-1"></i>Pengguna</a></div>
      <div class="col-6"><a href="<?= base_url('legal-documents') ?>" class="btn btn-warning btn-sm w-100 text-dark"><i class="bi bi-shield-check me-1"></i>Aplikasi</a></div>
      <?php if (in_array(session('role'), [0, '0'], true)): ?>
      <div class="col-12"><a href="<?= base_url('user/login-attempts') ?>" class="btn btn-outline-danger btn-sm w-100"><i class="bi bi-shield-lock me-1"></i>Login Attempts</a></div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif ?>



  </div>

  

  <hr class="my-4">

  
  
   <div class="row g-3 mb-4">

    
  </div>

  <?php if ((int) session('role') === 0): ?>
    <div class="card shadow-sm border-0 mb-4">
      <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
          <h6 class="fw-bold mb-1"><i class="bi bi-cloud-download me-2 text-primary"></i>Sinkronisasi Source Code</h6>
          <p class="text-muted small mb-0">Ambil perubahan terbaru dari repository Git.</p>
        </div>
        <a href="<?= base_url('maintenance/git-pull') ?>" class="btn btn-primary btn-sm">
          <i class="bi bi-cloud-download me-1"></i> Git Pull
        </a>
      </div>
    </div>
  <?php endif; ?>


</div>
<?php echo view('footer'); ?>

