<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pendaftaran & Cek Status</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; padding: 50px; }
        .container { max-width: 500px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        
        /* Tab Styling */
        .tabs { display: flex; border-bottom: 2px solid #ddd; margin-bottom: 20px; }
        .tab-link { flex: 1; padding: 10px; text-align: center; cursor: pointer; background: #eee; border: none; outline: none; transition: 0.3s; }
        .tab-link.active { background: #007bff; color: white; font-weight: bold; }
        
        /* Content Visibility */
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        /* Form Styling */
        form input, form textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { opacity: 0.9; }
        label { font-size: 14px; color: #666; font-weight: bold; display: block; margin-top: 10px; }
    </style>
</head>
<body>

<div class="container">

    <?php if(session()->getFlashdata('errors')): ?>
    <div style="color:red; margin-bottom:15px;">
        <ul>
        <?php foreach(session()->getFlashdata('errors') as $err): ?>
            <li><?= esc($err) ?></li>
        <?php endforeach ?>
        </ul>
    </div>
<?php endif; ?>
    <h2>Pendaftaran Titik Parkir Baru</h2>
    
    <div class="tabs">
        <button class="tab-link active" onclick="openTab(event, 'daftar')">Pendaftaran</button>
        <button class="tab-link" onclick="openTab(event, 'login')">Cek Progress</button>
    </div>

    <?php if(session()->getFlashdata('success')): ?>
        <div style="color: green; margin-bottom: 15px;"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if(session()->getFlashdata('error')): ?>
        <div style="color: red; margin-bottom: 15px;"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div id="daftar" class="tab-content active">
        <form action="<?= base_url('register/save') ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="text" name="anggota_nama" placeholder="Nama Lengkap" required>
            <input type="text" name="anggota_noktp" placeholder="NIK (16 Digit)" maxlength="16" required>
            <input type="text" name="register_va" placeholder="Virtual Account (Optional)">
            
            <label>Alamat Lengkap</label>
            <textarea name="anggota_alamat" placeholder="Contoh: Jl. Merdeka No. 1" rows="3" required></textarea>

            <label>Tanggal Lahir</label>
            <input type="date" name="anggota_tgllhr" required>

            <label>Foto Selfie</label>
            <input type="file" name="anggota_foto" accept="image/*" required>

            <label>Foto KTP</label>
            <input type="file" name="foto_ktp" accept="image/*" required>

            <button type="submit">Kirim Pendaftaran</button>
        </form>
    </div>

    <div id="login" class="tab-content">
        <p style="font-size: 14px; color: #666;">Masukkan NIK Anda untuk melihat status verifikasi pendaftaran.</p>
        <form action="<?= base_url('register/login') ?>" method="post">
            <?= csrf_field() ?>
            <input type="text" name="anggota_noktp" placeholder="Masukkan NIK 16 Digit" maxlength="16" required>
            <button type="submit" style="background: #007bff;">Cek Status Sekarang</button>
        </form>
    </div>
</div>

<script>
    function openTab(evt, tabName) {
        var i, tabcontent, tablinks;
        
        // Hide all tab content
        tabcontent = document.getElementsByClassName("tab-content");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].classList.remove("active");
        }

        // Remove active class from all buttons
        tablinks = document.getElementsByClassName("tab-link");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].classList.remove("active");
        }

        // Show current tab and add active class to button
        document.getElementById(tabName).classList.add("active");
        evt.currentTarget.classList.add("active");
    }
</script>

</body>
</html>