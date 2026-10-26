<?php echo view('header'); ?>


<div class="card">
    <div class="card-header">
        Catat Transaksi


            <a class="btn btn-danger btn-sm float-end" href="<?= base_url('transaksi') ?>">Cancel</a>
   
    </div>
    <div class="card-body">
       
       <div class="container mt-4">
    <h4 class="mb-4">Pencatatan Transaksi</h4>
   <form action="<?= isset($transaksi) ? base_url('/transaksi/update/'.$transaksi['transaksi_id']) : base_url('/transaksi/store') ?>" method="post">
    <?= csrf_field() ?>


    <div class="row mb-3">
        <label class="col-sm-3 col-form-label">Virtual Account</label>
        <div class="col-sm-9">
        <select name="transaksi_va" class="form-select select2" data-placeholder="-- Pilih Anggota --">
            <option value="">-- Pilih Anggota --</option>
            <?php foreach ($anggota as $a): ?>
                <option value="<?= esc($a['va_owner_va']) ?>"
                    <?= (isset($transaksi['transaksi_va']) && $transaksi['transaksi_va'] == $a['va_owner_va']) ? 'selected' : '' ?>>
                    <?= esc($a['va_owner_va']) ?> -- <?= esc($a['anggota_nama']) ?> -- <?= esc($a['titpar_namatempat']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-sm-3 col-form-label">Nominal</label>
        <div class="col-sm-9">
            <input type="text" id="transaksi_nominal" class="form-control" name="transaksi_nominal" required>
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-sm-3 col-form-label">Tanggal</label>
        <div class="col-sm-9">
           <input type="date" id="transaksi_tanggal" name="transaksi_tanggal" class="form-control" required>
        </div>
    </div>

    <!-- Add more fields here -->

    <div class="text-end">
        <button type="submit" class="btn btn-primary"><?= isset($transaksi) ? 'Update' : 'Submit' ?></button>
    </div>
</form>

</div>


    </div>
</div>

<script>
  $(document).ready(function() {
    $('.select2').select2({
      theme: 'bootstrap4', // match Bootstrap 4/5 styles
      width: '100%',       // fix width issue
      allowClear: true
    });
  });
</script>

<script>
  new AutoNumeric('#transaksi_nominal', {
    digitGroupSeparator: ',',
    decimalCharacter: '.',
    decimalPlaces: 0,
    unformatOnSubmit: true
  });
</script>

<script>
  // Set default date to today
  const today = new Date().toISOString().split('T')[0];
  document.getElementById('transaksi_tanggal').value = today;
</script>

<?php echo view('footer'); ?>