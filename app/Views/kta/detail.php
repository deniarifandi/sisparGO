<h2>Detail Pendaftaran</h2>

<p><b>Nama:</b> <?= $item->anggota_nama ?></p>
<p><b>No KTP:</b> <?= $item->anggota_noktp ?></p>
<p><b>Alamat:</b> <?= $item->anggota_alamat ?></p>
<p><b>Tanggal Lahir:</b> <?= $item->anggota_tgllhr ?></p>

<p><b>Foto Profil:</b><br>
<img src="<?= base_url() ?>uploads/anggota_foto/<?= $item->anggota_foto ?>" width="200">
</p>

<p><b>Foto KTP:</b><br>
<img src="<?= base_url() ?>uploads/foto_ktp/<?= $item->foto_ktp ?>" width="200">
</p>

<form method="post" action="/admin/kta/updateStatus/<?= $item->id ?>">
    <select name="status">
        <option value="0" <?= $item->anggota_status == '0' ? 'selected' : '' ?>>Pending</option>
        <option value="1" <?= $item->anggota_status == '1' ? 'selected' : '' ?>>Approved</option>
        <option value="2" <?= $item->anggota_status == '2' ? 'selected' : '' ?>>Rejected</option>
    </select>
    <button type="submit">Update Status</button>
</form>

<a href="/admin/kta">Kembali</a>
