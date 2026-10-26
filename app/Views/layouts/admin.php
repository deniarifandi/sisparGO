<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="base-url" content="<?= base_url() ?>">
    <title><?= $title ?? 'Sisparma - Dinas Perhubungan Kota Malang' ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= base_url('sisparma_icon.ico') ?>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS Framework & Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <!-- Third-Party Vendor CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">

    <!-- Custom Admin Theme CSS -->
    <link rel="stylesheet" href="<?= base_url('css/admin.css') ?>">

    <!-- jQuery (Diperlukan di head jika view child langsung mengeksekusi script document.ready) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <?= $this->renderSection('head') ?>

    <style>
    body {
      display: flex;
      font-family: 'Poppins', Helvetica, sans-serif;
    }
    .sidebar {
      width: 265px;
      min-height: 100vh;
      background: white;
      transition: all 0.3s;
      color: #fff;
      display: flex;
      flex-direction: column;
      z-index: 1050; /* stay above content */
    }
    .sidebar.collapsed {
      width: 70px;
    }
    /* Brand area */
    .brand {
      height: 65px;
      background: white;
      display: flex;
      align-items: center;
      justify-content: space-between;
      
      transition: all 0.3s;
    }
    .brand img {
      max-height: 100%;
      max-width: 100%;
    }
    /* Hide logo when collapsed */
    .sidebar.collapsed .brand .imglogo {
      display: none;
    }
    /* Center toggle when collapsed */
    .sidebar.collapsed .brand {
      justify-content: center;
    }
    .sidebar .nav-link {
      color: white;
      white-space: nowrap;
    }
    .sidebar.collapsed .nav-link span {
      display: none;
    }
    .sidebar .nav-link i {
      margin-right: 10px;
    }
    .sidebar.collapsed .nav-link i {
      margin-right: 0;
      text-align: center;
      width: 100%;
    }
    .content {
      flex-grow: 1;
    }

    header {
      background: #212529;
      color: #fff;
      height: 4rem;
      padding: 10px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
  </style>  

  <style type="text/css">
   /* === Drawer behavior on small screens === */
   @media (max-width: 768px) {
    .sidebar {
      position: fixed;
      top: 0;
      left: -220px; /* hidden off screen */
      height: 100%;
      width: 220px;
      box-shadow: 2px 0 5px rgba(0,0,0,0.5);
    }
    .sidebar.show {
      left: 0; /* slide in */
    }
    .sidebar.collapsed {
      width: 220px; /* drawer always full width */
    }

    /* When drawer open, show overlaySidebar */
    .overlaySidebar {
      display: block;
    }
  }
</style>

<style type="text/css">
  .form-control[readonly] {
    background-color: #d8d7d2 !important;  /* white background */
    opacity: 1;                         /* reset Bootstrap dim */
  }

</style>

<style type="text/css">
 @media (min-width: 769px) {
  .sidebar {
    flex-shrink: 0;
    width: 265px;
    min-width: 265px;
    max-width: 265px;
  }

  .sidebar.collapsed {
    width: 70px;
    min-width: 70px;
    max-width: 70px;
  }

  .content {
    flex-grow: 1;
    overflow-x: auto;
  }
}
</style>
</head>
<body>

<!-- Loading Spinner Overlay -->
<div id="overlaySpinner" 
     class="d-none position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-dark bg-opacity-50"
     style="z-index: 2000; pointer-events: all;">
  <div class="spinner-border text-light" style="width: 3rem; height: 3rem;" role="status">
    <span class="visually-hidden">Loading...</span>
  </div>
</div>

<!-- Sidebar Layout -->
<aside id="sidebar" class="sidebar d-flex flex-column">
    <div class="brand" style="display:flex; align-items:center; justify-content:center;">
    <img class="imglogo" src="<?= base_url() ?>images/logo.png" alt="Logo">
    <button class="btn btn-sm d-none d-md-block" id="toggleBtn">
      <img src="<?= base_url() ?>images/arrow_icon.png">
    </button>
  </div>

    <ul class="nav nav-pills flex-column p-2 gap-1">
        <?php
        $navItems = [
            ['icon' => 'bi-house',             'label' => 'Dashboard',       'url' => base_url('dashboard')],
            ['icon' => 'bi-geo-alt-fill',      'label' => 'Parkir',          'url' => base_url('parkir')],
            ['icon' => 'bi-envelope',          'label' => 'Setoran',         'url' => base_url('setoran')],
            ['icon' => 'bi-file-earmark-text', 'label' => 'Master Data',     'url' => base_url('master')],
            ['icon' => 'bi-graph-up-arrow',    'label' => 'Laporan',         'url' => base_url('laporan')],
            ['icon' => 'bi-gear',              'label' => 'Manajemen',       'url' => base_url('manajemen')],
            ['icon' => 'bi-book',              'label' => 'Buku Manual',     'url' => base_url('manual')],
            ['icon' => 'bi-people-fill',       'label' => 'Virtual Account', 'url' => base_url('')],
            ['icon' => 'bi-file-bar-graph',    'label' => 'Versi 3',         'url' => 'https://sisparma.malangkota.go.id/v3'], 
        ];


        $currentUrl = current_url();
        ?>
        <?php foreach ($navItems as $item): 
            // Cek status aktif yang lebih fleksibel
            $isActive = (rtrim($currentUrl, '/') === rtrim($item['url'], '/'));
        ?>
        <li class="nav-item">
            <a href="<?= $item['url'] ?>" class="nav-link d-flex align-items-center <?= $isActive ? 'active' : '' ?>">
                <i class="bi <?= $item['icon'] ?> nav-icon text-danger"></i>
                <span class="nav-label text-dark ms-3"><?= $item['label'] ?></span>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
</aside>

<!-- Main Wrapper -->
<div class="content d-flex flex-column min-vh-100" id="content" style="background-color: #EEF0F8;">

    <!-- Top Header Navigation -->
    <header class="navbar navbar-expand bg-white px-4 py-2 justify-content-between shadow-sm">
        <div class="d-flex align-items-center gap-2 text-primary">
            <i class="fas fa-home"></i> 
            <strong class="d-none d-sm-inline">SISPARMA</strong>
        </div>
        
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted d-none d-sm-inline" style="font-size: 13px;">
                Selamat datang, <strong><?= esc(session('nama')) ?></strong>
            </span>
            
            <form action="<?= base_url('logout') ?>" method="post" class="d-inline m-0">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-danger btn-sm d-flex align-items-center gap-1">
                    <i class="bi bi-box-arrow-right"></i> <span class="d-none d-md-inline">Logout</span>
                </button>
            </form>

            <button class="btn btn-light btn-sm d-block d-md-none border" id="toggleBtnMobile" aria-label="Toggle Menu Mobile">
                <i class="bi bi-list fs-5"></i>
            </button>
        </div>
    </header>

    <!-- Overlay Backdrop for Mobile Sidebar -->
    <div id="overlaySidebar" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" style="display: none; z-index: 1040;"></div>

    <!-- Main Dynamic Content Context -->
    <main class="container-fluid my-4 flex-grow-1 px-4">
        <?= $this->renderSection('content') ?>
    </main>

</div>

<!-- Core JavaScript Libraries -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Vendor Scripts (DataTables, Select2, AutoNumeric, Datepicker) -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/autonumeric@4.6.0/dist/autoNumeric.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

<!-- Layout Sidebar Control Script -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const sidebar         = document.getElementById('sidebar');
    const overlaySidebar  = document.getElementById('overlaySidebar');
    const toggleBtn       = document.getElementById('toggleBtn');
    const toggleBtnMobile = document.getElementById('toggleBtnMobile');

    // Restore state on desktop
    if (window.innerWidth > 768 && localStorage.getItem('sidebar-collapsed') === 'true') {
        sidebar?.classList.add('collapsed');
    }

    function toggleSidebar() {
        if (window.innerWidth <= 768) {
            sidebar?.classList.toggle('show');
            if (overlaySidebar) {
                overlaySidebar.style.display = sidebar.classList.contains('show') ? 'block' : 'none';
            }
        } else {
            sidebar?.classList.toggle('collapsed');
            localStorage.setItem('sidebar-collapsed', sidebar.classList.contains('collapsed'));
        }
    }

    toggleBtn?.addEventListener('click', toggleSidebar);
    toggleBtnMobile?.addEventListener('click', toggleSidebar);
    overlaySidebar?.addEventListener('click', toggleSidebar);

    window.addEventListener('resize', () => {
        if (window.innerWidth <= 768) {
            sidebar?.classList.remove('collapsed');
        } else {
            sidebar?.classList.toggle('collapsed', localStorage.getItem('sidebar-collapsed') === 'true');
            if (overlaySidebar) overlaySidebar.style.display = 'none';
        }
    });
});
</script>

<?= $this->renderSection('scripts') ?>
</body>
</html>
