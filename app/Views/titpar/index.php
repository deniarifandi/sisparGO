<?php echo view('header'); ?>
<script>const titparWilayah = <?= json_encode($kelurahan ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>



<!-- Custom Style Tambahan untuk Visual Lebih Modern -->
<style>
    .card {
        border: none;
        border-radius: 12px;
    }
    .card-header {
        background-color: #ffffff;
        border-bottom: 1px solid #f0f0f0;
        padding: 1rem 1.25rem;
    }
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        color: #6c757d;
        background-color: #f8f9fa;
        white-space: nowrap;
    }
    /* Merapikan layout DataTables & Filter Box */
    .dt-buttons .btn {
        border-radius: 6px;
        margin-right: 4px;
    }
    .dataTables_wrapper .dataTables_filter input {
        border-radius: 6px;
        border: 1px solid #ced4da;
        padding: 0.375rem 0.75rem;
    }
    .filter-box {
        background-color: #f8f9fa;
        border-radius: 10px;
        border: 1px solid #e9ecef;
    }
    #usersTable_wrapper .dt-buttons {
        display: flex;
        flex-wrap: nowrap;
        gap: 3px;
    }
    #usersTable_wrapper .dt-buttons .btn {
        margin: 0;
        padding: .2rem .45rem;
        font-size: .75rem;
        line-height: 1.25;
        white-space: nowrap;
    }
    #usersTable_wrapper .dataTables_filter input {
        width: 150px;
        max-width: 45vw;
        padding: .2rem .45rem;
        font-size: .8rem;
    }
    #usersTable_wrapper .dataTables_length select {
        padding-top: .2rem;
        padding-bottom: .2rem;
        font-size: .8rem;
    }
    #usersTable td { vertical-align: middle; }
    #usersTable td .btn-action {
        width: 29px;
        height: 27px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .78rem;
    }
    @media (max-width: 767.98px) {
        .card-body { padding: 1rem !important; }
        .filter-box { padding: .75rem !important; }
        #usersTable_wrapper .dataTables_filter input { width: 120px; }
    }
</style>

<div class="container-fluid py-4">

    <!-- Flash Message Notification -->
    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= session()->getFlashdata('message'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Main Card Container -->
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold text-dark">
                <i class="bi bi-geo-alt-fill text-primary me-2"></i>Daftar Titik Parkir Kota Bogor
            </h5>
            <?php if (session('role') < 4): ?>
                <a class="btn btn-primary btn-sm px-3 shadow-sm rounded-pill" href="<?= base_url('titpar/create') ?>">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Baru
                </a>
            <?php endif; ?>
        </div>
        
        <div class="card-body p-4">

            <!-- FILTER SECTION -->
            <div class="filter-box p-3 mb-4">
                <div class="row g-3 align-items-center">
                    <div class="col-md-2">
                        <label for="filterStatus" class="form-label small fw-bold text-muted mb-1">
                            <i class="bi bi-funnel me-1"></i>Status
                        </label>
                        <select id="filterStatus" class="form-select form-select-sm">
                            <option value="">-- Semua Status --</option>
                            <option value="3">Aktif</option>
                            <option value="2">Non Aktif</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="filterKecamatan" class="form-label small fw-bold text-muted mb-1">
                            <i class="bi bi-map me-1"></i>Kecamatan
                        </label>
                        <select id="filterKecamatan" class="form-select form-select-sm">
                            <option value="">-- Semua Kecamatan --</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="filterKelurahan" class="form-label small fw-bold text-muted mb-1">
                            <i class="bi bi-building me-1"></i>Kelurahan
                        </label>
                        <select id="filterKelurahan" class="form-select form-select-sm">
                            <option value="">-- Semua Kelurahan --</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="filterMasaBerlaku" class="form-label small fw-bold text-muted mb-1">
                            <i class="bi bi-calendar-check me-1"></i>Masa Berlaku
                        </label>
                        <select id="filterMasaBerlaku" class="form-select form-select-sm">
                            <option value="">-- Semua Masa Berlaku --</option>
                            <option value="aman">Aman (&gt; 30 hari)</option>
                            <option value="mendekati">Mendekati akhir (1–30 hari)</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>

                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" id="btnResetFilter" class="btn btn-outline-secondary btn-sm w-100 mt-4" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- TABLE SECTION -->
            <div class="d-flex justify-content-end mb-2">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-layout-three-columns me-1"></i> Kolom
                    </button>
                    <div id="titparColumnMenu" class="dropdown-menu dropdown-menu-end p-2" style="min-width: 240px;">
                        <div class="small text-muted px-2 py-1">Tampilkan kolom</div>
                        <div class="dropdown-divider"></div>
                        <div id="titparColumnItems"></div>
                        <div class="dropdown-divider"></div>
                        <button id="resetTitparColumns" type="button" class="dropdown-item small text-primary">Reset kolom</button>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table id="usersTable" class="table table-hover align-middle style-table" style="width:100%; font-size: 13px;">
                    <thead>
                        <tr>
                            <th class="text-center">ID</th>
                            <th>Nama Tempat</th>
                            <th>Lokasi / Alamat</th>
                            <th>Masa Berlaku Mulai</th>
                            <th>Masa Berlaku Selesai</th>
                            <th>No. Reg</th>
                            <th>Kecamatan</th>
                            <th>Kelurahan</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th>Koordinator</th>
                            <th>Anggota</th>
                            <th>Zona</th>
                            <th class="text-center">Status</th>
                            <?php if (session('role') < 4): ?>
                                <th class="text-center">Kelola Titik Parkir</th>
                                <th class="text-center">Aksi Status</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {

    const exportPeople = function(people) {
        if (!people || !people.length) return 'Belum ada';
        return people.map(function(person) {
            const name = String(person.nama || '').replace(/\s+/g, ' ').trim();
            return name + (person.anggota_id ? ' #' + person.anggota_id : '');
        }).join('\n');
    };

    // 1. Inisialisasi DataTables
    const table = $('#usersTable').DataTable({
        processing: true,
        serverSide: false,
        responsive: true,
        // Simpan pagination, search, length, dan sorting otomatis di browser.
        stateSave: true,
        stateDuration: -1,

        // Mengatur jumlah data default yang tampil pertama kali (misal: 25)
        pageLength: 10, 

        // Opsi pilihan jumlah baris data yang bisa dipilih pengguna
        lengthMenu: [
            [10, 25, 50, 100, -1],
            ['10 Data', '25 Data', '50 Data', '100 Data', 'Semua Data']
        ],

        // 'l' ditambahkan ke dalam dom agar dropdown pilihan jumlah baris muncul di sebelah kiri atas
        dom: "<'row mb-3'<'col-sm-12 col-md-6 d-flex align-items-center justify-content-start gap-2'lB><'col-sm-12 col-md-6 d-flex align-items-center justify-content-end'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7 d-flex justify-content-end'p>>",


        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="bi bi-file-earmark-excel me-1"></i> Excel',
                className: 'btn btn-outline-success btn-sm',
                exportOptions: {
                    orthogonal: 'export',
                    stripNewlines: false
                },
                customize: function(xlsx) {
                    const sheet = xlsx.xl.worksheets['sheet1.xml'];
                    const styles = xlsx.xl['styles.xml'];
                    const cellXfs = styles.getElementsByTagName('cellXfs')[0];
                    const wrapStyle = cellXfs.children.length;
                    const style = cellXfs.children[0].cloneNode(true);
                    const alignment = styles.createElementNS(styles.documentElement.namespaceURI, 'alignment');
                    alignment.setAttribute('wrapText', '1');
                    alignment.setAttribute('vertical', 'top');
                    style.setAttribute('applyAlignment', '1');
                    style.appendChild(alignment);
                    cellXfs.appendChild(style);
                    cellXfs.setAttribute('count', cellXfs.children.length);

                    // K dan L adalah kolom Koordinator dan Anggota dalam ekspor.
                    $('col', sheet).each(function() {
                        const min = Number(this.getAttribute('min'));
                        if (min === 11 || min === 12) this.setAttribute('width', '50');
                    });
                    $('sheetData row', sheet).each(function() {
                        let lineCount = 1;
                        $('c', this).each(function() {
                            if (!/^(K|L)\d+$/.test(this.getAttribute('r'))) return;
                            const text = $('is t', this).text();
                            if (!text.includes('\n')) return;
                            this.setAttribute('s', wrapStyle);
                            lineCount = Math.max(lineCount, text.split('\n').reduce(function(total, line) {
                                return total + Math.max(1, Math.ceil(line.length / 45));
                            }, 0));
                        });
                        if (lineCount > 1) {
                            this.setAttribute('ht', Math.min(409, lineCount * 15 + 4));
                            this.setAttribute('customHeight', '1');
                        }
                    });
                }
            },
            {
                extend: 'csvHtml5',
                text: '<i class="bi bi-filetype-csv me-1"></i> CSV',
                className: 'btn btn-outline-info btn-sm'
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="bi bi-file-earmark-pdf me-1"></i> PDF',
                className: 'btn btn-outline-danger btn-sm',
                orientation: 'landscape',
                pageSize: 'A4',
                title: 'Daftar Titik Parkir Kota Bogor'
            },
            {
                extend: 'print',
                text: '<i class="bi bi-printer me-1"></i> Cetak',
                className: 'btn btn-outline-secondary btn-sm'
            }
        ],
        order: [[0, 'desc']],
        ajax: {
            url: "<?= site_url('titpar/data') ?>",
            type: "POST",
            dataSrc: function(json) {
                const kecamatan = [...new Map(titparWilayah.filter(r => r.kecamatan_id).map(r => [r.kecamatan_id, r.kecamatan_nama])).entries()];
                const kecSelect = $('#filterKecamatan').empty().append('<option value="">-- Semua Kecamatan --</option>');
                kecamatan.forEach(([id, nama]) => kecSelect.append(new Option(nama, id)));
                $('#filterKelurahan').empty().append('<option value="">-- Semua Kelurahan --</option>');
                return json.data || [];
            }
        },
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari data...",
            lengthMenu: "Tampilkan _MENU_ data",
            zeroRecords: "Data tidak ditemukan",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data tersedia",
            infoFiltered: "(disaring dari _MAX_ total data)"
        },
        columns: [
            { data: 'titpar_id', className: 'text-center fw-bold text-muted' },
            { data: 'titpar_namatempat', className: 'fw-semibold' },
            { data: 'titpar_lokasi' },
            {
                data: 'masa_berlaku_start',
                render: function(data) {
                    return data ? new Date(data + 'T00:00:00').toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '<span class="text-muted">-</span>';
                }
            },
            {
                data: 'masa_berlaku_end',
                render: function(data) {
                    return data ? new Date(data + 'T00:00:00').toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '<span class="text-muted">-</span>';
                }
            },
            { data: 'titpar_newnoreg' },
            {
                data: 'titpar_klrhnid',
                render: function(data, type, row) {
                    return row.kecamatan_nama || '<span class="text-muted">-</span>';
                }
            },
            {
                data: 'titpar_klrhnid',
                render: function (data, type, row) {
                    return row.kelurahan_nama || '<span class="text-muted">-</span>';
                }
            },
            {
                data:"titpar_latitude"
            },
            {
                data:"titpar_longitude"
            },
            {
                data: 'koordinator',
                render: function(data, type) {
                    if (type === 'export') return exportPeople(data);
                    if (!data || data.length === 0) {
                        return '<span class="text-muted fst-italic" style="font-size: 11px;">Belum ada</span>';
                    }

                    let html = '<div class="d-flex flex-wrap gap-1 align-items-center">';
                    data.forEach(function(k) {
                        const noregText = k.anggota_id ? `<span class="opacity-75 ms-1 fw-normal">#${k.anggota_id}</span>` : '';
                        html += `
                            <span class="badge bg-light text-dark border py-1 px-2 fw-semibold" style="font-size: 11px;" title="${k.nama}">
                                <i class="bi bi-person text-primary me-1"></i>${k.nama}${noregText}
                            </span>
                        `;
                    });
                    html += '</div>';
                    return html;
                }
            },
            {
    data: 'anggota',
    visible: false,
    render: function(data, type) {
        if (type === 'export') return exportPeople(data);

        if (!data || data.length === 0) {
            return '<span class="text-muted fst-italic" style="font-size: 11px;">Belum ada</span>';
        }

        let html = '<div class="d-flex flex-column gap-1">';

        data.forEach(function(a) {

            const noregText = a.anggota_id
                ? `<span class="opacity-75 ms-1 fw-normal">#${a.anggota_id}</span>`
                : '';

            html += `
                <span class="badge bg-light text-dark border py-1 px-2 fw-semibold"
                      style="font-size: 11px;"
                      title="${a.nama}">
                    <i class="bi bi-person me-1 text-secondary"></i>
                    ${a.nama}${noregText}
                </span>
            `;
        });

        html += '</div>';

        return html;
    }
},
{
    data: 'zona_id',
    render: function(data, type, row) {

        const zona = Object.fromEntries(titparWilayah.map(item => [String(item.kecamatan_id), item.kecamatan_nama]));

        return zona[String(row.kecamatan_id)] ?? zona[String(data)] ?? '-';
    }
},
            {
                data: 'titpar_status',
                className: 'text-center',
                render: function(data) {
                    if (data == 2) {
                        return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">Non Aktif</span>';
                    } else if (data == 3) {
                        return '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Aktif</span>';
                    } else {
                        return '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">Unknown</span>';
                    }
                }
            },
            <?php if (session('role') < 4): ?>
            {
                data: null,
                className: 'text-center',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <a href="<?= site_url('titpar/manage/') ?>${row.titpar_id}" 
                           class="btn btn-outline-primary btn-sm btn-action sendBtn"
                           title="Kelola Titik Parkir" aria-label="Kelola Titik Parkir">
                            <i class="bi bi-people-fill"></i>
                        </a>
                    `;
                }
            },
            {
                data: null,
                className: 'text-center',
                orderable: false,
                render: function(data, type, row) {
                    if (row.titpar_status == 3) {
                        return `
                            <a href="<?= site_url('titpar/delete/') ?>${row.titpar_id}" 
                               class="btn btn-sm btn-outline-danger btn-action sendBtn"
                               title="Non-Aktifkan Tempat" aria-label="Non-Aktifkan Tempat"
                               onclick="return confirm('Yakin ingin menonaktifkan item ini?')">
                                <i class="bi bi-power"></i>
                            </a>
                        `;
                    } else if (row.titpar_status == 2) {
                        return `
                            <a href="<?= site_url('titpar/undelete/') ?>${row.titpar_id}" 
                               class="btn btn-sm btn-outline-success btn-action sendBtn"
                               title="Aktifkan Tempat Kembali" aria-label="Aktifkan Tempat Kembali"
                               onclick="return confirm('Yakin ingin mengaktifkan item ini kembali?')">
                                <i class="bi bi-check-circle"></i>
                            </a>
                        `;
                    }
                    return '<span class="text-muted">-</span>';
                }
            }
            <?php endif ?>
        ]
    });

    // Preferensi kolom disimpan lokal di browser ini.
    const columnStorageKey = 'titparColumnVisibility';
    const columnLabels = [
        'ID', 'Nama Tempat', 'Lokasi / Alamat', 'Masa Berlaku Mulai', 'Masa Berlaku Selesai', 'No. Reg', 'Kecamatan',
        'Kelurahan', 'Latitude', 'Longitude', 'Koordinator', 'Anggota',
            'Zona', 'Status', 'Kelola Titik Parkir', 'Aksi Status'
    ];
    const savedColumns = (() => {
        try { return JSON.parse(localStorage.getItem(columnStorageKey) || 'null'); }
        catch (error) { return null; }
    })();

    if (Array.isArray(savedColumns) && savedColumns.length === table.columns().count()) {
        savedColumns.forEach((visible, index) => table.column(index).visible(visible, false));
        table.columns.adjust().draw(false);
    }

    const columnItems = $('#titparColumnItems');
    table.columns().every(function(index) {
        const checked = table.column(index).visible();
        const item = $('<label class="dropdown-item py-1 px-2 d-flex align-items-center gap-2"></label>');
        const checkbox = $('<input type="checkbox" class="form-check-input mt-0">')
            .prop('checked', checked)
            .attr('aria-label', 'Tampilkan ' + (columnLabels[index] || ('Kolom ' + (index + 1))));
        checkbox.on('change', function() {
            table.column(index).visible(this.checked);
            localStorage.setItem(columnStorageKey, JSON.stringify(table.columns().visible().toArray()));
        });
        item.append(checkbox, $('<span></span>').text(columnLabels[index] || ('Kolom ' + (index + 1))));
        columnItems.append(item);
    });

    $('#resetTitparColumns').on('click', function() {
        localStorage.removeItem(columnStorageKey);
        table.columns().every(function() { this.visible(true, false); });
        table.columns.adjust().draw(false);
        columnItems.find('input[type="checkbox"]').prop('checked', true);
    });

const tableStateStorageKey = 'sispargoBogorTitparTableState';
const readTableState = () => {
    try { return JSON.parse(localStorage.getItem(tableStateStorageKey) || '{}'); }
    catch (error) { return {}; }
};
const saveTableState = () => {
    localStorage.setItem(tableStateStorageKey, JSON.stringify({
        search: table.search(),
        page: table.page(),
        length: table.page.len(),
        order: table.order(),
        status: $('#filterStatus').val() || '',
        kecamatan: $('#filterKecamatan').val() || '',
        kelurahan: $('#filterKelurahan').val() || '',
        masa: $('#filterMasaBerlaku').val() || ''
    }));
};

const savedTableState = readTableState();
if (savedTableState.search) table.search(savedTableState.search);
if (savedTableState.length) table.page.len(parseInt(savedTableState.length, 10));
if (Array.isArray(savedTableState.order)) table.order(savedTableState.order);

$('#filterStatus').val(savedTableState.status || '');
$('#filterMasaBerlaku').val(savedTableState.masa || '');
if (savedTableState.kecamatan) {
    $('#filterKecamatan').val(savedTableState.kecamatan).trigger('change');
    $('#filterKelurahan').val(savedTableState.kelurahan || '');
}

table.draw(false);
if (Number.isInteger(parseInt(savedTableState.page, 10))) {
    table.page(parseInt(savedTableState.page, 10)).draw(false);
}

table.on('search.dt page.dt length.dt order.dt draw.dt', saveTableState);

    // 2. Custom Filtering Search Logic untuk DataTables
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex, rowData) {
        const selectedStatus = $('#filterStatus').val();
        const selectedKecamatan = $('#filterKecamatan').val();
        const selectedKelurahan = $('#filterKelurahan').val();
        const selectedMasa = $('#filterMasaBerlaku').val();

        const rowStatus = rowData.titpar_status;
        const rowKlrhnId = rowData.titpar_klrhnid || '';
        const rowKecamatanCode = rowData.kecamatan_id || (rowKlrhnId ? rowKlrhnId.substring(0, 6) : '');
        const endDate = rowData.masa_berlaku_end ? new Date(rowData.masa_berlaku_end + 'T00:00:00') : null;
        const daysLeft = endDate ? Math.floor((endDate - new Date(new Date().toDateString())) / 86400000) : null;

        // Filter Status
        if (selectedStatus && rowStatus != selectedStatus) {
            return false;
        }

        // Filter Kecamatan
        if (selectedKecamatan && rowKecamatanCode !== selectedKecamatan) {
            return false;
        }

        // Filter Kelurahan
        if (selectedKelurahan && rowKlrhnId !== selectedKelurahan) {
            return false;
        }

        if (selectedMasa === 'aman' && (daysLeft === null || daysLeft <= 30)) return false;
        if (selectedMasa === 'mendekati' && (daysLeft === null || daysLeft < 0 || daysLeft > 30)) return false;
        if (selectedMasa === 'expired' && (daysLeft === null || daysLeft >= 0)) return false;

        return true;
    });

    // 3. Dynamic Dropdown Kelurahan berdasarkan Kecamatan
    $('#filterKecamatan').on('change', function() {
        const kecId = $(this).val();
        const kelSelect = $('#filterKelurahan');

        kelSelect.html('<option value="">-- Semua Kelurahan --</option>');

        if (kecId) {
            const seen = new Set();
            titparWilayah.forEach(function(row) {
                if (String(row.kecamatan_id) === String(kecId) && row.id && !seen.has(row.id)) {
                    seen.add(row.id);
                    kelSelect.append(new Option(row.kelurahan_nama, row.id));
                }
            });
        }

    table.draw();
    saveTableState();
    });

    // Event Trigger saat Filter Berubah
    $('#filterStatus, #filterKelurahan, #filterMasaBerlaku').on('change', function() {
    table.draw();
    saveTableState();
    });

    if (savedTableState.kecamatan) {
        $('#filterKecamatan').trigger('change');
        $('#filterKelurahan').val(savedTableState.kelurahan || '');
        table.draw(false);
        saveTableState();
    }

    // Reset Button Event
    $('#btnResetFilter').on('click', function() {
        $('#filterStatus').val('');
        $('#filterMasaBerlaku').val('');
        $('#filterKecamatan').val('').trigger('change');
        // table.draw();
        localStorage.removeItem(tableStateStorageKey);

    table.search('').page('first').draw();
    });
});

// Event Listener untuk Overlay Spinner
const overlay = document.getElementById("overlaySpinner");
$(document).on("click", ".sendBtn", function() {
    if (overlay) {
        overlay.classList.remove("d-none");
    }
});
</script>

<?php echo view('footer'); ?>

