<?php echo view('header'); ?>
<?php
$isEdit = isset($anggota) && is_array($anggota);
$anggota = $anggota ?? [];
$value = static fn (string $field): string => old($field, $anggota[$field] ?? '');
$photo = $anggota['anggota_foto'] ?? '';

$documentUrl = static function (string $field) use ($anggota): string {
    $file = $anggota[$field] ?? '';
    if (preg_match('~^pendaftaran/([0-9]+)/[^/]+$~', $file, $match)) {
        $kind = $field === 'foto_ktp' ? 'dokumen_ktp' : 'dokumen_kk';
        return base_url('admin/pendaftaran-anggota/dokumen/' . $match[1] . '/' . $kind);
    }

    return $file ? base_url('uploads/' . $field . '/' . $file) : '';
};

$documentIsImage = static function (string $field) use ($anggota): bool {
    $file = strtolower((string) ($anggota[$field] ?? ''));
    return $file !== '' && in_array(pathinfo($file, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'webp'], true);
};
?>
<style>
    .anggota-form-card { max-width: 1080px; margin: 24px auto 48px; border-radius: 18px; overflow: hidden; }
    .anggota-form-card .card-header { background: #fff !important; color: #172033; padding: 20px 28px !important; border-bottom: 1px solid #e9edf3; }
    .anggota-form-card .card-header strong { font-size: 1.25rem; font-weight: 700; }
    .anggota-form-card .card-body { background: #fbfcfe; padding: 32px !important; }
    
    /* Section Title */
    .section-title { font-size: 0.85rem; letter-spacing: 0.1em; text-transform: uppercase; font-weight: 700; color: #2878d8; padding-bottom: 12px; border-bottom: 2px solid #e9f0ff; margin-bottom: 20px; margin-top: 32px; }
    .section-title:first-child { margin-top: 0; }
    
    /* Inputs */
    .form-label { font-weight: 600; color: #344054; font-size: 0.9rem; }
    .form-control, .form-select { min-height: 48px; border-color: #d9e1ec; border-radius: 10px; padding: 10px 16px; }
    .form-control:focus, .form-select:focus { border-color: #2878d8; box-shadow: 0 0 0 .25rem rgba(40, 120, 216, 0.15); }
    
    /* Document Boxes */
    .document-box { background: #fff; border: 1px solid #e0e8f6; border-radius: 14px; padding: 20px; height: 100%; box-shadow: 0 2px 10px rgba(0,0,0,0.01); display: flex; flex-direction: column; }
    .camera-guide { position: absolute; z-index: 2; left: 50%; top: 50%; transform: translate(-50%, -50%); width: min(78%, 315px); aspect-ratio: 3 / 4; border: 3px solid rgba(255,255,255,.95); border-radius: 10px; box-shadow: 0 0 0 9999px rgba(0,0,0,.28), 0 0 0 1px rgba(0,0,0,.35); pointer-events: none; }
    .camera-guide::after { content: 'Sesuaikan dalam bingkai 3:4'; position: absolute; left: 50%; bottom: -30px; transform: translateX(-50%); color: #fff; font-size: .78rem; white-space: nowrap; text-shadow: 0 1px 3px #000; }
    .camera-guide.landscape { width: min(88%, 420px); aspect-ratio: 4 / 3; }
    .camera-guide.landscape::after { content: 'Sesuaikan dokumen dalam bingkai 4:3'; }
    .current-document { max-width: 100%; max-height: 150px; margin: 0 auto 15px auto; border: 1px solid #dee2e6; border-radius: 10px; object-fit: contain; display: block; }
    .selected-document-preview { display: none; margin: 0 0 15px; padding: 10px; border: 1px solid #bcd5f7; border-radius: 10px; background: #f4f8ff; }
    .selected-document-preview small { display: block; margin-bottom: 7px; color: #2878d8; font-weight: 600; text-align: left; }
    .selected-document-preview img { display: block; width: 100%; max-height: 180px; margin: 0 auto; border-radius: 8px; object-fit: contain; background: #fff; }
    .comparison-panel { height: 100%; min-height: 320px; padding: 18px; border: 1px solid #e3e9f2; border-radius: 14px; background: #f8fafc; display: flex; flex-direction: column; }
    .comparison-stage { flex: 1; min-height: 250px; display: flex; align-items: center; justify-content: center; overflow: hidden; border-radius: 10px; background: #eef2f7; }
    .comparison-stage img { display: none; width: 100%; height: 100%; max-height: 55vh; object-fit: contain; }
    .comparison-editor-canvas { display: none; max-width: 100%; max-height: 55vh; background: #fff; outline: 3px solid #111; outline-offset: -3px; cursor: grab; touch-action: none; }
    .comparison-editor-canvas.is-dragging { cursor: grabbing; }
    .comparison-placeholder { color: #7b8798; font-size: .9rem; padding: 24px; text-align: center; }
    .comparison-controls { display: none; margin-top: 14px; padding: 14px; border: 1px solid #e3e9f2; border-radius: 10px; background: #fff; }
    .comparison-control-row { display: flex; align-items: center; gap: 10px; }
    .comparison-control-row + .comparison-control-row { margin-top: 12px; }
    .comparison-control-row .form-range { flex: 1; min-height: auto; padding: 0; }
    .comparison-control-value { min-width: 48px; color: #526174; font-size: .8rem; text-align: right; }
    
    .action-bar { position: sticky; bottom: 0; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); z-index: 10; padding: 20px 0; border-top: 1px solid #e9edf3; }
</style>

<div class="card border-0 shadow-sm anggota-form-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div>
            <strong><?= $isEdit ? 'Ubah Data Anggota: ' . esc($value('anggota_nama')) : 'Tambah Anggota Baru' ?></strong>
            <?php if ($isEdit): ?>
                <div class="small text-muted mt-1"><i class="fa fa-id-card me-1"></i> NIK: <?= esc($value('anggota_noktp')) ?></div>
            <?php endif; ?>
        </div>
        <a class="btn btn-outline-secondary btn-sm rounded-pill px-3" href="<?= base_url('anggota') ?>">
            <i class="fa fa-arrow-left me-1"></i> Kembali
        </a>
    </div>
    
    <div class="card-body">
        <?php if ($message = session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                <i class="fa fa-check-circle me-1"></i> <?= esc($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        <?php endif; ?>

        <?php if (session('errors')): ?>
            <div class="alert alert-danger rounded-3 shadow-sm mb-4">
                <strong class="d-block mb-2"><i class="fa fa-exclamation-circle me-1"></i> Data belum dapat disimpan!</strong>
                <ul class="mb-0">
                    <?php foreach (session('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= $isEdit ? base_url('anggota/update/' . $anggota['anggota_id']) : base_url('anggota/store') ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            
            <div class="row g-4">
                <!-- DATA PRIBADI -->
                <div class="col-12">
                    <h6 class="section-title">Data Pribadi</h6>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">No. Registrasi</label>
                    <input type="text" name="anggota_noreg" value="<?= esc($value('anggota_noreg')) ?>" class="form-control" maxlength="20" placeholder="Opsional">
                </div>
                <div class="col-md-4">
                    <label class="form-label">No. Registrasi Baru</label>
                    <input type="text" name="anggota_newnoreg" value="<?= esc($value('anggota_newnoreg')) ?>" class="form-control" maxlength="6" placeholder="Opsional">
                </div>
                <div class="col-md-4"></div>

                <div class="col-md-6">
                    <label for="anggota_nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" id="anggota_nama" name="anggota_nama" value="<?= esc($value('anggota_nama')) ?>" class="form-control" required maxlength="150" autocomplete="name" placeholder="Sesuai KTP">
                </div>
                <div class="col-md-6">
                    <label for="anggota_noktp" class="form-label">No. KTP / NIK <span class="text-danger">*</span></label>
                    <input type="text" id="anggota_noktp" name="anggota_noktp" value="<?= esc($value('anggota_noktp')) ?>" class="form-control" required inputmode="numeric" pattern="[0-9]{16}" maxlength="16" minlength="16" autocomplete="off" placeholder="16 Digit Angka">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nomor KK</label>
                    <input type="text" name="nomor_kk" value="<?= esc($value('nomor_kk')) ?>" class="form-control" maxlength="30" inputmode="numeric" placeholder="Nomor Kartu Keluarga">
                </div>
                <div class="col-md-6">
                    <label for="anggota_nohp" class="form-label">No. Handphone</label>
                    <input type="tel" id="anggota_nohp" name="anggota_nohp" value="<?= esc($value('anggota_nohp')) ?>" class="form-control" maxlength="20" inputmode="tel" autocomplete="tel" placeholder="Contoh: 08123456789">
                </div>

                <div class="col-md-6">
                    <label for="anggota_tgllhr" class="form-label">Tanggal Lahir</label>
                    <input type="date" id="anggota_tgllhr" name="anggota_tgllhr" value="<?= esc($value('anggota_tgllhr')) ?>" class="form-control" max="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tempat Lahir</label>
                    <input type="text" name="anggota_tmplhr" value="<?= esc($value('anggota_tmplhr')) ?>" class="form-control" maxlength="50" placeholder="Kota/Kabupaten">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="anggota_jnsklm" class="form-select">
                        <option value="">- Pilih Jenis Kelamin -</option>
                        <option value="L" <?= $value('anggota_jnsklm') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="P" <?= $value('anggota_jnsklm') === 'P' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status Keanggotaan</label>
                    <select name="anggota_status" class="form-select">
                        <option value="3" <?= (string)$value('anggota_status') === '3' ? 'selected' : '' ?>>Aktif</option>
                        <option value="2" <?= (string)$value('anggota_status') === '2' ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>

                <div class="col-12">
                    <label for="anggota_alamat" class="form-label">Alamat Lengkap</label>
                    <textarea id="anggota_alamat" name="anggota_alamat" class="form-control" rows="3" maxlength="1000" placeholder="Nama jalan, RT/RW, dsb."><?= esc($value('anggota_alamat')) ?></textarea>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Provinsi</label>
                    <select id="wilayahProvinsi" class="form-select" data-selected="<?= esc($wilayah['provinsi'] ?? '') ?>"><option value="">- Pilih Provinsi -</option></select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kabupaten / Kota</label>
                    <select id="wilayahKabupaten" class="form-select" data-selected="<?= esc($wilayah['kabupaten_kota'] ?? '') ?>" disabled><option value="">- Pilih Kabupaten/Kota -</option></select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kecamatan</label>
                    <select id="wilayahKecamatan" class="form-select" data-selected="<?= esc($wilayah['kecamatan_id'] ?? '') ?>" disabled><option value="">- Pilih Kecamatan -</option></select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kelurahan / Desa</label>
                    <select id="wilayahKelurahan" name="anggota_klrhnid" class="form-select" data-selected="<?= esc($value('anggota_klrhnid')) ?>" disabled><option value="">- Pilih Kelurahan/Desa -</option></select>
                </div>

                <!-- DATA PEKERJAAN -->
                <div class="col-12">
                    <h6 class="section-title">Data Tambahan</h6>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Pekerjaan</label>
                    <input type="text" name="anggota_peker" value="<?= esc($value('anggota_peker')) ?>" class="form-control" maxlength="100">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pendidikan Terakhir</label>
                    <input type="text" name="anggota_pente" value="<?= esc($value('anggota_pente')) ?>" class="form-control" maxlength="100">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Agama</label>
                    <select name="anggota_staagamaid" class="form-select">
                        <option value="">- Pilih Agama -</option>
                        <?php 
                        $agamaOptions = [1 => 'Islam', 2 => 'Katolik', 3 => 'Kristen', 4 => 'Hindu', 5 => 'Budha', 6 => 'Kong Hu Cu']; 
                        foreach ($agamaOptions as $id => $nama): 
                        ?>
                            <option value="<?= $id ?>" <?= (string)$value('anggota_staagamaid') === (string)$id ? 'selected' : '' ?>><?= esc($nama) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- DOKUMEN & FOTO -->
                <div class="col-12">
                    <h6 class="section-title">Dokumen & Foto</h6>
                </div>

                <!-- Box Pas Foto -->
                <div class="col-md-4 text-center">
                    <div class="document-box">
                        <label class="form-label d-block text-start">Pas Foto</label>
                        <?php if ($photo): ?>
                            <img class="current-document" src="<?= base_url('uploads/anggota_foto/' . $photo) ?>" alt="Foto Profil tersimpan">
                        <?php endif; ?>
                        <input type="file" id="anggota_foto" name="anggota_foto" class="form-control document-input d-none" accept="image/jpeg,image/png,image/webp" data-filename="fotoName" data-inline-preview="anggotaFotoInlinePreview" data-label="Pas Foto" data-empty-text="Maks. 2MB (JPG, PNG, WEBP)" data-current-url="<?= $photo ? esc(base_url('uploads/anggota_foto/' . $photo), 'attr') : '' ?>" data-current-is-image="<?= $photo ? '1' : '0' ?>">
                        <div class="selected-document-preview">
                            <small><i class="fa fa-image me-1"></i> Preview Foto Baru</small>
                            <img id="anggotaFotoInlinePreview" alt="Preview pas foto baru">
                        </div>
                        
                        <div class="mt-auto">
                            <div class="d-grid gap-2">
                                <label for="anggota_foto" class="btn btn-light btn-sm m-0"><i class="fa fa-upload me-1"></i> Pilih File</label>
                                <button type="button" class="btn btn-outline-primary btn-sm open-document-camera m-0" data-input="anggota_foto"><i class="fa fa-camera me-1"></i> Kamera</button>
                            </div>
                            <small id="fotoName" class="form-text mt-2 d-block text-start text-truncate text-muted">Maks. 2MB (JPG, PNG, WEBP)</small>
                        </div>
                    </div>
                </div>

                <!-- Box KTP -->
                <div class="col-md-4 text-center">
                    <div class="document-box">
                        <label class="form-label d-block text-start">Dokumen KTP</label>
                        
                        <?php if ($documentUrl('foto_ktp')): ?>
                            <?php if ($documentIsImage('foto_ktp')): ?>
                                <img class="current-document" src="<?= esc($documentUrl('foto_ktp')) ?>" alt="KTP tersimpan">
                            <?php else: ?>
                                <a class="btn btn-light btn-sm mb-3" href="<?= esc($documentUrl('foto_ktp')) ?>" target="_blank"><i class="fa fa-file-pdf me-1 text-danger"></i> KTP Tersimpan (PDF)</a>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <input type="file" id="foto_ktp" name="foto_ktp" class="form-control document-input d-none" accept="image/jpeg,image/png,image/webp,application/pdf" data-filename="ktpName" data-inline-preview="fotoKtpInlinePreview" data-label="Dokumen KTP" data-empty-text="Biarkan kosong jika tidak diubah." data-current-url="<?= esc($documentUrl('foto_ktp'), 'attr') ?>" data-current-is-image="<?= $documentIsImage('foto_ktp') ? '1' : '0' ?>">
                        <div class="selected-document-preview">
                            <small><i class="fa fa-image me-1"></i> Preview KTP Baru</small>
                            <img id="fotoKtpInlinePreview" alt="Preview KTP baru">
                        </div>
                        
                        <div class="mt-auto">
                            <div class="d-grid gap-2">
                                <label for="foto_ktp" class="btn btn-light btn-sm m-0"><i class="fa fa-upload me-1"></i> Pilih File</label>
                                <button type="button" class="btn btn-outline-primary btn-sm open-document-camera m-0" data-input="foto_ktp"><i class="fa fa-camera me-1"></i> Kamera</button>
                            </div>
                            <small id="ktpName" class="form-text mt-2 d-block text-start text-truncate text-muted">Biarkan kosong jika tidak diubah.</small>
                        </div>
                    </div>
                </div>

                <!-- Box KK -->
                <div class="col-md-4 text-center">
                    <div class="document-box">
                        <label class="form-label d-block text-start">Dokumen KK</label>
                        
                        <?php if ($documentUrl('foto_kk')): ?>
                            <?php if ($documentIsImage('foto_kk')): ?>
                                <img class="current-document" src="<?= esc($documentUrl('foto_kk')) ?>" alt="KK tersimpan">
                            <?php else: ?>
                                <a class="btn btn-light btn-sm mb-3" href="<?= esc($documentUrl('foto_kk')) ?>" target="_blank"><i class="fa fa-file-pdf me-1 text-danger"></i> KK Tersimpan (PDF)</a>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <input type="file" id="foto_kk" name="foto_kk" class="form-control document-input d-none" accept="image/jpeg,image/png,image/webp,application/pdf" data-filename="kkName" data-inline-preview="fotoKkInlinePreview" data-label="Dokumen KK" data-empty-text="Biarkan kosong jika tidak diubah." data-current-url="<?= esc($documentUrl('foto_kk'), 'attr') ?>" data-current-is-image="<?= $documentIsImage('foto_kk') ? '1' : '0' ?>">
                        <div class="selected-document-preview">
                            <small><i class="fa fa-image me-1"></i> Preview KK Baru</small>
                            <img id="fotoKkInlinePreview" alt="Preview KK baru">
                        </div>
                        
                        <div class="mt-auto">
                            <div class="d-grid gap-2">
                                <label for="foto_kk" class="btn btn-light btn-sm m-0"><i class="fa fa-upload me-1"></i> Pilih File</label>
                                <button type="button" class="btn btn-outline-primary btn-sm open-document-camera m-0" data-input="foto_kk"><i class="fa fa-camera me-1"></i> Kamera</button>
                            </div>
                            <small id="kkName" class="form-text mt-2 d-block text-start text-truncate text-muted">Biarkan kosong jika tidak diubah.</small>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Action Bar -->
            <div class="action-bar d-flex justify-content-end gap-3">
                <a class="btn btn-light px-4 py-2" href="<?= base_url('anggota') ?>">Batal</a>
                <button type="submit" class="btn btn-primary px-4 py-2 shadow-sm">
                    <i class="fa fa-save me-1"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Simpan Anggota' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Perbandingan Foto/Dokumen -->
<div class="modal fade" id="documentComparisonModal" tabindex="-1" aria-labelledby="documentComparisonModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="documentComparisonModalLabel">Perbandingan Foto</h5>
                    <div class="small text-muted">Pastikan foto baru sudah benar sebelum menyimpan perubahan.</div>
                </div>
            </div>
            <div class="modal-body bg-white">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="comparison-panel">
                            <h6 class="text-center mb-3">Foto Awal</h6>
                            <div class="comparison-stage">
                                <img id="comparisonOldImage" alt="Foto awal">
                                <div id="comparisonOldPlaceholder" class="comparison-placeholder">Belum ada foto awal.</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="comparison-panel">
                            <h6 class="text-center mb-3">Foto Baru</h6>
                            <div class="comparison-stage">
                                <canvas id="comparisonNewCanvas" class="comparison-editor-canvas" aria-label="Editor foto baru"></canvas>
                                <div id="comparisonNewPlaceholder" class="comparison-placeholder">Belum ada foto baru.</div>
                            </div>
                            <div id="comparisonControls" class="comparison-controls">
                                <div class="comparison-control-row">
                                    <span class="small fw-semibold" style="width: 78px;">Zoom</span>
                                    <button type="button" id="zoomOutBtn" class="btn btn-outline-secondary btn-sm" aria-label="Zoom out"><i class="fa fa-minus"></i></button>
                                    <input type="range" id="zoomRange" class="form-range" min="100" max="500" step="5" value="100" aria-label="Zoom foto">
                                    <button type="button" id="zoomInBtn" class="btn btn-outline-secondary btn-sm" aria-label="Zoom in"><i class="fa fa-plus"></i></button>
                                    <span id="zoomValue" class="comparison-control-value">100%</span>
                                </div>
                                <div class="comparison-control-row">
                                    <span class="small fw-semibold" style="width: 78px;">Brightness</span>
                                    <i class="fa fa-sun-o text-muted" aria-hidden="true"></i>
                                    <input type="range" id="brightnessRange" class="form-range" min="50" max="150" step="5" value="100" aria-label="Brightness foto">
                                    <span id="brightnessValue" class="comparison-control-value">100%</span>
                                </div>
                                <div class="comparison-control-row">
                                    <span class="small fw-semibold" style="width: 78px;">Contrast</span>
                                    <i class="fa fa-adjust text-muted" aria-hidden="true"></i>
                                    <input type="range" id="contrastRange" class="form-range" min="50" max="150" step="5" value="100" aria-label="Contrast foto">
                                    <span id="contrastValue" class="comparison-control-value">100%</span>
                                </div>
                                <div class="comparison-control-row">
                                    <span class="small fw-semibold" style="width: 78px;">Color</span>
                                    <i class="fa fa-tint text-muted" aria-hidden="true"></i>
                                    <input type="range" id="colorRange" class="form-range" min="0" max="200" step="5" value="100" aria-label="Saturasi warna foto">
                                    <span id="colorValue" class="comparison-control-value">100%</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <small class="text-muted"><i class="fa fa-arrows me-1"></i> Foto tampil penuh. Zoom dan geser jika ingin crop.</small>
                                    <button type="button" id="resetEditorBtn" class="btn btn-light btn-sm"><i class="fa fa-undo me-1"></i> Reset</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="cancelImageEditBtn" class="btn btn-light px-4">
                    Batalkan Foto Baru
                </button>
                <button type="button" id="closeDocumentPreviewBtn" class="btn btn-primary px-4" data-bs-dismiss="modal" style="display:none;">
                    Tutup Preview
                </button>
                <button type="button" id="saveImageEditBtn" class="btn btn-primary px-4">
                    <i class="fa fa-save me-1"></i> Simpan Hasil Edit
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Kamera -->
<div class="modal fade" id="cameraModal" tabindex="-1" aria-labelledby="cameraModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="cameraModalLabel">Ambil Foto</h5>
                <button type="button" class="btn-close stop-camera" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center bg-dark rounded-bottom p-0 overflow-hidden" style="position: relative;">
                <video id="cameraVideo" autoplay playsinline style="width: 100%; max-height: 70vh; object-fit: cover;"></video>
                <div class="camera-guide" aria-hidden="true"></div>
                <div class="p-3" style="position: absolute; bottom: 0; width: 100%; background: linear-gradient(transparent, rgba(0,0,0,0.8));">
                    <button type="button" id="takePhotoBtn" class="btn btn-success btn-lg rounded-pill px-4">
                        <i class="fa fa-camera"></i> Jepret
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let cameraStream = null;
    let cameraTargetInput = null;
    const cameraVideo = document.getElementById('cameraVideo');
    // Bootstrap dimuat oleh footer setelah section halaman; inisialisasi lazy.
    let cameraModal = null;
    function getCameraModal() {
        if (!cameraModal && window.bootstrap && bootstrap.Modal) {
            cameraModal = new bootstrap.Modal(document.getElementById('cameraModal'));
        }
        return cameraModal;
    }

    let documentComparisonModal = null;
    function getDocumentComparisonModal() {
        if (!documentComparisonModal && window.bootstrap && bootstrap.Modal) {
            documentComparisonModal = new bootstrap.Modal(document.getElementById('documentComparisonModal'));
        }
        return documentComparisonModal;
    }

    function setComparisonImage(image, placeholder, url, emptyMessage) {
        if (url) {
            image.src = url;
            image.style.display = 'block';
            placeholder.style.display = 'none';
            return;
        }

        image.removeAttribute('src');
        image.style.display = 'none';
        placeholder.textContent = emptyMessage;
        placeholder.style.display = 'block';
    }

    const comparisonCanvas = document.getElementById('comparisonNewCanvas');
    const comparisonControls = document.getElementById('comparisonControls');
    const zoomRange = document.getElementById('zoomRange');
    const brightnessRange = document.getElementById('brightnessRange');
    const contrastRange = document.getElementById('contrastRange');
    const colorRange = document.getElementById('colorRange');
    const acceptedDocumentFiles = new Map();
    const inlinePreviewUrls = new Map();
    const editorState = {
        input: null,
        file: null,
        image: null,
        zoom: 1,
        brightness: 100,
        contrast: 100,
        color: 100,
        offsetX: 0,
        offsetY: 0,
        dragging: false,
        pointerId: null,
        lastX: 0,
        lastY: 0
    };

    function setInputFile(input, file) {
        if (!file) {
            input.value = '';
            return;
        }
        const transfer = new DataTransfer();
        transfer.items.add(file);
        input.files = transfer.files;
    }

    function updateFileName(input, file) {
        const filenameText = document.getElementById(input.dataset.filename);
        if (!filenameText) return;

        if (file) {
            filenameText.innerHTML = '<i class="fa fa-check text-success me-1"></i> ' + file.name;
            filenameText.classList.remove('text-muted');
            filenameText.classList.add('text-primary');
        } else {
            filenameText.textContent = input.dataset.emptyText || 'Biarkan kosong jika tidak diubah.';
            filenameText.classList.remove('text-primary');
            filenameText.classList.add('text-muted');
        }
    }

    function updateInlinePreview(input, file) {
        const image = document.getElementById(input.dataset.inlinePreview || '');
        if (!image) return;
        const container = image.closest('.selected-document-preview');
        const previousUrl = inlinePreviewUrls.get(input.id);
        if (previousUrl) {
            URL.revokeObjectURL(previousUrl);
            inlinePreviewUrls.delete(input.id);
        }

        if (file && file.type.startsWith('image/')) {
            const previewUrl = URL.createObjectURL(file);
            inlinePreviewUrls.set(input.id, previewUrl);
            image.src = previewUrl;
            container.style.display = 'block';
            return;
        }

        image.removeAttribute('src');
        container.style.display = 'none';
    }

    function editorScale() {
        if (!editorState.image) return 1;
        // Skala awal menggunakan contain agar seluruh foto terlihat tanpa crop.
        // Pengguna dapat memperbesar dan menggeser foto untuk menentukan crop.
        const baseScale = Math.min(
            comparisonCanvas.width / editorState.image.naturalWidth,
            comparisonCanvas.height / editorState.image.naturalHeight
        );
        return baseScale * editorState.zoom;
    }

    function clampEditorOffsets() {
        if (!editorState.image) return;
        const scale = editorScale();
        const maxX = Math.max(0, (editorState.image.naturalWidth * scale - comparisonCanvas.width) / 2);
        const maxY = Math.max(0, (editorState.image.naturalHeight * scale - comparisonCanvas.height) / 2);
        editorState.offsetX = Math.max(-maxX, Math.min(maxX, editorState.offsetX));
        editorState.offsetY = Math.max(-maxY, Math.min(maxY, editorState.offsetY));
    }

    function drawEditor() {
        if (!editorState.image) return;
        clampEditorOffsets();
        const context = comparisonCanvas.getContext('2d');
        const scale = editorScale();
        const drawWidth = editorState.image.naturalWidth * scale;
        const drawHeight = editorState.image.naturalHeight * scale;
        const drawX = (comparisonCanvas.width - drawWidth) / 2 + editorState.offsetX;
        const drawY = (comparisonCanvas.height - drawHeight) / 2 + editorState.offsetY;

        context.clearRect(0, 0, comparisonCanvas.width, comparisonCanvas.height);
        context.fillStyle = '#ffffff';
        context.fillRect(0, 0, comparisonCanvas.width, comparisonCanvas.height);
        context.save();
        context.filter = `brightness(${editorState.brightness}%) contrast(${editorState.contrast}%) saturate(${editorState.color}%)`;
        context.drawImage(editorState.image, drawX, drawY, drawWidth, drawHeight);
        context.restore();
    }

    function syncEditorControls() {
        zoomRange.value = Math.round(editorState.zoom * 100);
        brightnessRange.value = editorState.brightness;
        contrastRange.value = editorState.contrast;
        colorRange.value = editorState.color;
        document.getElementById('zoomValue').textContent = Math.round(editorState.zoom * 100) + '%';
        document.getElementById('brightnessValue').textContent = editorState.brightness + '%';
        document.getElementById('contrastValue').textContent = editorState.contrast + '%';
        document.getElementById('colorValue').textContent = editorState.color + '%';
    }

    function resetEditor() {
        editorState.zoom = 1;
        editorState.brightness = 100;
        editorState.contrast = 100;
        editorState.color = 100;
        editorState.offsetX = 0;
        editorState.offsetY = 0;
        syncEditorControls();
        drawEditor();
    }

    function setEditorZoom(percent) {
        editorState.zoom = Math.max(1, Math.min(5, Number(percent) / 100));
        syncEditorControls();
        drawEditor();
    }

    function showComparisonDialog(modal) {
        const cameraElement = document.getElementById('cameraModal');
        if (cameraElement.classList.contains('show')) {
            cameraElement.addEventListener('hidden.bs.modal', () => modal.show(), { once: true });
        } else {
            modal.show();
        }
    }

    function showComparisonModal(input, file) {
        const modal = getDocumentComparisonModal();
        if (!modal) {
            alert('Komponen preview belum siap. Muat ulang halaman lalu coba lagi.');
            return;
        }

        const label = input.dataset.label || 'Foto';
        const oldImage = document.getElementById('comparisonOldImage');
        const oldPlaceholder = document.getElementById('comparisonOldPlaceholder');
        const newPlaceholder = document.getElementById('comparisonNewPlaceholder');
        const cancelButton = document.getElementById('cancelImageEditBtn');
        const closeButton = document.getElementById('closeDocumentPreviewBtn');
        const saveButton = document.getElementById('saveImageEditBtn');
        document.getElementById('documentComparisonModalLabel').textContent = 'Perbandingan ' + label;

        if (input.dataset.currentUrl && input.dataset.currentIsImage === '1') {
            setComparisonImage(oldImage, oldPlaceholder, input.dataset.currentUrl, 'Belum ada foto awal.');
        } else {
            const oldMessage = input.dataset.currentUrl
                ? 'Berkas awal berupa PDF dan tidak dapat ditampilkan sebagai gambar.'
                : 'Belum ada foto awal.';
            setComparisonImage(oldImage, oldPlaceholder, '', oldMessage);
        }

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = () => {
                const image = new Image();
                image.onload = () => {
                    editorState.input = input;
                    editorState.file = file;
                    editorState.image = image;
                    comparisonCanvas.width = input.id === 'anggota_foto' ? 600 : 1200;
                    comparisonCanvas.height = input.id === 'anggota_foto' ? 800 : 900;
                    comparisonCanvas.style.display = 'block';
                    newPlaceholder.style.display = 'none';
                    comparisonControls.style.display = 'block';
                    cancelButton.style.display = 'inline-block';
                    saveButton.style.display = 'inline-block';
                    closeButton.style.display = 'none';
                    resetEditor();
                    showComparisonDialog(modal);
                };
                image.onerror = () => {
                    const previousFile = acceptedDocumentFiles.get(input.id) || null;
                    setInputFile(input, previousFile);
                    updateFileName(input, previousFile);
                    updateInlinePreview(input, previousFile);
                    alert('Foto baru tidak dapat dibaca. Silakan pilih file gambar lain.');
                };
                image.src = reader.result;
            };
            reader.onerror = () => {
                const previousFile = acceptedDocumentFiles.get(input.id) || null;
                setInputFile(input, previousFile);
                updateFileName(input, previousFile);
                updateInlinePreview(input, previousFile);
                alert('File foto gagal dibaca. Silakan pilih file lain.');
            };
            reader.readAsDataURL(file);
        } else {
            editorState.input = null;
            editorState.file = null;
            editorState.image = null;
            comparisonCanvas.style.display = 'none';
            comparisonControls.style.display = 'none';
            newPlaceholder.textContent = 'Berkas baru berupa PDF dan tidak dapat diedit sebagai gambar.';
            newPlaceholder.style.display = 'block';
            cancelButton.style.display = 'none';
            saveButton.style.display = 'none';
            closeButton.style.display = 'inline-block';
            acceptedDocumentFiles.set(input.id, file);
            updateInlinePreview(input, file);
            showComparisonDialog(modal);
        }
    }

    function updateSelectedDocument(input, file, showPreview = true) {
        updateFileName(input, file);
        if (showPreview) showComparisonModal(input, file);
    }

    zoomRange.addEventListener('input', function () {
        setEditorZoom(this.value);
    });
    document.getElementById('zoomOutBtn').addEventListener('click', () => {
        setEditorZoom(editorState.zoom * 100 - 10);
    });
    document.getElementById('zoomInBtn').addEventListener('click', () => {
        setEditorZoom(editorState.zoom * 100 + 10);
    });
    brightnessRange.addEventListener('input', function () {
        editorState.brightness = Number(this.value);
        syncEditorControls();
        drawEditor();
    });
    contrastRange.addEventListener('input', function () {
        editorState.contrast = Number(this.value);
        syncEditorControls();
        drawEditor();
    });
    colorRange.addEventListener('input', function () {
        editorState.color = Number(this.value);
        syncEditorControls();
        drawEditor();
    });
    document.getElementById('resetEditorBtn').addEventListener('click', resetEditor);

    comparisonCanvas.addEventListener('pointerdown', function (event) {
        if (!editorState.image) return;
        editorState.dragging = true;
        editorState.pointerId = event.pointerId;
        editorState.lastX = event.clientX;
        editorState.lastY = event.clientY;
        comparisonCanvas.classList.add('is-dragging');
        comparisonCanvas.setPointerCapture(event.pointerId);
    });
    comparisonCanvas.addEventListener('pointermove', function (event) {
        if (!editorState.dragging || event.pointerId !== editorState.pointerId) return;
        const rect = comparisonCanvas.getBoundingClientRect();
        editorState.offsetX += (event.clientX - editorState.lastX) * (comparisonCanvas.width / rect.width);
        editorState.offsetY += (event.clientY - editorState.lastY) * (comparisonCanvas.height / rect.height);
        editorState.lastX = event.clientX;
        editorState.lastY = event.clientY;
        drawEditor();
    });
    function stopEditorDrag(event) {
        if (!editorState.dragging || event.pointerId !== editorState.pointerId) return;
        editorState.dragging = false;
        comparisonCanvas.classList.remove('is-dragging');
        if (comparisonCanvas.hasPointerCapture(event.pointerId)) {
            comparisonCanvas.releasePointerCapture(event.pointerId);
        }
        editorState.pointerId = null;
    }
    comparisonCanvas.addEventListener('pointerup', stopEditorDrag);
    comparisonCanvas.addEventListener('pointercancel', stopEditorDrag);

    document.getElementById('cancelImageEditBtn').addEventListener('click', function () {
        if (!editorState.input) return;
        const previousFile = acceptedDocumentFiles.get(editorState.input.id) || null;
        setInputFile(editorState.input, previousFile);
        updateFileName(editorState.input, previousFile);
        updateInlinePreview(editorState.input, previousFile);
        getDocumentComparisonModal().hide();
    });

    document.getElementById('saveImageEditBtn').addEventListener('click', function () {
        if (!editorState.input || !editorState.image) return;
        const button = this;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span> Menyimpan...';

        comparisonCanvas.toBlob(blob => {
            if (!blob) {
                button.disabled = false;
                button.innerHTML = '<i class="fa fa-save me-1"></i> Simpan Hasil Edit';
                alert('Hasil edit foto gagal dibuat. Silakan coba lagi.');
                return;
            }

            const baseName = editorState.file.name.replace(/\.[^.]+$/, '');
            const editedFile = new File([blob], baseName + '-edit.jpg', { type: 'image/jpeg' });
            setInputFile(editorState.input, editedFile);
            acceptedDocumentFiles.set(editorState.input.id, editedFile);
            updateFileName(editorState.input, editedFile);
            updateInlinePreview(editorState.input, editedFile);
            button.disabled = false;
            button.innerHTML = '<i class="fa fa-save me-1"></i> Simpan Hasil Edit';
            getDocumentComparisonModal().hide();
        }, 'image/jpeg', 0.9);
    });

    // Preview File Upload & Deteksi Nama File
    document.querySelectorAll('.document-input').forEach(function (input) {
        input.addEventListener('change', function () {
            const file = this.files && this.files[0];
            
            if (file) {
                updateSelectedDocument(this, file);
            } else {
                // Reset text jika batal pilih
                updateFileName(this, null);
                updateInlinePreview(this, null);
            }
        });
    });

    // Buka Kamera (Trigger)
    document.querySelectorAll('.open-document-camera').forEach(function (button) {
        button.addEventListener('click', async function () {
            cameraTargetInput = document.getElementById(this.dataset.input);
            await openCamera();
        });
    });

    // Fungsi Buka Kamera di Modal
    async function openCamera() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert('Browser ini tidak mendukung akses kamera. Pastikan menggunakan HTTPS atau localhost.');
            return;
        }
        try {
            const modal = getCameraModal();
            if (!modal) {
                alert('Komponen modal belum siap. Muat ulang halaman lalu coba lagi.');
                return;
            }
            const cameraGuide = document.querySelector('.camera-guide');
            const isLandscape = cameraTargetInput && cameraTargetInput.id !== 'anggota_foto';
            cameraGuide.classList.toggle('landscape', isLandscape);
            stopCamera();
            // Tampilkan modal lebih dulu agar browser tidak menganggap permintaan kamera
            // sebagai aksi yang tidak lagi berasal dari klik pengguna.
            modal.show();
            await new Promise(resolve => setTimeout(resolve, 50));

            // Untuk laptop, minta webcam umum terlebih dahulu. Constraint
            // facingMode=environment sering membuat webcam desktop gagal dibuka.
            try {
                cameraStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
            } catch (firstError) {
                cameraStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: { ideal: 'environment' } }, audio: false
                });
            }
            cameraVideo.srcObject = cameraStream;
            await new Promise((resolve, reject) => {
                const timeout = setTimeout(() => reject(new Error('Video timeout')), 5000);
                cameraVideo.onloadedmetadata = () => {
                    clearTimeout(timeout);
                    resolve();
                };
                cameraVideo.play().catch(reject);
            });
        } catch (e) {
            if (cameraModal) cameraModal.hide();
            stopCamera();
            const reason = e && e.name === 'NotAllowedError'
                ? 'Akses kamera ditolak. Izinkan kamera untuk situs ini lalu coba lagi.'
                : e && e.name === 'NotFoundError'
                    ? 'Webcam laptop tidak ditemukan. Pastikan kamera terpasang dan tidak dinonaktifkan.'
                    : e && e.name === 'NotReadableError'
                        ? 'Webcam sedang digunakan aplikasi lain. Tutup Zoom/Teams/Camera lalu coba lagi.'
                : 'Kamera tidak dapat dibuka. Pastikan kamera tidak sedang dipakai aplikasi lain.';
            alert(reason);
        }
    }

    // Fungsi Tutup Kamera (Reset)
    function stopCamera() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(track => track.stop());
            cameraStream = null;
            cameraVideo.srcObject = null;
        }
        cameraVideo.removeAttribute('src');
    }

    // Event saat modal ditutup, kamera mati
    document.getElementById('cameraModal').addEventListener('hidden.bs.modal', stopCamera);

    // Ambil Foto dari Video
    document.getElementById('takePhotoBtn').addEventListener('click', function () {
        if (!cameraTargetInput || !cameraStream || !cameraVideo.videoWidth || !cameraVideo.videoHeight) {
            alert('Kamera belum siap. Tunggu sampai pratinjau tampil, lalu coba lagi.');
            return;
        }
        const sourceWidth = cameraVideo.videoWidth;
        const sourceHeight = cameraVideo.videoHeight;
        const canvas = document.createElement('canvas');
        const maxLongEdge = 1600;
        const cameraScale = Math.min(1, maxLongEdge / Math.max(sourceWidth, sourceHeight));
        canvas.width = Math.round(sourceWidth * cameraScale);
        canvas.height = Math.round(sourceHeight * cameraScale);
        
        // Simpan seluruh frame kamera tanpa crop. Penentuan crop dilakukan
        // pengguna sendiri di modal editor melalui zoom dan geser.
        canvas.getContext('2d').drawImage(
            cameraVideo, 0, 0, sourceWidth, sourceHeight,
            0, 0, canvas.width, canvas.height
        );
        
        // Convert canvas ke File JPEG
        canvas.toBlob(function (blob) {
            if (!blob) {
                alert('Foto gagal diambil. Silakan coba lagi.');
                return;
            }
            const fileName = cameraTargetInput.name + '-kamera.jpg';
            const file = new File([blob], fileName, { type: 'image/jpeg' });
            
            // Set file ke input dengan DataTransfer
            const transfer = new DataTransfer();
            transfer.items.add(file);
            cameraTargetInput.files = transfer.files;
            updateSelectedDocument(cameraTargetInput, file, false);

            // Modal perbandingan dibuka setelah modal kamera selesai ditutup
            // agar backdrop dan fokus Bootstrap tidak saling bertabrakan.
            const selectedInput = cameraTargetInput;
            const cameraElement = document.getElementById('cameraModal');
            cameraElement.addEventListener('hidden.bs.modal', () => {
                showComparisonModal(selectedInput, file);
            }, { once: true });
            if (cameraModal) cameraModal.hide();
        }, 'image/jpeg', 0.9);
    });
    const wilayahBase = '<?= base_url('anggota/wilayah') ?>';
    const provinsiSelect = document.getElementById('wilayahProvinsi');
    const kabupatenSelect = document.getElementById('wilayahKabupaten');
    const kecamatanSelect = document.getElementById('wilayahKecamatan');
    const kelurahanSelect = document.getElementById('wilayahKelurahan');

    function setOptions(select, rows, valueKey, labelKey, placeholder) {
        select.innerHTML = `<option value="">${placeholder}</option>`;
        rows.forEach(row => {
            const option = new Option(row[labelKey], row[valueKey]);
            select.add(option);
        });
        select.disabled = rows.length === 0;
    }
    async function loadWilayah(url, select, valueKey, labelKey, selected = '') {
        const response = await fetch(url);
        const rows = await response.json();
        setOptions(select, rows, valueKey, labelKey, select.options[0]?.text || '- Pilih -');
        if (selected) select.value = selected;
        return rows;
    }
    (async function initWilayah() {
        const selectedProvinsi = provinsiSelect.dataset.selected;
        const selectedKabupaten = kabupatenSelect.dataset.selected;
        const selectedKecamatan = kecamatanSelect.dataset.selected;
        const selectedKelurahan = kelurahanSelect.dataset.selected;
        await loadWilayah(`${wilayahBase}/provinsi`, provinsiSelect, 'provinsi', 'provinsi', selectedProvinsi);
        if (provinsiSelect.value) {
            await loadWilayah(`${wilayahBase}/kabupaten/${encodeURIComponent(provinsiSelect.value)}`, kabupatenSelect, 'kabupaten_kota', 'kabupaten_kota', selectedKabupaten);
        }
        if (kabupatenSelect.value) {
            await loadWilayah(`${wilayahBase}/kecamatan/${encodeURIComponent(kabupatenSelect.value)}`, kecamatanSelect, 'kecamatan_id', 'kecamatan_nama', selectedKecamatan);
        }
        if (kecamatanSelect.value) {
            await loadWilayah(`${wilayahBase}/kelurahan/${encodeURIComponent(kecamatanSelect.value)}`, kelurahanSelect, 'id', 'kelurahan_nama', selectedKelurahan);
        }
    })().catch(console.error);
    provinsiSelect.addEventListener('change', async () => {
        setOptions(kabupatenSelect, [], 'kabupaten_kota', 'kabupaten_kota', '- Pilih Kabupaten/Kota -');
        setOptions(kecamatanSelect, [], 'kecamatan_id', 'kecamatan_nama', '- Pilih Kecamatan -');
        setOptions(kelurahanSelect, [], 'id', 'kelurahan_nama', '- Pilih Kelurahan/Desa -');
        if (provinsiSelect.value) await loadWilayah(`${wilayahBase}/kabupaten/${encodeURIComponent(provinsiSelect.value)}`, kabupatenSelect, 'kabupaten_kota', 'kabupaten_kota');
    });
    kabupatenSelect.addEventListener('change', async () => {
        setOptions(kecamatanSelect, [], 'kecamatan_id', 'kecamatan_nama', '- Pilih Kecamatan -');
        setOptions(kelurahanSelect, [], 'id', 'kelurahan_nama', '- Pilih Kelurahan/Desa -');
        if (kabupatenSelect.value) await loadWilayah(`${wilayahBase}/kecamatan/${encodeURIComponent(kabupatenSelect.value)}`, kecamatanSelect, 'kecamatan_id', 'kecamatan_nama');
    });
    kecamatanSelect.addEventListener('change', async () => {
        setOptions(kelurahanSelect, [], 'id', 'kelurahan_nama', '- Pilih Kelurahan/Desa -');
        if (kecamatanSelect.value) await loadWilayah(`${wilayahBase}/kelurahan/${encodeURIComponent(kecamatanSelect.value)}`, kelurahanSelect, 'id', 'kelurahan_nama');
    });
</script>

<?php echo view('footer'); ?>
