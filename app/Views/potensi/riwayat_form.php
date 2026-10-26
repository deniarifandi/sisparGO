<?php echo view('header'); ?>
<div class="card">
  <div class="card-header"><strong>Edit Riwayat Potensi</strong></div>
  <div class="card-body">
    <form method="post" action="<?= base_url('potensi/riwayat/update/' . $riwayat['potensi_riwayat_id']) ?>">
      <?= csrf_field() ?>
      <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
      <?php endif; ?>
      <div class="row g-2 mb-3">
        <div class="col-md-6"><label class="form-label">Berlaku Mulai</label><input type="date" name="berlaku_mulai" class="form-control" value="<?= esc(old('berlaku_mulai', $riwayat['berlaku_mulai'])) ?>" required></div>
        <div class="col-md-6"><label class="form-label">Berlaku Sampai</label><input type="date" name="berlaku_sampai" class="form-control" value="<?= esc(old('berlaku_sampai', $riwayat['berlaku_sampai'] ?? '')) ?>"><small class="text-muted">Kosongkan jika masih berlaku. Jika ada riwayat berikutnya, tanggal akhir paling lambat sehari sebelumnya.</small></div>
      </div>
      <div class="row g-2">
      <?php foreach (['senin','selasa','rabu','kamis','jumat','sabtu','minggu'] as $field): ?>
        <div class="col-md-3"><label class="form-label text-capitalize"><?= $field ?></label><input type="text" inputmode="numeric" name="<?= $field ?>" class="form-control autonumeric" value="<?= esc(old($field, (string) round((float) ($riwayat[$field] ?? 0)))) ?>" required></div>
      <?php endforeach; ?>
      </div>
      <div class="mt-3"><button class="btn btn-primary">Simpan</button> <a class="btn btn-secondary" href="<?= base_url('transaksi/va/' . $riwayat['potensi_va']) ?>">Batal</a></div>
    </form>
  </div>
</div>
<script>
  document.querySelectorAll('.autonumeric').forEach(el => {
    new AutoNumeric(el, {
      digitGroupSeparator: ',',
      decimalCharacter: '.',
      decimalPlaces: 0,
      minimumValue: '0',
      unformatOnSubmit: true
    });
  });
</script>
<?php echo view('footer'); ?>
