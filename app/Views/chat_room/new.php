<?php echo view('header'); ?>

<?php if (session()->getFlashdata('message')): ?>
    <div class="alert alert-danger">
        <?= session()->getFlashdata('message'); ?>
    </div>
<?php endif; ?>

<div class="card">

    <div class="card-header">
        <strong>Start New Chat</strong>

        <a href="<?= base_url('chat-room') ?>"
           class="btn btn-danger btn-sm float-end">
            Cancel
        </a>
    </div>

    <div class="card-body">

        <form id="newChatForm">

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">
                    Anggota
                </label>

                <div class="col-sm-9">

                    <select
                        class="form-select select2"
                        name="client_id"
                        id="client_id"
                        data-placeholder="-- Pilih Anggota --"
                        required
                    >

                        <option value=""></option>

                        <?php foreach ($clients as $client): ?>

                            <option value="<?= esc($client['anggota_id']) ?>">

                                <?= esc($client['anggota_nama']) ?>

                                (<?= esc($client['anggota_id']) ?>)

                                <?php if (!empty($client['titpar_namatempat'])): ?>

                                    -- <?= esc($client['titpar_namatempat']) ?>

                                    <?php if (!empty($client['titpar_id'])): ?>
                                        (<?= esc($client['titpar_id']) ?>)
                                    <?php endif; ?>

                                <?php endif; ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>
            </div>

            <div class="text-end">

                <button type="submit"
                        class="btn btn-primary">

                    Open Chat

                </button>

            </div>

        </form>

    </div>

</div>


<script>

$(document).ready(function() {

    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%',
        allowClear: true,
        placeholder: '-- Pilih Anggota --'
    });

});


$('#newChatForm').on('submit', function(e) {

    e.preventDefault();

    const clientId = $('#client_id').val();

    if (!clientId) {
        alert('Silakan pilih anggota');
        return;
    }

    $.ajax({

        url: "<?= site_url('api/chat/start-admin') ?>",

        type: "POST",

        dataType: "json",

        data: {
            client_id: clientId
        },

        success: function(response) {

            console.log(response);

            if (response.status) {

                window.location.href =
                    "<?= site_url('chat-room/') ?>" +
                    response.chat_room_id;

            } else {

                alert(
                    response.message ||
                    'Gagal membuka chat'
                );

            }

        },

        error: function(xhr) {

            console.log(xhr.responseText);

            alert('Gagal membuat chat room');

        }

    });

});

</script>

<?php echo view('footer'); ?>