<?= view('header') ?>

<?php $allowedRoles = $allowedRoles ?? []; ?>
<?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>

<div class="card">
    <div class="card-header">
        Buat Pengguna
        <a class="btn btn-danger btn-sm float-end" href="<?= base_url('user/list') ?>">Cancel</a>
    </div>

    <div class="card-body">
        <div class="container mt-4">
            <h4 class="mb-4">Form Buat Pengguna</h4>

            <form action="<?= isset($user) ? base_url('user/update/'.$user['user_id']) : base_url('user/store') ?>" method="post" autocomplete="off">
                <?= csrf_field() ?>

            <!-- Decoy password field that traps autofill --> 
                <input type="password" name="fake_pass" class="fake-password-field" autocomplete="new-password" tabindex="-1" style="display: none;">

                 <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Nama</label>
                    <div class="col-sm-9">
                        <input type="text" name="nama" id="nama"
                               value="<?= isset($user['nama']) ? esc($user['nama']) : '' ?>"
                               class="form-control" required>
                    </div>
                </div>

                <!-- username -->
              <div class="row mb-3">
              <label class="col-sm-3 col-form-label">Username</label>
              <div class="col-sm-9">
                <?php if (isset($user) && $user['role'] == 0): ?>
                  <input type="text" class="form-control" value="<?= esc($user['username']) ?>" disabled>
                  <input type="hidden" name="username" value="<?= esc($user['username']) ?>">
                <?php else: ?>
                  <input type="text" name="username" id="username"
                         value="<?= isset($user['username']) ? esc($user['username']) : '' ?>"
                         class="form-control" required autocomplete="off">
                <?php endif; ?>
              </div>
            </div>


                <!-- password -->
                <div class="row mb-3">
                  <label class="col-sm-3 col-form-label">Password</label>
                  <div class="col-sm-9">
                    <div class="input-group">
                      <input type="password" name="password" id="password" class="form-control" <?= isset($user) ? '' : 'required' ?>>
                      <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="bi bi-eye"></i>
                      </button>
                    </div>
                    <?php if (isset($user)): ?>
                      <small class="text-muted">Kosongkan jika tidak ingin mengubah password.</small>
                    <?php endif; ?>
                  </div>
                </div>

                <!-- Role -->
                <div class="row mb-3">
                  <label class="col-sm-3 col-form-label">Peran / Role</label>
                  <div class="col-sm-9">
                    <?php if (isset($user) && $user['role'] == 0): ?>
                      <!-- Show readonly role for superadmin -->
                      <input type="text" class="form-control" value="Superadmin" disabled>
                      <input type="hidden" name="role" value="0">
                    <?php else: ?>
                      <select name="role" class="form-select" required>
                        <option value="">-- Pilih Role --</option>
                        <?php $roleLabels = [1 => 'Supervisor', 2 => 'Admin Utama', 3 => 'Admin', 4 => 'Pengawas']; ?>
                        <?php foreach ($roleLabels as $roleValue => $roleLabel): ?>
                          <?php if (in_array($roleValue, $allowedRoles, true) || (isset($user) && (int) $user['role'] === $roleValue)): ?>
                            <option value="<?= $roleValue ?>" <?= isset($user) && $user['role'] == $roleValue ? 'selected' : '' ?>><?= $roleLabel ?></option>
                          <?php endif; ?>
                        <?php endforeach; ?>
                      </select>
                    <?php endif; ?>
                  </div>
                </div>


                <!-- Submit -->

         <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <?= isset($user) ? 'Update Pengguna' : 'Simpan Pengguna' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
  document.getElementById('togglePassword').addEventListener('click', function () {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');

    if (passwordInput.type === 'password') {
      passwordInput.type = 'text';
      icon.classList.remove('bi-eye');
      icon.classList.add('bi-eye-slash');
    } else {
      passwordInput.type = 'password';
      icon.classList.remove('bi-eye-slash');
      icon.classList.add('bi-eye');
    }
  });
</script>

<?= view('footer') ?>
