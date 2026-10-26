<?= view('header') ?>
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <strong>Pembatasan Login</strong>
    <span class="text-muted small">Maks. <?= (int) $max ?> kegagalan / <?= (int) $minutes ?> menit</span>
  </div>
  <div class="card-body">
    <?php foreach (['success' => 'alert-success', 'error' => 'alert-danger'] as $key => $class): ?>
      <?php if (session()->getFlashdata($key)): ?><div class="alert <?= $class ?>"><?= esc(session()->getFlashdata($key)) ?></div><?php endif; ?>
    <?php endforeach; ?>
    <div class="d-flex gap-2 mb-3 flex-wrap">
      <form method="post" action="<?= base_url('user/login-attempts/reset') ?>" onsubmit="return confirm('Reset semua catatan percobaan login?')">
        <?= csrf_field() ?><input type="hidden" name="scope" value="all"><button class="btn btn-danger btn-sm">Reset Semua</button>
      </form>
      <form method="post" action="<?= base_url('user/login-attempts/reset') ?>" class="d-flex gap-2">
        <?= csrf_field() ?><input type="hidden" name="scope" value="username"><input name="value" class="form-control form-control-sm" placeholder="Username" required><button class="btn btn-outline-danger btn-sm">Reset Username</button>
      </form>
      <form method="post" action="<?= base_url('user/login-attempts/reset') ?>" class="d-flex gap-2">
        <?= csrf_field() ?><input type="hidden" name="scope" value="ip"><input name="value" class="form-control form-control-sm" placeholder="IP address" required><button class="btn btn-outline-danger btn-sm">Reset IP</button>
      </form>
    </div>
    <div class="table-responsive"><table class="table table-sm table-striped"><thead><tr><th>Waktu</th><th>Username</th><th>IP</th><th>Hasil</th></tr></thead><tbody>
      <?php foreach ($rows as $row): ?><tr><td><?= esc($row['attempted_at']) ?></td><td><?= esc($row['username'] ?: '-') ?></td><td><?= esc($row['ip_address'] ?: '-') ?></td><td><span class="badge <?= $row['successful'] ? 'bg-success' : 'bg-danger' ?>"><?= $row['successful'] ? 'Berhasil' : 'Gagal' ?></span></td></tr><?php endforeach; ?>
    </tbody></table></div>
  </div>
</div>
<?= view('footer') ?>
