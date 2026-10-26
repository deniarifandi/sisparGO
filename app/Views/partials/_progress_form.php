<div class="status-note d-flex align-items-start gap-2 mb-3">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="#1a4699" class="flex-shrink-0 mt-1">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
    </svg>
    Masukkan NIK yang digunakan saat mendaftar untuk memantau proses verifikasi dokumen Anda.
</div>

<form action="<?= base_url('register/progress') ?>" method="post">
    <?= csrf_field() ?>
    <div class="mb-3">
        <label class="form-label">Nomor NIK Terdaftar</label>
        <input type="text" class="form-control" name="anggota_noktp"
               placeholder="16 digit NIK" maxlength="16" inputmode="numeric" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Tanggal Lahir</label>
        <input type="date" class="form-control" name="anggota_tgllhr" required>
    </div>
    <button type="submit" class="btn btn-navy w-100">Cek Status</button>
</form>