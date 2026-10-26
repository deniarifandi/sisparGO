<?php echo view('header'); ?>

<?php if (session()->getFlashdata('message')): ?>
    <div class="alert alert-info">
        <?= session()->getFlashdata('message'); ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        Daftar Titpar Grup
        <?php if (session('role') < 4): ?>
            <a class="btn btn-primary btn-sm float-end" href="<?= base_url('titpargrup/create') ?>">Add New</a>
        <?php endif ?>
    </div>
    <div class="card-body">
        <table id="usersTable" class="display" style="font-size: 12px; width: 100%;">
            <thead>
                <tr>
                    <th>Nama (Anggota ID)</th>
                    <th>Nama Tempat (Titpar ID)</th>
                    <th>Status Anggota</th>
                    <th>Nama Koordinator</th>
                    <th>Shift</th>
                    <?php if (session('role') < 4): ?>
                        <th>Action</th>
                    <?php endif ?>
                </tr>
            </thead>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#usersTable').DataTable({
        processing: true,
        serverSide: false,
        order: [[0, 'asc']],
        ajax: {
            url: "<?= site_url('titpargrup/data') ?>",
            type: "POST"
        },
        columns: [
            { data: 'nama_anggota_custom' },
            { data: 'nama_tempat_custom' },
            {
                data: 'titpargrup_staanggota',
                render: function(data, type, row) {
                    if (data == "KOORDINATOR") {
                        return '<span class="badge bg-danger">KOORDINATOR</span>';
                    } else if (data == "ANGGOTA") {
                        return '<span class="badge bg-success">ANGGOTA</span>';
                    } else {
                        return '<span class="badge bg-secondary">BELUM TERDAFTAR</span>';
                    }
                }
            },
            { data: 'koordinator_custom' },
            { data: 'shift_custom' },
            <?php if (session('role') < 4): ?>
            { 
                data: null,
                render: function(data, type, row) {
                    return `
                        <a href="<?= site_url('titpargrup/edit/') ?>${row.titpargrup_id}" 
                           class="btn btn-warning btn-sm">Edit</a>
                        <a href="<?= site_url('titpargrup/delete/') ?>${row.titpargrup_id}" 
                           class="btn btn-danger btn-sm" 
                           onclick="return confirm('Are you sure you want to delete this item?')">
                           Delete
                        </a>
                    `;
                }
            }
            <?php endif ?>
        ]
    });
});
</script>

<script type="text/javascript">
const overlay = document.getElementById("overlaySpinner");

$(document).on("click", ".sendBtn", function() {
    if (overlay) {
        overlay.classList.remove("d-none");
    }
});
</script>

<?php echo view('footer'); ?>