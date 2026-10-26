<?php echo view('header'); ?>

<div class="card">
    <div class="card-header">
        Catat Titik Parkir Anggota
        <a class="btn btn-danger btn-sm float-end" href="<?= base_url('titpargrup') ?>">Cancel</a>
    </div>
    <div class="card-body">
        <div class="container mt-4">
            <h4 class="mb-4">Pencatatan Titik Parkir Anggota</h4>

            <?php $action = isset($titpargrup['titpargrup_id']) ? base_url('/titpargrup/update/'.$titpargrup['titpargrup_id']) : base_url('/titpargrup/store'); ?>

            <form action="<?= $action ?>" method="post">
                <?= csrf_field() ?>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Nama Anggota</label>
                    <div class="col-sm-9">
                        <select id="titpargrup_anggotaid" name="titpargrup_anggotaid" class="form-select select2" required>
                            <option value="">-- Pilih Anggota --</option>
                            <?php foreach ($anggotaList as $a): ?>
                                <option value="<?= $a['anggota_id']; ?>" <?= (isset($titpargrup['titpargrup_anggotaid']) && $titpargrup['titpargrup_anggotaid'] == $a['anggota_id']) ? 'selected' : '' ?>>
                                    <?= esc($a['anggota_nama']); ?> (<?= esc($a['anggota_id']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Nama Titik Parkir</label>
                    <div class="col-sm-9">
                        <select id="titpargrup_titparid" name="titpargrup_titparid" class="form-select select2" required>
                            <option value="">-- Pilih Titik Parkir --</option>
                            <?php foreach ($titparList as $t): ?>
                                <option value="<?= $t['titpar_id']; ?>" <?= (isset($titpargrup['titpargrup_titparid']) && $titpargrup['titpargrup_titparid'] == $t['titpar_id']) ? 'selected' : '' ?>>
                                    <?= esc($t['titpar_namatempat']); ?> (<?= esc($t['titpar_id']); ?>)
                                    <?php if (!empty($t['titpar_lokasi'])): ?>
                                        (<?= esc($t['titpar_lokasi']); ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Status Anggota</label>
                    <div class="col-sm-9">
                        <select id="titpargrup_staanggota" name="titpargrup_staanggota" class="form-select select2" required>
                            <option value="">-- Pilih Status Anggota --</option>
                            <option value="KOORDINATOR" <?= (isset($titpargrup['titpargrup_staanggota']) && $titpargrup['titpargrup_staanggota'] === 'KOORDINATOR') ? 'selected' : '' ?>>
                                KOORDINATOR
                            </option>
                            <option value="ANGGOTA" <?= (isset($titpargrup['titpargrup_staanggota']) && $titpargrup['titpargrup_staanggota'] === 'ANGGOTA') ? 'selected' : '' ?>>
                                ANGGOTA
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Input Nama Shift (Diberi ID row_namashift agar bisa disembunyikan/ditampilkan) -->
                <div class="row mb-3" id="row_namashift" style="display: none;">
    <label class="col-sm-3 col-form-label">Nama Shift / Koordinator</label>
    <div class="col-sm-9">
        <select id="titpargrup_namashift"
                name="titpargrup_namashift"
                class="form-select select2">

            <option value="">-- Pilih Koordinator --</option>

            <?php foreach ($anggotaList as $a): ?>
                <option
                    value="<?= esc($a['anggota_id']); ?>"
                    <?= (
                        isset($titpargrup['titpargrup_namashift']) &&
                        $titpargrup['titpargrup_namashift'] == $a['anggota_id']
                    ) ? 'selected' : '' ?>
                >
                    <?= esc($a['anggota_nama']); ?>
                    (<?= esc($a['anggota_id']); ?>)
                </option>
            <?php endforeach; ?>

        </select>
    </div>
</div>

                <!-- Input Shift Hari (Menggunakan Bitwise Encoding) -->
                <!-- Shift Hari & Jam -->
<div id="row_shift" style="display: none;">

    <!-- Input Shift Hari -->
    <div class="row mb-3">
        <label class="col-sm-3 col-form-label">Shift Hari</label>
        <div class="col-sm-9">
            <?php 
                $hariList = [
                    1  => 'Senin',
                    2  => 'Selasa',
                    4  => 'Rabu',
                    8  => 'Kamis',
                    16 => 'Jumat',
                    32 => 'Sabtu',
                    64 => 'Minggu'
                ];

                $shiftHariDB = isset($titpargrup['titpargrup_shifthari'])
                    ? (int)$titpargrup['titpargrup_shifthari']
                    : 0;
            ?>

            <div class="d-flex flex-wrap gap-3 pt-2">
                <?php foreach ($hariList as $bitValue => $label): ?>
                    <div class="form-check me-3">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="titpargrup_shifthari[]"
                            id="hari_<?= $bitValue ?>"
                            value="<?= $bitValue ?>"
                            <?= ($shiftHariDB & $bitValue) ? 'checked' : '' ?>
                        >

                        <label class="form-check-label" for="hari_<?= $bitValue ?>">
                            <?= $label ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Input Jam Mulai & Jam Selesai -->
    <div class="row mb-3">
        <label class="col-sm-3 col-form-label">Jam Shift</label>

        <div class="col-sm-9">
            <div class="row">

                <div class="col-md-6 mb-2 mb-md-0">
                    <div class="input-group">
                        <span class="input-group-text">Mulai</span>

                        <input
                            type="time"
                            id="titpargrup_shiftjammulai"
                            name="titpargrup_shiftjammulai"
                            class="form-control"
                            value="<?= isset($titpargrup['titpargrup_shiftjammulai']) ? esc($titpargrup['titpargrup_shiftjammulai']) : '' ?>"
                        >
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text">Selesai</span>

                        <input
                            type="time"
                            id="titpargrup_shiftjamselesai"
                            name="titpargrup_shiftjamselesai"
                            class="form-control"
                            value="<?= isset($titpargrup['titpargrup_shiftjamselesai']) ? esc($titpargrup['titpargrup_shiftjamselesai']) : '' ?>"
                        >
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <?= isset($titpargrup) ? 'Update' : 'Submit' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {

    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%',
        allowClear: true
    });

    function checkStatusAnggota() {

        var status = $('#titpargrup_staanggota').val();

        // Jika ANGGOTA
        if (status === 'ANGGOTA') {

            // Tampilkan pilihan koordinator
            $('#row_namashift').slideDown();

            // Sembunyikan hari & jam
            $('#row_shift').slideUp();

        }

        // Jika KOORDINATOR
        else if (status === 'KOORDINATOR') {

            // Sembunyikan pilihan koordinator
            $('#row_namashift').slideUp();

            // Kosongkan koordinator
            $('#titpargrup_namashift')
                .val('')
                .trigger('change');

            // Tampilkan hari & jam
            $('#row_shift').slideDown();

        }

        // Jika belum memilih status
        else {

            $('#row_namashift').slideUp();
            $('#row_shift').slideUp();

            $('#titpargrup_namashift')
                .val('')
                .trigger('change');
        }
    }

    // Jalankan saat halaman pertama kali dibuka
    // Penting untuk mode Edit
    checkStatusAnggota();

    // Jalankan saat status berubah
    $('#titpargrup_staanggota').on('change', function() {
        checkStatusAnggota();
    });

});
</script>

<?php echo view('footer'); ?>