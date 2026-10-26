<?= view('header'); ?>

<div class="card">
    <div class="card-header">
        Input Kendala
    </div>

    <div class="card-body">

        <form id="formKendala">

            <div class="mb-3">
                <label class="form-label">Nomor VA</label>
                <select name="kendala_va" id="vaSelect" class="form-select form-control" required>
                    <option value="">-- Pilih VA --</option>

                    <?php foreach ($listva as $va): ?>
                        <option value="<?= $va['va_owner_va']; ?>">
                            <?= $va['va_owner_va']; ?> - <?= $va['va_owner_nama']; ?> - <?= $va['titpar_namatempat']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Alasan Kendala</label>
                <textarea name="kendala_alasan" class="form-control" rows="3" required></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Potongan</label>
                <select name="kategori" class="form-select" required>
                    <option value="">-- Pilih --</option>
                    <option value="1">100%</option>
                    <option value="2">50%</option>
                    <option value="3">25%</option>
                    <option value="0">0%</option>
                </select>
            </div>
            <div class="mb-3">
    <label class="form-label">Tanggal Mulai</label>
    <input type="date" name="start_date" class="form-control" required>
</div>

<div class="mb-3">
    <label class="form-label">Tanggal Selesai</label>
    <input type="date" name="end_date" class="form-control" required>
</div>

            <button type="submit" class="btn btn-primary">Simpan</button>

        </form>

    </div>
</div>

<script>
$('#formKendala').on('submit', function(e) {
    e.preventDefault();

    $.ajax({
        url: "<?= site_url('kendalaadminsave') ?>",
        type: "POST",
        data: $(this).serialize(),
        dataType: "json",
        success: function(res) {
                alert(res.message || 'Berhasil');

                $('#formKendala')[0].reset();

                if (res.redirect) {
                    setTimeout(function() {
                        window.location.href = res.redirect;
                    }, 500); // delay 0.5 detik
                }
            },
        error: function() {
            alert('Server error');
        }
    });
});

$('#vaSelect').select2({
    placeholder: '-- Pilih VA --',
    width: '100%'
});
</script>

<?= view('footer'); ?>