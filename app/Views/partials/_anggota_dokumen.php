<div class="card border shadow-none">

    <div class="card-header">
        <strong>Dokumen</strong>
    </div>

    <div class="card-body">

        <div class="row g-3">

            <?php
            $docs = [
                'foto_ktp'     => 'Foto KTP',
                'kk'           => 'Kartu Keluarga',
                'anggota_foto' => 'Foto Anggota',
            ];
            ?>

            <?php foreach ($docs as $field => $label): ?>

                <div class="col-md-4">

                    <div class="card h-100">

                        <div class="card-header">
                            <?= $label ?>
                        </div>

                        <div class="card-body text-center">

                            <?php if (!empty($anggota[$field])): ?>

                                <img src="<?= base_url('uploads/'.$anggota[$field]) ?>"
                                     class="img-fluid rounded border mb-3">

                            <?php else: ?>

                                <div class="text-muted mb-3">
                                    Belum ada file
                                </div>

                            <?php endif; ?>

                            <button
                                class="btn btn-primary btn-sm btn-upload"
                                data-id="<?= $anggota['id'] ?>"
                                data-field="<?= $field ?>"
                                data-bs-toggle="modal"
                                data-bs-target="#modalUpload">
                                Upload
                            </button>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</div>