<?php echo view('header'); ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>


<div class="card">
    <div class="card-header">
        Pencatatan Potensi

        <a class="btn btn-danger btn-sm float-end" href="<?= base_url('potensi') ?>">Cancel</a>
   
    </div>
    <div class="card-body">
       
       <div class="container mt-4">
    <h4 class="mb-4">Pencatatan Potesi</h4>
   <form action="<?= !empty($isEdit) ? base_url('/potensi/update/' . $data['va_owner_va']) : base_url('/potensi/store/' . ($data['va_owner_va'] ?? '')) ?>" method="post">
    <?= csrf_field() ?>
    <?php if (!empty($return_to)): ?>
        <input type="hidden" name="return_to" value="<?= esc($return_to) ?>">
    <?php endif; ?>
    
    <div class="row mb-3">
        <label class="col-sm-3 col-form-label">VA (Indeks)</label>
        <div class="col-sm-9">
            <input type="text" name="va_owner_va" class="form-control" 
                   value="<?= isset($data) ? esc($data['va_owner_va']) : '' ?>" readonly>
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-sm-3 col-form-label">Nama Anggota</label>
        <div class="col-sm-9">
            <input type="text" class="form-control " 
                   value="<?= isset($data) ? esc($data['anggota_id']." - ".$data['anggota_nama']) : '' ?>" readonly>
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-sm-3 col-form-label">Tempat Parkir</label>
        <div class="col-sm-9">
            <input type="text" class="form-control" 
                   value="<?= isset($data) ? esc($data['titpar_id']." - ".$data['titpar_namatempat']." - ".$data['titpar_lokasi']) : '' ?>" readonly>
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-sm-3 col-form-label">Berlaku Mulai</label>
        <div class="col-sm-9">
            <input type="date" name="berlaku_mulai" class="form-control"
                   value="<?= esc(old('berlaku_mulai', $data['berlaku_mulai'] ?? date('Y-m-d'))) ?>" required>
            <small class="text-muted">Perubahan hanya berlaku mulai tanggal ini; periode sebelumnya tetap memakai nilai lama.</small>
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-sm-3 col-form-label">Berlaku Sampai</label>
        <div class="col-sm-9">
            <input type="date" name="berlaku_sampai" class="form-control"
                   value="<?= esc(old('berlaku_sampai', $data['berlaku_sampai'] ?? '')) ?>">
            <small class="text-muted">Kosongkan jika berlaku tanpa tanggal akhir.</small>
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-sm-3 col-form-label">Senin</label>
    <div class="col-sm-9">
        <input type="text" name="senin" class="form-control autonumeric text-end"`r`n              value="<?= isset($data['senin']) ? $data['senin'] : 0 ?>" required>
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label">Selasa</label>
    <div class="col-sm-9">
        <input type="text" name="selasa" class="form-control autonumeric text-end"`r`n               value="<?= isset($data['selasa']) ? $data['selasa'] : 0 ?>" required>
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label">Rabu</label>
    <div class="col-sm-9">
        <input type="text" name="rabu" class="form-control autonumeric text-end"`r`n               value="<?= isset($data['rabu']) ? $data['rabu'] : 0 ?>" required>
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label">Kamis</label>
    <div class="col-sm-9">
        <input type="text" name="kamis" class="form-control autonumeric text-end"`r`n              value="<?= isset($data['kamis']) ? $data['kamis'] : 0 ?>" required>
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label">Jumat</label>
    <div class="col-sm-9">
        <input type="text" name="jumat" class="form-control autonumeric text-end"`r`n              value="<?= isset($data['jumat']) ? $data['jumat'] : 0 ?>" required>
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label">Sabtu</label>
    <div class="col-sm-9">
        <input type="text" name="sabtu" class="form-control autonumeric text-end"`r`n               value="<?= isset($data['sabtu']) ? $data['sabtu'] : 0 ?>" required>
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label">Minggu</label>
    <div class="col-sm-9">
        <input type="text" name="minggu" class="form-control autonumeric text-end"`r`n               value="<?= isset($data['minggu']) ? $data['minggu'] : 0 ?>" required>
    </div>
</div>

    <!-- Add more fields here -->

    <div class="text-end">
        <button type="submit" class="btn btn-primary"><?= isset($data) ? 'Update' : 'Submit' ?></button>
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
  document.querySelectorAll('.autonumeric').forEach(el => {
    new AutoNumeric(el, {
      digitGroupSeparator: ',',
      decimalCharacter: '.',
      decimalPlaces: 0,
      unformatOnSubmit: true
    });
  });
</script>

<?php echo view('footer'); ?>
