<!-- tiket/pesan.php -->
<?php echo view('header'); ?>

<?php
// ==== SAFE INPUTS FROM CONTROLLER ====
$info  = ($data[0] ?? null);
if (!$info) {
  // Avoid null property access if controller didn't send data
  $info            = (object)[
    'sisa_saldo'   => 0,
    'va_owner_va'  => '',
    'va_owner_nama'=> ''
  ];
}

$isEdit = isset($tiket) && is_array($tiket);
$original_total = isset($original_total) ? (int)$original_total : 0;

// ==== PRICE CONSTANTS (align with controller) ====
$PRICES = [
  'motor'     => 2000,
  'mobil'     => 3000,
  'truk'      => 5000,
  'motor_in'  => 3000,
  'mobil_in'  => 5000,
  'truk_in'   => 10000,
];

// ==== ITEMS ====
$items = [
  ["label" => "Motor", "name" => "tiket_motor", "awal" => "tiket_nomor_motor_awal", "akhir" => "tiket_nomor_motor_akhir", "harga" => $PRICES['motor']],
  ["label" => "Mobil", "name" => "tiket_mobil", "awal" => "tiket_nomor_mobil_awal", "akhir" => "tiket_nomor_mobil_akhir", "harga" => $PRICES['mobil']],
  ["label" => "Truk",  "name" => "tiket_truk",  "awal" => "tiket_nomor_truk_awal",  "akhir" => "tiket_nomor_truk_akhir",  "harga" => $PRICES['truk']],
];

$itemsIn = [
  ["label" => "Motor (IN)", "name" => "tiket_motor_in", "awal" => "tiket_nomor_motor_in_awal", "akhir" => "tiket_nomor_motor_in_akhir", "harga" => $PRICES['motor_in']],
  ["label" => "Mobil (IN)", "name" => "tiket_mobil_in", "awal" => "tiket_nomor_mobil_in_awal", "akhir" => "tiket_nomor_mobil_in_akhir", "harga" => $PRICES['mobil_in']],
  ["label" => "Truk (IN)",  "name" => "tiket_truk_in",  "awal" => "tiket_nomor_truk_in_awal",  "akhir" => "tiket_nomor_truk_in_akhir",  "harga" => $PRICES['truk_in']],
];
?>

<style>
  table.table-sm td, table.table-sm th {
    padding: 4px !important;
    vertical-align: middle;
  }
  .form-control.form-control-sm {
    padding-top: .15rem;
    padding-bottom: .15rem;
  }
  .fw-semibold { font-weight: 600; }
  .rounded-top-4 { border-top-left-radius: .8rem; border-top-right-radius: .8rem; }
  .rounded-4 { border-radius: .8rem; }
  .small .form-control { font-size: .85rem; }
</style>

<div class="card shadow-sm border-0 rounded-4 mb-3">
  <div class="card-header <?= $isEdit ? 'bg-warning' : 'bg-primary' ?> text-white d-flex justify-content-between align-items-center rounded-top-4 py-2">
    <h6 class="mb-0 fw-semibold d-flex align-items-center gap-2">
      <?= $isEdit ? 'Edit Pemesanan Tiket' : 'Pemesanan Tiket' ?>
      <?php if ($isEdit): ?>
        <span class="badge bg-dark px-2 py-1">EDIT MODE</span>
      <?php endif; ?>
    </h6>
    <a class="btn btn-light btn-sm" href="<?= base_url('tiket') ?>">Cancel</a>
  </div>

  <div class="p-3">
    <form action="<?= $isEdit ? base_url('tiket/update/' . ($tiket['tiket_id'] ?? '')) : base_url('tiket/store') ?>" method="post">
      <?= csrf_field() ?>

      <!-- Sisa Saldo -->
      <div class="alert alert-info py-1 px-2 mb-3 small rounded-2">
        <strong>Sisa Saldo:</strong>
        Rp <?= number_format((int)$info->sisa_saldo, 0, ',', '.') ?>
      </div>

      <!-- Edit-Only Info -->
      <?php if ($isEdit): 
        $maxTotalBaru = $original_total + (int)$info->sisa_saldo;
      ?>
      <div class="alert alert-warning small p-2 mb-3 border-warning">
        <div><strong>Informasi Edit:</strong></div>
        <div class="mt-1">Total Original: <strong>Rp <?= number_format($original_total, 0, ',', '.') ?></strong></div>
        <div>Sisa Saldo Saat Ini: <strong>Rp <?= number_format((int)$info->sisa_saldo, 0, ',', '.') ?></strong></div>
        <div class="mt-1 text-dark">
          Maksimal Total Baru:
          <strong>Rp <?= number_format($maxTotalBaru, 0, ',', '.') ?></strong>
        </div>
      </div>
      <?php endif; ?>

      <input type="hidden" id="sisa_saldo" value="<?= (int)$info->sisa_saldo ?>">
      <input type="hidden" id="original_total" value="<?= $original_total ?>">
      <input type="hidden" name="va_owner_va" value="<?= esc($info->va_owner_va) ?>">
      <?php if (!$isEdit): ?>
        <input type="hidden" name="pesan_token" value="<?= esc($pesan_token ?? '') ?>">
      <?php endif; ?>

      <!-- REGULER -->
      <h6 class="fw-bold mt-2 mb-2 <?= $isEdit ? 'text-warning' : 'text-primary' ?> small">1. Parkir Reguler</h6>

      <table class="table table-sm align-middle mb-2 small">
        <thead class="table-light">
          <tr>
            <th>Jenis</th>
            <th>Jumlah</th>
            <th>Harga</th>
            <th>Subtotal</th>
            <th>Nomor Awal</th>
            <th>Nomor Akhir</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $x): ?>
          <tr>
            <td><?= esc($x['label']) ?></td>
            <td>
              <input type="number" class="form-control form-control-sm tiket-jumlah"
                     name="<?= esc($x['name']) ?>"
                     value="<?= isset($tiket[$x['name']]) ? (int)$tiket[$x['name']] : 0 ?>"
                     min="0"
                     data-harga="<?= (int)$x['harga'] ?>">
            </td>
            <td>Rp <?= number_format((int)$x['harga'], 0, ',', '.') ?></td>
            <td class="subtotal fw-bold">Rp 0</td>
            <td><input type="text" class="form-control form-control-sm"
                       name="<?= esc($x['awal']) ?>"
                       value="<?= esc($tiket[$x['awal']] ?? '') ?>"></td>

            <td><input type="text" class="form-control form-control-sm"
                       name="<?= esc($x['akhir']) ?>"
                       value="<?= esc($tiket[$x['akhir']] ?? '') ?>"></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <!-- INSIDENTIL -->
      <h6 class="fw-bold mt-3 mb-2 <?= $isEdit ? 'text-warning' : 'text-primary' ?> small">2. Parkir Insidentil</h6>

      <table class="table table-sm align-middle small">
        <thead class="table-light">
          <tr>
            <th>Jenis</th>
            <th>Jumlah</th>
            <th>Harga</th>
            <th>Subtotal</th>
            <th>Nomor Awal</th>
            <th>Nomor Akhir</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($itemsIn as $x): ?>
          <tr>
            <td><?= esc($x['label']) ?></td>
            <td>
              <input type="number" class="form-control form-control-sm tiket-jumlah"
                     name="<?= esc($x['name']) ?>"
                     value="<?= isset($tiket[$x['name']]) ? (int)$tiket[$x['name']] : 0 ?>"
                     min="0"
                     data-harga="<?= (int)$x['harga'] ?>">
            </td>
            <td>Rp <?= number_format((int)$x['harga'], 0, ',', '.') ?></td>
            <td class="subtotal fw-bold">Rp 0</td>

            <td><input type="text" class="form-control form-control-sm"
                       name="<?= esc($x['awal']) ?>"
                       value="<?= esc($tiket[$x['awal']] ?? '') ?>"></td>

            <td><input type="text" class="form-control form-control-sm"
                       name="<?= esc($x['akhir']) ?>"
                       value="<?= esc($tiket[$x['akhir']] ?? '') ?>"></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <!-- TOTAL -->
      <div class="bg-light p-2 rounded-2 small border mb-3">
        <label class="fw-bold mb-1">Total Harga</label>
        <input type="text" id="total_harga" class="form-control form-control-sm fw-bold" readonly value="Rp 0">

        <?php if ($isEdit): ?>
        <small class="text-muted d-block mt-1">
          Validasi edit: Total baru tidak boleh melebihi Maksimal Total Baru.
        </small>
        <?php else: ?>
        <small class="text-muted d-block mt-1">
          Validasi baru: Total pesanan tidak boleh melebihi sisa saldo.
        </small>
        <?php endif; ?>
      </div>

      <div class="text-end mt-2">
        <button id="btnSubmit"
                type="submit"
                class="btn <?= $isEdit ? 'btn-warning' : 'btn-primary' ?> btn-sm px-3 py-1 fw-semibold">
          <?= $isEdit ? 'Update Tiket' : 'Pesan Tiket' ?>
        </button>
      </div>

    </form>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {

  const jumlahInputs = document.querySelectorAll(".tiket-jumlah");
  const totalField   = document.getElementById("total_harga");
  const btnSubmit    = document.getElementById("btnSubmit");

  const sisaSaldo     = parseInt(document.getElementById("sisa_saldo").value) || 0;
  const originalTotal = parseInt(document.getElementById("original_total").value) || 0;

  const isEdit       = <?= json_encode($isEdit) ?>;
  const maxTotalEdit = originalTotal + sisaSaldo;

  const rupiah = (n) => "Rp " + (n || 0).toLocaleString("id-ID");

  function calculateCurrentTotal() {
    let total = 0;

    jumlahInputs.forEach(input => {
      if (parseInt(input.value) < 0) input.value = 0;

      const jumlah = parseInt(input.value) || 0;
      const harga  = parseInt(input.dataset.harga) || 0;
      const sub    = jumlah * harga;

      total += sub;

      const row = input.closest("tr");
      const cell = row?.querySelector(".subtotal");
      if (cell) {
        cell.textContent = rupiah(sub);
      }
    });

    return total;
  }

  function validateTotal() {
    const newTotal = calculateCurrentTotal();

    // NEW PESANAN
    if (!isEdit) {
      if (newTotal > sisaSaldo) {
        totalField.value = rupiah(newTotal) + " (Saldo Tidak Cukup!)";
        totalField.classList.add("text-danger");
        btnSubmit.disabled = true;
      } else {
        totalField.value = rupiah(newTotal);
        totalField.classList.remove("text-danger");
        btnSubmit.disabled = false;
      }
      return;
    }

    // EDIT MODE = total cannot exceed maxTotalEdit
    if (newTotal > maxTotalEdit) {
      totalField.value = rupiah(newTotal) + " (Melebihi Maksimal Total Baru!)";
      totalField.classList.add("text-danger");
      btnSubmit.disabled = true;
    } else {
      totalField.value = rupiah(newTotal);
      totalField.classList.remove("text-danger");
      btnSubmit.disabled = false;
    }
  }

  validateTotal();

  jumlahInputs.forEach(input => {
    input.addEventListener("input", validateTotal);
    input.addEventListener("change", validateTotal);
  });

  // Final guard on submit
  const form = document.querySelector("form");
  let isSubmitting = false;

  form.addEventListener("submit", function(e) {

    // Kunci submit seketika agar double-click tidak membuat dua POST.
    if (isSubmitting) {
      e.preventDefault();
      return;
    }

    const newTotal = calculateCurrentTotal();

    if (!isEdit && newTotal > sisaSaldo) {
      e.preventDefault();
      alert("Total harga melebihi saldo! Tidak dapat memesan.");
      return;
    }

    if (isEdit && newTotal > maxTotalEdit) {
      e.preventDefault();
      alert("Total baru melebihi Maksimal Total Baru.");
      return;
    }

    isSubmitting = true;
    btnSubmit.disabled = true;
    btnSubmit.textContent = isEdit ? "Menyimpan..." : "Memesan...";
  });

});
</script>

<?php echo view('footer'); ?>
