<div class="card border shadow-none">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Potensi Kendaraan</h6>
        <button type="button" class="btn btn-primary btn-sm"
                data-bs-toggle="modal" data-bs-target="#modalPotensi">
            <i class="fa fa-pencil"></i> Edit
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm text-center mb-0">
                <thead>
                    <tr>
                        <?php foreach (['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $hari): ?>
                            <th><?= $hari ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <?php foreach (['senin','selasa','rabu','kamis','jumat','sabtu','minggu'] as $key): ?>
                            <td><?= esc($titpar['potensi_' . $key]) ?></td>
                        <?php endforeach; ?>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>