<?php if (empty($modal)): ?><?= view('header') ?><?php endif ?>
<?php
$sections = [
    'Data Pribadi' => ['nama_lengkap'=>'Nama Lengkap', 'nik'=>'No. KTP / NIK', 'nomor_kk'=>'Nomor KK', 'no_handphone'=>'No. Handphone', 'tanggal_lahir'=>'Tanggal Lahir', 'tempat_lahir'=>'Tempat Lahir', 'jenis_kelamin'=>'Jenis Kelamin'],
    'Alamat' => ['alamat_lengkap'=>'Alamat Lengkap', 'provinsi'=>'Provinsi', 'kabupaten_kota'=>'Kabupaten / Kota', 'kecamatan_nama'=>'Kecamatan', 'kelurahan_nama'=>'Kelurahan / Desa'],
    'Data Tambahan' => ['pekerjaan'=>'Pekerjaan', 'pendidikan_terakhir'=>'Pendidikan Terakhir', 'agama'=>'Agama'],
    'Informasi Pengajuan' => ['anggota_id'=>'ID Login / Anggota', 'status'=>'Status', 'created_at'=>'Tanggal Pengajuan', 'updated_at'=>'Terakhir Diperbarui', 'catatan_verifikasi'=>'Catatan Verifikasi', 'diproses_oleh'=>'ID Admin Pemeriksa', 'diproses_at'=>'Waktu Keputusan'],
];
$display = array_merge($row, $wilayah);
$display['status'] = ucfirst(str_replace('_', ' ', $row['status']));
$display['kecamatan_nama'] = $wilayah['kecamatan_nama'] ?? ($row['kecamatan_id'] ?: '-');
$display['kelurahan_nama'] = $wilayah['kelurahan_nama'] ?? ($row['kelurahan_id'] ?: '-');
?>
<div class="container-fluid py-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div><h4 class="fw-bold mb-1">Detail Pengajuan Pendaftaran</h4><p class="text-muted mb-0"><?= esc($row['nomor_pengajuan']) ?></p></div>
    <?php if (empty($modal)): ?><a href="<?= base_url('admin/pendaftaran-anggota') ?>" class="btn btn-outline-secondary btn-sm">Kembali ke Daftar</a><?php endif ?>
  </div>
  <div class="row g-4">
    <div class="col-lg-8">
      <?php if ($row['status'] === 'menunggu'): ?>
        <section class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body p-4">
          <h5 class="fw-bold">Keputusan Pengajuan</h5>
          <form method="post" action="<?= base_url('admin/pendaftaran-anggota/approve/' . (int) $row['id']) ?>" data-review-form>
            <?= csrf_field() ?>
            <label for="reviewNote" class="form-label">Catatan verifikasi <span class="text-muted">(wajib untuk Minta Perbaikan / Tolak)</span></label>
            <textarea id="reviewNote" name="catatan_verifikasi" class="form-control mb-3" rows="3" maxlength="2000"></textarea>
            <div class="d-flex gap-2">
              <button type="submit" name="decision" value="approve" class="btn btn-success">Setujui</button>
              <button type="submit" name="decision" value="correction" formaction="<?= base_url('admin/pendaftaran-anggota/perbaikan/' . (int) $row['id']) ?>" class="btn btn-warning">Minta Perbaikan</button>
              <button type="submit" name="decision" value="reject" formaction="<?= base_url('admin/pendaftaran-anggota/reject/' . (int) $row['id']) ?>" class="btn btn-danger">Tolak</button>
            </div>
          </form>
        </div></section>
      <?php endif ?>

      <?php foreach ($sections as $title => $fields): ?>
        <section class="card border-0 shadow-sm rounded-4 mb-4">
          <div class="card-body p-4"><h5 class="fw-bold mb-4"><?= esc($title) ?></h5>
            <dl class="row mb-0">
              <?php foreach ($fields as $key => $label): ?>
                <dt class="col-sm-4 text-muted fw-normal mb-2"><?= esc($label) ?></dt>
                <dd class="col-sm-8 mb-3 text-break" style="white-space: pre-line"><?= esc($display[$key] ?? '') ?: '-' ?></dd>
              <?php endforeach ?>
            </dl>
          </div>
        </section>
      <?php endforeach ?>
    </div>
    <div class="col-12 order-last">
      <section class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body p-4">
        <h5 class="fw-bold mb-4">Riwayat Pengajuan</h5>
        <?php foreach (($history ?? []) as $event): ?>
          <div class="border-bottom pb-3 mb-3">
            <strong><?= esc(ucwords(str_replace('_',' ',$event['aksi']))) ?></strong>
            <span class="text-muted small"> · <?= esc($event['created_at']) ?> · <?= esc($event['aktor']) ?></span>
            <div><?= esc(str_replace('_',' ',$event['status_awal'] ?? '-')) ?> → <?= esc(str_replace('_',' ',$event['status_akhir'])) ?></div>
            <?php if (!empty($event['catatan'])): ?><p class="mb-1 text-break" style="white-space:pre-line"><?= esc($event['catatan']) ?></p><?php endif ?>
            <?php if ($event['aksi']==='kirim_ulang'):
              $before=json_decode($event['data_sebelum'] ?? '{}',true) ?: [];
              $after=json_decode($event['data_sesudah'] ?? '{}',true) ?: [];
              $changes=[];
              foreach(['nama_lengkap','nomor_kk','no_handphone','tanggal_lahir','tempat_lahir','jenis_kelamin','alamat_lengkap','provinsi','kabupaten_kota','kecamatan_id','kelurahan_id','pekerjaan','pendidikan_terakhir','agama','pas_foto','dokumen_ktp','dokumen_kk'] as $field) {
                  if(($before[$field] ?? null)!==($after[$field] ?? null)) $changes[]=str_replace('_',' ',$field);
              }
            ?>
              <div class="small text-muted">Diperbarui: <?= esc($changes ? implode(', ',$changes) : 'Tidak ada perubahan data') ?></div>
            <?php endif ?>
          </div>
        <?php endforeach ?>
      </div></section>
    </div>
    <div class="col-lg-4">
      <section class="card border-0 shadow-sm rounded-4"><div class="card-body p-4">
        <h5 class="fw-bold mb-4">Dokumen &amp; Foto</h5>
        <?php foreach (['pas_foto'=>'Pas Foto', 'dokumen_ktp'=>'Dokumen KTP', 'dokumen_kk'=>'Dokumen KK'] as $field => $label): ?>
          <div class="mb-4"><h6><?= esc($label) ?></h6>
            <?php if (!empty($row[$field])): ?>
              <?php $url = base_url('admin/pendaftaran-anggota/dokumen/' . (int) $row['id'] . '/' . $field); ?>
              <a href="<?= esc($url, 'attr') ?>" target="_blank" rel="noopener" class="d-block">
                <img src="<?= esc($url, 'attr') ?>" alt="<?= esc($label, 'attr') ?>" class="img-fluid rounded border" style="max-height:240px;object-fit:contain" loading="lazy">
                <span class="d-block small mt-2">Lihat ukuran penuh</span>
              </a>
            <?php else: ?><p class="text-muted small">Tidak diunggah.</p><?php endif ?>
          </div>
        <?php endforeach ?>
      </div></section>
    </div>
  </div>
</div>
<?php if (empty($modal)): ?><?= view('admin/pendaftaran_review_script') ?><?= view('footer') ?><?php endif ?>
