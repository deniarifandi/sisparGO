<?php echo view('header'); ?>

<?php if (session()->getFlashdata('message')): ?>
    <div class="alert alert-danger">
        <?= session()->getFlashdata('message'); ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        VA Owner List 

            <a class="btn btn-danger btn-sm float-end" href="<?= base_url('va-owner') ?>">Cancel</a>
   
    </div>
    <div class="card-body">
       
        <?php if (isset($jatim)): ?>
            <div class="alert alert-info">
                <?= json_encode($jatim) ?>
            </div>
        <?php endif ?>


       <div class="container mt-4">
    <h4 class="mb-4">Virtual Account</h4>
   <form action="<?= isset($va_owner) ? base_url('/va-owner/update/'.$va_owner['va_owner_id']) : base_url('/va-owner/store') ?>" method="post">
    <?= csrf_field() ?>
    
    <input type="hidden" name="register_id" value="<?= esc($register_id ?? '') ?>">

   <div class="row mb-3">
    <label class="col-sm-3 col-form-label">VA (Indeks)</label>
    <div class="col-sm-9">
        <input type="text" name="va_owner_va" 
               class="form-control <?= isset($va_owner) ? 'bg-light text-muted' : '' ?>" 
               value="<?= old('va_owner_va', isset($va_owner) ? esc($va_owner['va_owner_va']) : '') ?>" 
               <?= isset($va_owner) ? 'readonly' : '' ?>>
    </div>
</div>

<?php if (isset($jatim['virtualAccountData']['virtualAccountName'])): ?>
<div class="row mb-3">
    <label class="col-sm-3 col-form-label"></label>
    <div class="col-sm-9 text-begin" style="font-size: 12px; color: #6c757d;">
        Nama Terdaftar di Bank Jatim: 
        <strong><?= $jatim['virtualAccountData']['virtualAccountName']; ?></strong>
    </div>
</div>
<?php endif; ?>


  <div class="row mb-3">
    <label class="col-sm-3 col-form-label">Anggota Jukir</label>
    <div class="col-sm-9">
        <select class="form-select select2" data-placeholder="-- Pilih Anggota --"
                name="titpargrup_id" <?= isset($va_owner) ? '' : '' ?> required>
            <option value="">-- Pilih Anggota --</option>
            <?php foreach ($anggota as $a): ?>
             <option value="<?= $a['titpargrup_id'] ?>"
                        <?=
                            old('titpargrup_id',
                                isset($va_owner)
                                    ? $va_owner['va_owner_titpargrupid']
                                    : ($selected_titpargrup_id ?? '')
                            ) == $a['titpargrup_id']
                            ? 'selected' : ''
                        ?>
                    
                    <?= isset($va_owner) && $va_owner['va_owner_titpargrupid'] == $a['titpargrup_id'] ? 'selected' : '' ?>>
                    <?= esc($a['anggota_nama']) ?> (<?= esc($a['anggota_id']) ?>) -- <?= esc($a['titpar_namatempat']) ?> (<?= esc($a['titpar_id']) ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <?php if (isset($va_owner)): ?>
            <!-- Hidden supaya value tetap terkirim -->
           
        <?php endif; ?>
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label">HP</label>
    <div class="col-sm-9">
       <input type="text" name="va_owner_hp" class="form-control"
       pattern="^(0|62)[0-9]+$"
       inputmode="numeric"
       title="Nomor HP harus dimulai dengan 0 atau 62 dan hanya angka"
       value="<?= old('va_owner_hp', esc($va_owner_hp ?? '')) ?>" required>
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label">BERITA 5</label>
    <div class="col-sm-9">
        <input type="text" name="va_owner_email" class="form-control" 
                  value="<?= old('va_owner_email', isset($va_owner) ? esc($va_owner['va_owner_email']) : 'DISHUB KOTA MALANG') ?>" required
               required>
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label">Tagihan</label>
    <div class="col-sm-9">
        <input type="text" name="va_owner_tagihan" class="form-control text-end autonumeric"
               value="<?= old('va_owner_tagihan', isset($jatim['virtualAccountData']['totalAmount']['value']) ? $jatim['virtualAccountData']['totalAmount']['value'] : '') ?>"

               required>
    </div>
</div>

<?php
$expiredDate = isset($jatim['virtualAccountData']['expiredDate'])
    ? date('d M Y', strtotime($jatim['virtualAccountData']['expiredDate']))
    : date('d M Y');
?>

<div class="row mb-3">
  <label class="col-sm-3 col-form-label">Tanggal Expired</label>
  <div class="col-sm-9">
    <input type="text" 
           name="va_owner_expired" 
           class="form-control datepicker" 
           value="<?= old('va_owner_expired', $expiredDate) ?>" 
           style="padding-left: 10px;"
           required>
  </div>
</div>

    <!-- Add more fields here -->

    <div class="text-end">
        <button type="submit" class="btn btn-primary"><?= isset($va_owner) ? 'Update' : 'Submit' ?></button>
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
$(function() {
  $('.datepicker').datepicker({
    format: "dd M yyyy",
    autoclose: true,
    todayHighlight: true
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