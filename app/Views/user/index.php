<?php echo view('header'); ?>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= session()->getFlashdata('success') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= session()->getFlashdata('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        User List <span class="float-end d-flex gap-2"><?php if ((int) session('role') === 0): ?><a class="btn btn-outline-danger btn-sm" href="<?= base_url('user/login-attempts') ?>">Login Attempts</a><?php endif; ?><?php if ((int) session('role') <= 2): ?><a class="btn btn-primary btn-sm" href="<?= base_url('user/create') ?>">Add New</a><?php endif; ?></span>
    </div>
    <div class="card-body">
        <table id="usersTable" class="display" style="font-size: 12px; width: 100%;">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nama</th>
              <th>Username</th>
              <th>Role</th>
              <th>Action</th>
            </tr>
          </thead>
        </table>
    </div>
</div>

 <script>
  $(document).ready(function() {

    const roleLabels = {
      '0': { text: 'Superadmin', class: 'badge bg-dark' },
      '1': { text: 'Supervisor', class: 'badge bg-warning text-dark' },
      '2': { text: 'Admin Utama', class: 'badge bg-info text-dark' },
      '3': { text: 'Admin', class: 'badge bg-primary' },
      '4': { text: 'Pengawas', class: 'badge bg-success' }
    };

    $('#usersTable').DataTable({
      processing: true,
      serverSide: false,
      order: [[3, 'asc']],

      ajax: {
        url: "<?= site_url('user/data') ?>",
        type: "POST"
      },
      columns: [
        { data: 'user_id' },
        { data: 'nama' },
        { data: 'username'},
        {
          data: 'role',
          render: function (data, type, row) {
            if (type === 'sort' || type === 'type') {
              return Number(data);
            }
            const role = roleLabels[data];
            if (role) {
              return `<span class="${role.class}">${role.text}</span>`;
            }
            return `<span class="badge bg-secondary">Tidak Diketahui</span>`;
          }
        },
        {
  data: null,
  orderable: false,
  render: function(data, type, row) {
    // Build edit button (always shown)
    let html = `
      <a class="btn btn-warning btn-sm" href="<?= base_url('user/edit/') ?>${row.user_id}">
        <i class="bi bi-pencil"></i> Edit
      </a>
    `;

    // Only show delete button if NOT Superadmin (role !== 0)
    if (row.role != '0') {
      html += `
        <form action="<?= base_url('user/userdelete/') ?>${row.user_id}" method="post" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?');">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-danger btn-sm">
            <i class="bi bi-trash"></i> Hapus
          </button>
        </form>
      `;
    }

    return html;
  }
}

      ],
    });
  });
</script>

<?php echo view('footer'); ?>
