<?php echo view('header'); ?>

<?php if (session()->getFlashdata('message')): ?>
    <div class="alert alert-info">
        <?= session()->getFlashdata('message'); ?>
    </div>
<?php endif; ?>

<div class="card">

  <div class="card-header">
    <div class="row g-2">
      <div class="col-md-4">
        <input type="text" id="keyword" class="form-control form-control-sm"
               placeholder="Search client / message">
      </div>
      <div class="col-md-2">
        <button id="filterBtn" class="btn btn-sm btn-primary w-100">Filter</button>
      </div>
      <div class="col-md-2">
        <button id="resetBtn" class="btn btn-sm btn-secondary w-100">Reset</button>
      </div>
    <div class="col-md-2">
  <button type="button" class="btn btn-sm btn-success w-100" data-bs-toggle="modal" data-bs-target="#newChatModal">
    + Chat Baru
  </button>
</div>
    </div>
  </div>

  <div class="card-body">
    <table id="chatTable" class="display" style="font-size:12px; width:100%;">
      <thead>
        <tr>
          <th>ID</th>
          <th>Client</th>
          <th>Last Message</th>
          <th>Unread</th>
          <th>Last Activity</th>
          <th>Action</th>
        </tr>
      </thead>
    </table>
  </div>

</div>

<!-- New Chat Modal -->
<div class="modal fade" id="newChatModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Mulai Chat Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="text" id="clientSearch" class="form-control" placeholder="Cari nama klien...">
        <div id="clientResults" class="list-group mt-2" style="max-height:250px; overflow-y:auto;"></div>
      </div>
    </div>
  </div>
</div>

<script>
let searchTimer;
$('#clientSearch').on('input', function () {
  clearTimeout(searchTimer);
  const q = $(this).val();
  if (q.length < 2) { $('#clientResults').empty(); return; }

  searchTimer = setTimeout(function () {
    $.get("<?= site_url('chat-room/search-client') ?>", { q: q }, function (data) {
      $('#clientResults').empty();
      data.forEach(function (c) {
        $('#clientResults').append(`
          <button type="button" class="list-group-item list-group-item-action"
                  data-id="${c.anggota_id}">
            ${c.anggota_nama} - ${c.titpar_namatempat}
          </button>
        `);
      });
    });
  }, 300);
});

$('#clientResults').on('click', '.list-group-item', function () {
  const clientId = $(this).data('id');

  $.post("<?= site_url('chat-room/admin-start') ?>", { client_id: clientId }, function (res) {
    if (res.status) {
      window.location.href = "<?= site_url('chat-room/') ?>" + res.chat_room_id;
    } else {
      alert(res.message || 'Gagal membuat chat');
    }
  }, 'json');
});
</script>

<script>
let table;

$(document).ready(function () {

  table = $('#chatTable').DataTable({
  processing: true,
  serverSide: true,
  pageLength: 10,
  order: [[4, 'desc']], // column "Last Activity"
  ajax: {
    url: "<?= site_url('chat-room/data') ?>",
    type: "POST",
    data: function (d) {
      d.keyword = $('#keyword').val();
    }
  },
  columns: [
  { data: 'id' },
  { data: 'anggota_nama' },
  { data: 'message' },
  {
    data: 'unread_count',
    className: 'dt-body-center',
    render: function (data) {
      return data > 0
        ? `<span class="badge bg-danger">${data}</span>`
        : `<span class="badge bg-secondary">0</span>`;
    }
  },
  {
    data: 'created_at',
    render: function (data) {
      if (!data) return '';
      const date = new Date(data);
      return date.toLocaleString('id-ID');
    }
  },
  {
    data: 'id',
    orderable: false,
    searchable: false,
    render: function (id) {
      return `
        <a href="<?= site_url('chat-room/') ?>${id}"
           class="btn btn-sm btn-outline-primary">
          Open Chat
        </a>
      `;
    }
  }
]
});

});

/* Filters */
$('#filterBtn').on('click', function () {
  table.ajax.reload();
});

$('#resetBtn').on('click', function () {
  $('#keyword').val('');
  table.ajax.reload();
});
</script>

<?php echo view('footer'); ?>
