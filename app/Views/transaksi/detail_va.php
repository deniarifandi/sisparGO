<?php echo view('header'); ?>

<style>
    .table-vertical-lines th,
    .table-vertical-lines td {
        border-left: 1px solid #dee2e6;
    }

    .table-vertical-lines th:last-child,
    .table-vertical-lines td:last-child {
        border-right: 1px solid #dee2e6;
    }

    #pageLoadingNotice {
        display: none;
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 2000;
        min-width: 220px;
    }
</style>

<div id="pageLoadingNotice" class="alert alert-info shadow-sm mb-0" role="status">
    <span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
    Sedang memuat data...
</div>

<?php if (session()->getFlashdata('message')): ?>
    <div class="alert alert-info">
        <?= session()->getFlashdata('message'); ?>
    </div>
<?php endif; ?>

<?php if (!$data): ?>

    <div class="alert alert-danger">
        Data VA tidak ditemukan.
    </div>

<?php else: ?>

<div class="container-fluid">

    <!-- DETAIL VA / PEMILIK -->
    <div class="card mb-4">


        <div class="card-header">
           

             <div class="mb-3">
                 <strong>Detail Virtual Account</strong>
        <a href="<?= base_url() ?>potensi/realisasi" class="btn btn-sm btn-secondary float-end">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali
        </a>
    </div>

        </div>

        <div class="card-body">
    <div class="row g-3">

        <!-- KIRI: DETAIL -->
        <div class="col-md-9">
            <div class="row g-3">

                <!-- VA -->
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted d-block">
                            Virtual Account
                        </small>
                        <strong class="text-primary">
                            <?= esc($data['va_owner_va'] ?? '-') ?>
                        </strong>
                    </div>
                </div>

                <!-- Nama Anggota -->
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted d-block">
                            Nama Anggota
                        </small>
                        <strong>
                            <?= esc(
                                $data['va_owner_nama']
                                ?? $data['anggota_nama']
                                ?? '-'
                            ) ?>
                        </strong>
                    </div>
                </div>

                <!-- ID Anggota -->
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted d-block">
                            ID Anggota
                        </small>
                        <strong>
                            <?= esc($data['anggota_id'] ?? '-') ?>
                        </strong>
                    </div>
                </div>

                <!-- No Reg -->
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted d-block">
                            No. Registrasi
                        </small>
                        <strong>
                            <?= esc(
                                $data['anggota_newnoreg']
                                ?? $data['anggota_noreg']
                                ?? '-'
                            ) ?>
                        </strong>
                    </div>
                </div>

                <!-- HP -->
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted d-block">
                            No. HP
                        </small>
                        <strong>
                            <?= esc(
                                $data['anggota_nohp']
                                ?? $data['va_owner_hp']
                                ?? '-'
                            ) ?>
                        </strong>
                    </div>
                </div>

                <!-- Email -->
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted d-block">
                            Email
                        </small>
                        <strong>
                            <?= esc($data['va_owner_email'] ?? '-') ?>
                        </strong>
                    </div>
                </div>

                <!-- Tempat Parkir -->
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted d-block">
                            Tempat Parkir
                        </small>
                        <strong>
                            <?= esc($data['titpar_namatempat'] ?? '-') ?>
                        </strong>
                    </div>
                </div>

                <!-- Lokasi -->
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted d-block">
                            Lokasi
                        </small>
                        <strong>
                            <?= esc($data['titpar_lokasi'] ?? '-') ?>
                        </strong>
                    </div>
                </div>

                <!-- Status Titik Parkir -->
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted d-block">
                            Status Titik Parkir
                        </small>
                        <?php $titparAktif = (int) ($data['titpar_status'] ?? 0) === 3; ?>
                        <span class="badge <?= $titparAktif ? 'bg-success' : 'bg-danger' ?>">
                            <?= $titparAktif ? 'Aktif' : 'Nonaktif' ?>
                        </span>
                    </div>
                </div>

                <!-- Masa Berlaku -->
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted d-block">
                            Masa Berlaku KTA
                        </small>
                 <strong>
    <?= !empty($data['titpargrup_masaberlakustart'])
        ? date('j M Y', strtotime($data['titpargrup_masaberlakustart']))
        : '-'
    ?>
    -
    <?= !empty($data['titpargrup_masaberlakuend'])
        ? date('j M Y', strtotime($data['titpargrup_masaberlakuend']))
        : '-'
    ?>
</strong>
                    </div>
                </div>

            </div>
        </div>


        <!-- KANAN: FOTO -->
        <div class="col-md-3">
            <div class="border rounded p-3 h-100 d-flex flex-column align-items-center justify-content-center">

                <small class="text-muted fw-bold mb-2">
                    FOTO ANGGOTA
                </small>

                <?php
                    $foto = $data['anggota_foto'] ?? '';
                    $fotoUrl = !empty($foto)
                        ? base_url('uploads/anggota_foto/' . $foto)
                        : base_url('images/no-photo.png');
                ?>

                <img
                    src="<?= esc($fotoUrl) ?>"
                    alt="Foto Anggota"
                    class="img-fluid rounded shadow-sm"
                    style="
                        width: 180px;
                        height: 220px;
                        object-fit: cover;
                    "
                    onerror="this.onerror=null; this.src='<?= base_url('images/no-photo.png') ?>';"
                >

                <div class="mt-2 text-center">
                    <strong class="d-block">
                        <?= esc($data['va_owner_nama'] ?? $data['anggota_nama'] ?? '-') ?>
                    </strong>

                    <small class="text-muted">
                        ID: <?= esc($data['anggota_id'] ?? '-') ?>
                    </small>
                </div>

            </div>
        </div>

    </div>
</div>


    </div>


    <!-- SUMMARY TRANSAKSI -->
    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-muted">
                        Total Transaksi
                    </small>

                    <h4 class="fw-bold mb-0">
                        <?= number_format(
                            $data['total_transaksi'] ?? 0,
                            0,
                            ',',
                            '.'
                        ) ?>
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-muted">
                        Total Nominal
                    </small>

                    <h4 class="fw-bold text-primary mb-0">
                        Rp <?= number_format(
                            $data['total_nominal'] ?? 0,
                            0,
                            ',',
                            '.'
                        ) ?>
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-muted">
                        Masa Berlaku VA
                    </small>

                   <h4 class="fw-bold mb-0">
    <?php
    if (!empty($data['va_owner_expired'])) {
        $date = new DateTime($data['va_owner_expired']);

        $bulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        echo $date->format('j') . ' ' .
             $bulan[(int) $date->format('n')] . ' ' .
             $date->format('Y');
    } else {
        echo '-';
    }
    ?>
</h4>
                </div>
            </div>
        </div>

    </div>

    <!-- POTENSI AKTIF -->
    <?php
        $potensi = $data['potensi'] ?? null;
        $potensiFields = ['senin','selasa','rabu','kamis','jumat','sabtu','minggu'];
        $potensiLabel = ['senin'=>'Senin','selasa'=>'Selasa','rabu'=>'Rabu','kamis'=>'Kamis','jumat'=>'Jumat','sabtu'=>'Sabtu','minggu'=>'Minggu'];
    ?>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Potensi Aktif</strong>
            <span class="badge bg-primary">Berlaku saat ini</span>
        </div>
        <div class="card-body">
            <?php if ($potensi): ?>
                <div class="row g-2">
                    <?php foreach ($potensiFields as $field): ?>
                        <div class="col-6 col-md-3 col-lg-2">
                            <div class="border rounded p-2 h-100">
                                <small class="text-muted d-block"><?= $potensiLabel[$field] ?></small>
                                <strong>Rp <?= number_format((float)($potensi[$field] ?? 0), 0, ',', '.') ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-muted">Belum ada data potensi.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- RIWAYAT POTENSI -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Riwayat Potensi</strong>
            <?php if (session('role') !== null && (int) session('role') <= 2): ?>
            <a href="<?= base_url('potensi/edit/' . ($data['va_owner_va'] ?? '')) ?>?return_to=<?= urlencode('transaksi/va/' . ($data['va_owner_va'] ?? '')) ?>" class="btn btn-sm btn-warning">
                <i class="bi bi-pencil-square me-1"></i> Ubah Potensi
            </a>
            <?php endif; ?>
        </div>
        <div class="card-body table-responsive">
            <?php $riwayat = $data['potensi_riwayat'] ?? []; ?>
            <?php if ($riwayat): ?>
                <table class="table table-sm table-striped align-middle table-vertical-lines" style="font-size:12px;">
                    <thead><tr><th>Mulai Berlaku</th><th>Sampai</th><th>Senin</th><th>Selasa</th><th>Rabu</th><th>Kamis</th><th>Jumat</th><th>Sabtu</th><th>Minggu</th><?php if (session('role') !== null && (int) session('role') <= 2): ?><th>Aksi</th><?php endif; ?></tr></thead>
                    <tbody>
                    <?php foreach ($riwayat as $r): ?>
                        <tr>
                            <td><?= esc(date('d-m-Y', strtotime($r['berlaku_mulai']))) ?></td>
                            <td>
                                <?php if ($r['berlaku_sampai']): ?>
                                    <?= esc(date('d-m-Y', strtotime($r['berlaku_sampai']))) ?>
                                <?php else: ?>
                                    <span class="badge bg-success">Sekarang</span>
                                <?php endif; ?>
                            </td>
                            <?php foreach (['senin','selasa','rabu','kamis','jumat','sabtu','minggu'] as $hari): ?>
                                <td>Rp <?= number_format((float)($r[$hari] ?? 0), 0, ',', '.') ?></td>
                            <?php endforeach; ?>
                            <?php if (session('role') !== null && (int) session('role') <= 2): ?>
                            <td class="text-nowrap">
                                <a class="btn btn-sm btn-warning" href="<?= base_url('potensi/riwayat/edit/' . $r['potensi_riwayat_id']) ?>">Edit</a>
                                <form method="post" action="<?= base_url('potensi/riwayat/delete/' . $r['potensi_riwayat_id']) ?>" class="d-inline" onsubmit="return confirm('Hapus riwayat potensi ini?');">
                                    <?= csrf_field() ?><button class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="text-muted">Belum ada riwayat potensi. Riwayat akan tercatat setelah migrasi dan perubahan berikutnya.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- REKAP POTENSI VS REALISASI -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Detail Potensi Bulanan dan Realisasi</strong>
            <form method="get" class="d-flex align-items-center gap-2">
                <label for="tahunRekap" class="mb-0">Tahun</label>
                <select id="tahunRekap" name="tahun" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php for ($year = date('Y'); $year >= date('Y') - 5; $year--): ?>
                        <option value="<?= $year ?>" <?= ((int)($filterYear ?? date('Y')) === $year) ? 'selected' : '' ?>><?= $year ?></option>
                    <?php endfor; ?>
                </select>
            </form>
        </div>
        <div class="card-body table-responsive">
            <?php $rekapBulanan = $data['potensi_realisasi_bulanan'] ?? []; ?>
            <table class="table table-sm table-striped align-middle table-vertical-lines" style="font-size:12px;">
                <thead><tr><th>Tahun</th><th>Bulan</th><th>Potensi</th><th>Pengurangan Kendala</th><th>Potensi Setelah Kendala</th><th>Realisasi</th><th>Selisih</th><th>Snapshot Mulai</th></tr></thead>
                <tbody>
                <?php foreach ($rekapBulanan as $r): ?>
                    <tr>
                        <td><?= esc($r['tahun']) ?></td><td><?= esc(date('F', mktime(0,0,0,$r['bulan'],1))) ?></td>
                        <td class="text-end">Rp <?= number_format($r['potensi'], 0, ',', '.') ?></td>
                        <td class="text-end text-danger">Rp <?= number_format($r['pengurangan_kendala'] ?? 0, 0, ',', '.') ?></td>
                        <td class="text-end">Rp <?= number_format($r['potensi_setelah_kendala'] ?? $r['potensi'], 0, ',', '.') ?></td>
                        <td class="text-end">Rp <?= number_format($r['realisasi'], 0, ',', '.') ?></td>
                        <td class="text-end">Rp <?= number_format($r['realisasi'] - ($r['potensi_setelah_kendala'] ?? $r['potensi']), 0, ',', '.') ?></td>
                        <td><?= esc($r['snapshot_mulai']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><strong>Rekap Potensi Tahunan dan Realisasi</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-sm table-striped align-middle table-vertical-lines" style="font-size:12px;">
                <thead><tr><th>Tahun</th><th>Potensi Tahunan Berjalan</th><th>Kendala</th><th>Potensi Setelah Kendala</th><th>Realisasi</th><th>Selisih</th><th>Persentase</th></tr></thead>
                <tbody>
                <?php foreach (($data['potensi_realisasi_tahunan'] ?? []) as $r): ?>
                    <tr>
                        <td><?= esc($r['tahun']) ?></td><td class="text-end">Rp <?= number_format($r['potensi'], 0, ',', '.') ?></td>
                        <td class="text-end">Rp <?= number_format($r['kendala'] ?? 0, 0, ',', '.') ?></td>
                        <td class="text-end">Rp <?= number_format($r['potensi_setelah_kendala'] ?? ($r['potensi'] - ($r['kendala'] ?? 0)), 0, ',', '.') ?></td>
                        <td class="text-end">Rp <?= number_format($r['realisasi'], 0, ',', '.') ?></td>
                        <td class="text-end">Rp <?= number_format($r['realisasi'] - ($r['potensi_setelah_kendala'] ?? ($r['potensi'] - ($r['kendala'] ?? 0))), 0, ',', '.') ?></td>
                        <td class="text-end"><?= ($r['potensi_setelah_kendala'] ?? 0) > 0 ? number_format($r['realisasi'] / $r['potensi_setelah_kendala'] * 100, 2, ',', '.') : '0,00' ?>%</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- KENDALA USER -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Kendala User dan Pengurangan</strong>
            <div class="d-flex align-items-center gap-3">
                <form method="get" class="d-flex align-items-center gap-2 mb-0">
                    <label for="tahunKendala" class="mb-0">Tahun</label>
                    <select id="tahunKendala" name="tahun" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php for ($year = date('Y'); $year >= date('Y') - 5; $year--): ?>
                            <option value="<?= $year ?>" <?= ((int)($filterYear ?? date('Y')) === $year) ? 'selected' : '' ?>><?= $year ?></option>
                        <?php endfor; ?>
                    </select>
                </form>
                <span class="fw-bold text-danger">Total Pengurangan: Rp <?= number_format((float)($data['kendala_total'] ?? 0), 0, ',', '.') ?></span>
            </div>
        </div>
        <div class="card-body table-responsive">
            <?php $kendala = $data['kendala'] ?? []; ?>
            <?php if ($kendala): ?>
                <table id="kendalaDetailTable" class="table table-sm table-striped table-hover align-middle table-vertical-lines" style="font-size:13px; width:100%;">
                    <thead class="table-light"><tr><th>ID</th><th>Tanggal</th><th>Kendala</th><th>Potongan</th><th>Status</th><th class="text-end">Pengurangan</th></tr></thead>
                    <tbody>
                    <?php foreach ($kendala as $k): ?>
                        <tr>
                            <td><?= esc($k['kendala_id']) ?></td>
                            <td data-order="<?= esc(strtotime($k['created_at'])) ?>" class="text-nowrap"><?= esc(date('d-m-Y H:i', strtotime($k['created_at']))) ?></td>
                            <td style="min-width:240px; white-space:normal;"><?= esc($k['kendala_alasan'] ?? '-') ?></td>
                            <td><?= number_format((float)($k['persentase_potongan'] ?? 0), 0, ',', '.') ?>%</td>
                            <td>
                                <?php if ((int)$k['approval'] === 1): ?><span class="badge bg-success">Approved</span>
                                <?php elseif ((int)$k['approval'] === 2): ?><span class="badge bg-danger">Rejected</span>
                                <?php else: ?><span class="badge bg-warning text-dark">Pending</span><?php endif; ?>
                            </td>
                            <td class="text-end text-nowrap">Rp <?= number_format((float)($k['pengurangan'] ?? 0), 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="text-muted">Belum ada kendala user untuk VA ini.</div>
            <?php endif; ?>
        </div>
    </div>


    <!-- DAFTAR TRANSAKSI -->
    <div class="card">

        <div class="card-header">
            <strong>
                Daftar Transaksi
            </strong>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table id="transaksiTable"
                       class="table table-striped table-hover align-middle table-vertical-lines"
                       style="font-size: 13px; width: 100%;">

                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Tanggal</th>
                            <th>Nominal</th>
                            <th>Jenis</th>
                            <th>Status</th>
                            <th>Konfirmasi WA</th>
                            <th>Reference</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (!empty($data['transaksi'])): ?>

                        <?php foreach ($data['transaksi'] as $trx): ?>

                            <tr>

                                <td class="text-nowrap">
                                    <?= esc($trx['transaksi_id']) ?>
                                </td>

                                <td data-order="<?= esc(strtotime($trx['transaksi_tanggal'] ?? '')) ?>" class="text-nowrap">
                                    <?= !empty($trx['transaksi_tanggal'])
                                        ? date(
                                            'd F Y H:i',
                                            strtotime($trx['transaksi_tanggal'])
                                        )
                                        : '-'
                                    ?>
                                </td>

                                <td class="text-end fw-bold text-nowrap">
                                    Rp <?= number_format(
                                        (float) $trx['transaksi_nominal'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                </td>

                                <td class="text-nowrap">
                                    <?= esc($trx['jenis'] ?? '-') ?>
                                </td>

                                <td class="text-nowrap">

                                    <?php
                                    $status = $trx['latestTransactionStatus'] ?? '';
                                    ?>

                                    <?php if ($status === '00'): ?>

                                        <span class="badge bg-success">
                                            <?= esc(
                                                $trx['transactionStatusDesc']
                                                ?? 'Success'
                                            ) ?>
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-secondary">
                                            <?= esc(
                                                $trx['transactionStatusDesc']
                                                ?? $status
                                                ?? '-'
                                            ) ?>
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td class="text-nowrap">

                                    <?php if (($trx['wa_konfirmasi'] ?? 0) == 1): ?>

                                        <span class="badge bg-success">
                                            Terkonfirmasi
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-secondary">
                                            Belum
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>
                                    <?= esc(
                                        $trx['coreReferenceNo']
                                        ?? '-'
                                    ) ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                    </tbody>

                    <tfoot>
                        <tr>
                            <th colspan="2" class="text-end">
                                Total:
                            </th>

                            <th class="text-end">
                                Rp <?= number_format(
                                    $data['total_nominal'] ?? 0,
                                    0,
                                    ',',
                                    '.'
                                ) ?>
                            </th>

                            <th colspan="4"></th>
                        </tr>
                    </tfoot>

                </table>

            </div>

        </div>

    </div>

</div>

<?php endif; ?>


<script>

$(document).ready(function() {

    $('#kendalaDetailTable').DataTable({
        processing: true,
        pageLength: 10,
        lengthMenu: [
            [10, 25, 50, 100],
            [10, 25, 50, 100]
        ],
        order: [[1, 'desc']],
        language: {
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data',
            zeroRecords: 'Data tidak ditemukan',
            processing: '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...',
            paginate: {
                first: 'Awal',
                last: 'Akhir',
                next: 'Berikutnya',
                previous: 'Sebelumnya'
            }
        }
    });

    $('#transaksiTable').DataTable({

        processing: true,

        pageLength: 25,

        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "All"]
        ],

        order: [
            [1, 'desc']
        ],

        dom: '<"top"lBf>rt<"bottom"ip><"clear">',

        buttons: [
            'excelHtml5',
            'csvHtml5',
            'pdfHtml5',
            'print'
        ]

    });

    const loadingNotice = $('#pageLoadingNotice');
    let loadingTimer;

    function showLoadingNotice() {
        clearTimeout(loadingTimer);
        loadingNotice.stop(true, true).fadeIn(120);
    }

    function hideLoadingNotice() {
        loadingTimer = setTimeout(function() {
            loadingNotice.fadeOut(180);
        }, 250);
    }

    $('#tahunRekap').closest('form').on('submit', showLoadingNotice);

    $('#kendalaDetailTable, #transaksiTable')
        .on('processing.dt', function(event, settings, processing) {
            processing ? showLoadingNotice() : hideLoadingNotice();
        })
        .on('search.dt length.dt page.dt', function() {
            showLoadingNotice();
            hideLoadingNotice();
        });

});

</script>





<?php echo view('footer'); ?>
