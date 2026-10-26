<?php echo view('header'); ?>
<div class="card border-0 shadow-sm">
  <div class="card-header d-flex justify-content-between"><strong>Detail Jukir</strong><a href="<?= base_url('anggota') ?>" class="btn btn-secondary btn-sm">Kembali</a></div>
  <div class="card-body">
    <?php $foto = trim((string) ($anggota['anggota_foto'] ?? '')); ?>
    <div class="row g-3 mb-4"><div class="col-md-2"><img src="<?= $foto ? base_url('uploads/anggota_foto/' . $foto) : base_url('images/no-photo.png') ?>" class="img-fluid rounded" style="max-height:180px;object-fit:cover" alt="Foto jukir" onerror="this.onerror=null;this.src='<?= base_url('images/no-photo.png') ?>';"></div><div class="col-md-10"><h4><?= esc($anggota['anggota_nama'] ?? '-') ?></h4><p class="mb-1">ID: <?= esc($anggota['anggota_id']) ?></p><p class="mb-1">No. Registrasi: <?= esc($anggota['anggota_newnoreg'] ?? $anggota['anggota_noreg'] ?? '-') ?></p><p class="mb-1">No. HP: <?= esc($anggota['anggota_nohp'] ?? '-') ?></p><p>Alamat: <?= esc($anggota['anggota_alamat'] ?? '-') ?></p></div></div>
    <h5>Titik Parkir (<?= count($titpars) ?>)</h5>
    <?php if (!$titpars): ?><div class="alert alert-info">Jukir ini belum memiliki titik parkir.</div><?php else: ?><div class="table-responsive"><table class="table table-bordered table-hover"><thead><tr><th>No</th><th>Nama Titik Parkir</th><th>Lokasi</th><th>Status</th><th>Masa Berlaku</th></tr></thead><tbody><?php foreach ($titpars as $i => $titpar): ?><tr><td><?= $i + 1 ?></td><td><?= esc($titpar['titpar_namatempat'] ?? '-') ?></td><td><?= esc($titpar['titpar_lokasi'] ?? '-') ?></td><td><?= esc($titpar['titpargrup_staanggota'] ?? '-') ?></td><td><?= esc(($titpar['titpargrup_masaberlakustart'] ?? '-') . ' s/d ' . ($titpar['titpargrup_masaberlakuend'] ?? '-')) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
  </div>
</div>
<?php echo view('footer'); ?>
