<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="base-url" content="<?= base_url() ?>">
  <title>SISPARGO - Kota Bogor</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <!-- Select2 Bootstrap 5 Theme -->
  <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.dataTables.min.css">
  <script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>
  <!-- Include JS -->
  <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
  <!-- Select2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/autonumeric@4.6.0/dist/autoNumeric.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
  
  <link rel="stylesheet" href="<?= base_url('css/sispargo.css') ?>">
</head>
<body>
<div id="overlaySpinner" class="d-none position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-dark bg-opacity-50" style="z-index:2000;">
  <div class="spinner-border text-light" role="status"><span class="visually-hidden">Memuat...</span></div>
</div>
<div class="content" id="content">
  <header class="app-header">
    <a href="<?= base_url() ?>" class="app-brand" aria-label="Dashboard SISPARGO">
      <span class="app-mark" aria-hidden="true">P</span>
      <span><strong>SISPARGO</strong><small>KOTA BOGOR</small></span>
    </a>
    <div class="header-actions">
      <a class="home-link" href="<?= base_url() ?>">Dashboard</a>
      <span class="user-name"><?= esc((string) session('nama')) ?></span>
      <form action="<?= base_url('logout') ?>" method="post">
        <?= csrf_field() ?>
        <button type="submit" class="logout-button">Keluar <span aria-hidden="true">&rarr;</span></button>
      </form>
    </div>
  </header>
  <main class="container-fluid app-main">