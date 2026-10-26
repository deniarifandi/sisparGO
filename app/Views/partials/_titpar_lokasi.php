<?php $jenis = (int)($titpar['jenis_kendaraan'] ?? 0); ?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light fw-semibold">1. Data Lokasi</div>
    <div class="card-body">

        <div class="mb-3">
            <label class="form-label">Nama Titik Parkir</label>
            <input type="text" name="titpar_namatempat" class="form-control"
                   value="<?= esc($titpar['titpar_namatempat'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Alamat / Lokasi</label>
            <textarea class="form-control" rows="3"
                      name="titpar_lokasi" required
            ><?= esc($titpar['titpar_lokasi'] ?? '') ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Batas Parkir</label>
            <textarea class="form-control" rows="3"
                      name="batas_parkir" required
            ><?= esc($titpar['batas_parkir'] ?? '') ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Jam Operasional</label>
            <textarea class="form-control" rows="2"
                      name="jam_operasional" required
            ><?= esc($titpar['jam_operasional'] ?? '') ?></textarea>
        </div>

        <!-- Jenis Kendaraan -->
        <div class="mb-3">
            <label class="form-label d-block">Jenis Kendaraan Parkir</label>
            <?php
            $kendaraan = [
                'motor' => ['bit' => 1, 'label' => 'Motor'],
                'mobil' => ['bit' => 2, 'label' => 'Mobil'],
                'truk'  => ['bit' => 4, 'label' => 'Truk'],
            ];
            ?>
            <?php foreach ($kendaraan as $value => $item): ?>
            <div class="form-check">
                <input class="form-check-input" type="checkbox"
                       id="<?= $value ?>" name="jenis_kendaraan[]"
                       value="<?= $value ?>"
                       <?= ($jenis & $item['bit']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="<?= $value ?>">
                    <?= $item['label'] ?>
                </label>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Map -->
        <?= view('partials/_titpar_map', ['titpar' => $titpar]) ?>

    </div>
</div>