<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sisparma - Command Center</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
:root {
  --bg-color: #0a1a2f;
  --text-color: #00f0ff;
  --card-bg: rgba(0,50,100,0.6);
  --card-border: rgba(0,255,255,0.3);
  --grid-color: rgba(0,255,255,0.05);
  --chart-bg: rgba(0,240,255,0.2);
  --chart-border: #00f0ff;
}

body.light-mode {
  --bg-color: #f0f2f5;
  --text-color: #003366;
  --card-bg: rgba(255,255,255,0.8);
  --card-border: rgba(0,100,255,0.3);
  --grid-color: rgba(0,0,0,0.05);
  --chart-bg: rgba(0,100,255,0.2);
  --chart-border: #003366;
}

body {
  font-family: 'Share Tech Mono', monospace;
  background: var(--bg-color);
  color: var(--text-color);
  padding-top: 60px;
  position: relative;
}

body::before {
  content:'';
  position: fixed; top:0; left:0; width:100%; height:100%;
  background-image: linear-gradient(var(--grid-color) 1px, transparent 1px),
                    linear-gradient(90deg, var(--grid-color) 1px, transparent 1px);
  background-size: 40px 40px;
  pointer-events: none;
  z-index: 0;
}

.navbar {
  background: rgba(0, 20, 50, 0.8);
  backdrop-filter: blur(5px);
  position: fixed;
  top:0; left:0; width:100%;
  z-index: 1000;
  border-bottom: 1px solid var(--card-border);
  transition: all 0.3s ease;
}

.card {
  background: var(--card-bg);
  border: 1px solid var(--card-border);
  border-radius: 1rem;
  box-shadow: 0 5px 15px rgba(0,255,255,0.2);
  transition: all 0.3s ease;
}
.card:hover { transform: translateY(-4px); }

.card-title, .card-text, .card-icon {
  color: var(--text-color);
}

.dashboard-container { padding: 2rem; z-index:1; position:relative; }
.footer { text-align: center; padding: 1rem; color: var(--text-color); font-size: 0.9rem; }

canvas { background: transparent; }

#dataTable td, 
#dataTable th {
  padding: 4px 6px !important;
  vertical-align: middle;
}

</style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark px-3 px-md-4">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    <a class="navbar-brand fw-bold fs-6 fs-md-5 text-truncate" href="#">
      Parking Information Center
    </a>
    <div class="d-flex align-items-center gap-2">
      <a href="<?= base_url() ?>" class="btn btn-outline-info btn-sm text-nowrap">🏠 Home</a>
      <button id="themeToggle" class="btn btn-outline-light btn-sm">🌗</button>
    </div>
  </div>
</nav>

<div class="dashboard-container container-fluid">
  <div class="row g-4">
    <div class="col-md-4">
      <a href="<?= base_url('transaksi') ?>" style="text-decoration: none; color: inherit;">
        <div class="card p-3 text-center">
          <i class="fas fa-tachometer-alt card-icon mb-2"></i>
          <div class="card-title">Pendapatan Hari Ini</div>
          <div class="card-text" style="font-size:24px">
            Rp. <?= number_format($pendapatanHariIni, 0, ',', '.') ?>
          </div>
          <div class="card-text" style="font-size:14px;">
            Update: <?= date('d M Y H:i:s') ?>
          </div>
        </div>
      </a>
    </div>
    <div class="col-md-4">
      <a href="<?= base_url('potensi') ?>" style="text-decoration: none; color: inherit;">
        <div class="card p-3 text-center">
          <i class="fas fa-network-wired card-icon mb-2"></i>
          <div class="card-title">Target Setoran Hari Ini</div>
          <div class="card-text" style="font-size:24px">Rp. <?= number_format($targetHariIni, 0, ',', '.') ?></div>
          <div class="card-text" style="font-size:14px;">
            Tanggal: <?= date('d M Y') ?>
          </div>
        </div>
      </a>
    </div>
    <div class="col-md-4">
       <div class="card p-3 text-center">
        
          <div class="card-title">Prosentase Pendapatan Harian</div>
          
          <div class="progress mt-2" style="height: 12px; border-radius:5px;">
              <div class="progress-bar" role="progressbar" 
                   style="width: <?= number_format($persentase, 0, ',', '.') ?>%; 
                          background-color: red;" 
                   aria-valuenow="<?= number_format($persentase, 0, ',', '.') ?>" 
                   aria-valuemin="0" 
                   aria-valuemax="100">
              </div>
          </div>

          <!-- Teks persentase -->
          <div class="card-text mt-1" style="font-size:24px">
              <?= number_format($persentase, 2, ',', '.') ?>%
          </div>
          <div class="card-text" style="font-size:14px; visibility: hidden;">
            Tanggal: <?= date('d M Y') ?>
          </div>
      </div>
    </div>
  
  </div>

  <div class="row mt-4">
    <div class="col-md-5 mt-1">
      <div class="card p-4">
        <div class="card-title mb-2" style="font-size:13px">Target x Realisasi Kecamatan (Bulan ini)</div>
        

          <div class="table-responsive">
            <table id="dataTable" class="table table-sm table-bordered table-striped align-middle text-center mb-0">
              <thead class="table-dark">
                <tr style="font-size: 13px;">
                  <th style="width: 40px;">No</th>
                  <th style="min-width: 120px;">Kecamatan</th>
                  <th style="min-width: 100px;">Potensi</th>
                  <th style="min-width: 100px;">Transaksi</th>
                  <th style="min-width: 80px;">%</th>
                </tr>
              </thead>
              <tbody style="font-size: 13px;">
                <tr><td colspan="5">Loading data...</td></tr>
              </tbody>
            </table>
          </div>

      </div>
    </div>
    
    <div class="col-md-5 mt-1">
      <div class="card p-4">
        <div class="card-title mb-2">Target vs Realisasi Bulanan(Total)</div>
         <canvas id="myChart" height="200"></canvas>
      </div>
    </div>

    <div class="col-md-2 mt-1">
      <div class="card p-4">
        <div class="card-title mb-2 text-center">Statistic</div>
        <div class="row">
          <div class="col-md-12 mt-1">
            <a href="<?= base_url('va-owner') ?>" style="text-decoration: none; color: inherit;">
              <div class="card p-2 text-center">
                <i class="fas fa-tachometer-alt card-icon mb-2"></i>
                <div class="card-title">VA Terdaftar</div>
                <div class="card-text"><?= number_format($totalVa, 0, ',', '.') ?></div>
              </div>
            </a>
          </div>
         <div class="col-md-12 mt-1">
          <a href="<?= base_url('titpar') ?>" style="text-decoration: none; color: inherit;">
            <div class="card p-2 text-center">
              <i class="fas fa-tachometer-alt card-icon mb-2"></i>
              <div class="card-title">Titik Parkir</div>
              <div class="card-text" id="totalPotensi">Loading...</div>
            </div>
          </a>
        </div>

        </div>
      </div>
    </div>
    

  </div>

  <div class="row mt-4">
    <div class="col-md-12">
       <div class="card p-4">
        <div class="card-title mb-2">Target vs Realisasi Bulanan & Tahunan</div>
       <table id="dataTableTahunan" class="table table-sm table-bordered table-striped align-middle text-center mb-0" style="font-size:12px">
        <thead class="table-dark">
          <tr>
            <th>Keterangan</th>
            <th>Jan</th>
            <th>Feb</th>
            <th>Mar</th>
            <th>Apr</th>
            <th>Mei</th>
            <th>Jun</th>
            <th>Jul</th>
            <th>Agu</th>
            <th>Sep</th>
            <th>Okt</th>
            <th>Nov</th>
            <th>Des</th>
            <th>Tahunan</th>
          </tr>
        </thead>
        <tbody>
          <!-- Data will be inserted here -->
        </tbody>
      </table>
      </div>
    </div>
  </div>
</div>

<div class="footer">&copy; 2025 Dinas Perhubungan Kota Malang</div>

<script>
function formatIDR(value) {
  if (value >= 1_000_000_000) return (value / 1_000_000_000).toFixed(1) + ' M';
  if (value >= 1_000_000) return (value / 1_000_000).toFixed(1) + ' jt';
  if (value >= 1_000) return (value / 1_000).toFixed(0) + ' rb';
  return value.toString();
}

fetch('<?= base_url() ?>targetsetoranbulanan/')
  .then(res => res.json())
  .then(data => {
    const tbody = document.querySelector('#dataTableTahunan tbody');

    const potensiRow = document.createElement('tr');
    const transaksiRow = document.createElement('tr');
    const prosentaseRow = document.createElement('tr');

    potensiRow.innerHTML = `<td>Potensi</td>`;
    transaksiRow.innerHTML = `<td>Transaksi</td>`;
    prosentaseRow.innerHTML = `<td>Prosentase</td>`;

    let potensiTahunan = 0;
    let transaksiTahunan = 0;

    for (let i = 1; i <= 12; i++) {
      const monthData = data.find(d => d.bulan === i) || { potensi: 0, transaksi: 0 };
      const potensi = Number(monthData.potensi);
      const transaksi = Number(monthData.transaksi);

      potensiTahunan += potensi;
      transaksiTahunan += transaksi;

      potensiRow.innerHTML += `<td>${formatIDR(potensi)}</td>`;
      transaksiRow.innerHTML += `<td>${formatIDR(transaksi)}</td>`;

      const persen = potensi > 0 ? ((transaksi / potensi) * 100).toFixed(2) + ' %' : '0 %';
      prosentaseRow.innerHTML += `<td>${persen}</td>`;
    }

    // Add totals in the last column
    potensiRow.innerHTML += `<td style="font-size:16px">${formatIDR(potensiTahunan)}</td>`;
    transaksiRow.innerHTML += `<td style="font-size:16px">${formatIDR(transaksiTahunan)}</td>`;
    const prosentaseTahunan = potensiTahunan > 0 ? ((transaksiTahunan / potensiTahunan) * 100).toFixed(2) + ' %' : '0 %';
    prosentaseRow.innerHTML += `<td style="font-size:16px">${prosentaseTahunan}</td>`;

    tbody.appendChild(potensiRow);
    tbody.appendChild(transaksiRow);
    tbody.appendChild(prosentaseRow);
  })
  .catch(err => console.error(err));
</script>


<script>
const toggle = document.getElementById('themeToggle');

// Apply saved theme on load
if(localStorage.getItem('theme') === 'light') {
  document.body.classList.add('light-mode');
}

// Toggle theme
toggle.addEventListener('click', () => {
  document.body.classList.toggle('light-mode');
  if(document.body.classList.contains('light-mode')){
    localStorage.setItem('theme','light');
  } else {
    localStorage.setItem('theme','dark');
  }
});
</script>

<script>
fetch('<?= base_url() ?>targetsetoranbulanankecamatan/1')
  .then(res => res.json())
  .then(data => {
    console.log('API Kecamatan raw:', data);
    const tbody = document.querySelector("#dataTable tbody");
    tbody.innerHTML = '';

    function parseNumber(value) {
      if (value === null || value === undefined) return 0;
      if (typeof value === 'number') return value;
      const s = String(value).trim();
      if (s === '') return 0;
      let cleaned = s.replace(/[^\d\.,-]/g, '');
      if (cleaned.indexOf('.') !== -1 && cleaned.indexOf(',') !== -1) {
        cleaned = cleaned.replace(/\./g, '').replace(',', '.');
      } else {
        cleaned = cleaned.replace(/\./g, '').replace(/,/g, '.');
      }
      const n = Number(cleaned);
      return isNaN(n) ? 0 : n;
    }

    let totalPotensi = 0;
    let totalTransaksi = 0;
    let sumPercentages = 0;
    let countRowsWithPotensi = 0;

    // helper to detect "unknown-like" names
    function isUnknownName(name) {
      if (name === null || name === undefined) return true;
      const s = String(name).trim();
      if (s === '') return true;
      // consider common placeholders as unknown
      return /^(unknown|\-+|n\/a|null|undefined)$/i.test(s);
    }

    let visibleIndex = 0; // numbering for visible rows

    data.forEach((item) => {
      const potensi = parseNumber(item.potensi);
      const transaksi = parseNumber(item.transaksi);
      const nama = item.kecamatan_nama ?? '';

      // If name is unknown-like AND both values are zero -> skip row entirely
      if (isUnknownName(nama) && potensi === 0 && transaksi === 0) {
        return; // skip
      }

      // include this row
      visibleIndex++;
      totalPotensi += potensi;
      totalTransaksi += transaksi;

      const percentage = potensi > 0 ? (transaksi / potensi) * 100 : 0;
      if (potensi > 0) {
        sumPercentages += percentage;
        countRowsWithPotensi++;
      }

      const row = `
        <tr>
          <td>${visibleIndex}</td>
          <td>${nama || '-'}</td>
          <td>${potensi.toLocaleString('id-ID')}</td>
          <td>${transaksi.toLocaleString('id-ID')}</td>
          <td>${percentage.toFixed(2)}%</td>
        </tr>
      `;
      tbody.insertAdjacentHTML('beforeend', row);
    });

    const weightedAvg = totalPotensi > 0
      ? ((totalTransaksi / totalPotensi) * 100)
      : 0;

    const simpleAvg = countRowsWithPotensi > 0
      ? (sumPercentages / countRowsWithPotensi)
      : 0;

    console.log({
      totalPotensi,
      totalTransaksi,
      weightedAvg: weightedAvg.toFixed(2) + '%',
      simpleAvg: simpleAvg.toFixed(2) + '%',
      visibleRows: visibleIndex
    });

    // If no visible rows, show a friendly message
    if (visibleIndex === 0) {
      tbody.innerHTML = `<tr><td colspan="5" class="text-muted">No data available.</td></tr>`;
      return;
    }

    const totalRow = `
      <tr class="fw-bold table-info">
        <td colspan="2">Total</td>
        <td>${totalPotensi.toLocaleString('id-ID')}</td>
        <td>${totalTransaksi.toLocaleString('id-ID')}</td>
        <td>${weightedAvg.toFixed(2)}%</td>
      </tr>
    `;
    tbody.insertAdjacentHTML('beforeend', totalRow);
  })
  .catch(err => {
    console.error('Error loading kecamatan data:', err);
    document.querySelector("#dataTable tbody").innerHTML =
      `<tr><td colspan="5" class="text-danger">Gagal memuat data.</td></tr>`;
  });
</script>



<script>

function getChartColors() {
  return {
    borderColor: getComputedStyle(document.body).getPropertyValue('--chart-border'),
    backgroundColor: getComputedStyle(document.body).getPropertyValue('--chart-bg')
  };
}

let chartColors = getChartColors();

fetch('<?= base_url() ?>targetsetoranbulanan')
.then(res => res.json())
.then(data => {
    const labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Okt','Nov','Des'];
    
    const potensiData = labels.map((_, i) => data[i].potensi);
    const transaksiData = labels.map((_, i) => data[i].transaksi);
    console.log(data[8].potensi);
    new Chart(document.getElementById('myChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: 'Potensi', data: potensiData, backgroundColor: 'rgba(54,162,235,0.5)' },
                { label: 'Realisasi', data: transaksiData, backgroundColor: 'rgba(255,99,132,0.5)' }
            ]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero:true } }
        }
    });
});


// fetch('<?= base_url() ?>targetsetoranbulanankecamatan/1')
//   .then(res => res.json())
//   .then(data => {
//     const labels = data.map(item => item.kecamatan_nama || 'Unknown');
//     const potensiData = data.map(item => item.potensi || 0);
//     const transaksiData = data.map(item => item.transaksi || 0);

//     new Chart(document.getElementById('kecamatanChart'), {
//       type: 'bar',
//       data: {
//         labels: labels,
//         datasets: [
//           { label: 'Potensi', data: potensiData, backgroundColor: 'rgba(54,162,235,0.5)' },
//           { label: 'Realisasi', data: transaksiData, backgroundColor: 'rgba(255,99,132,0.5)' }
//         ]
//       },
//       options: {
//         responsive: true,
//         scales: { y: { beginAtZero: true } }
//       }
//     });
//   });


</script>

<script>
// Update chart colors when theme toggled
toggle.addEventListener('click', () => {
  let newColors = getChartColors();
  chart.data.datasets[0].borderColor = newColors.borderColor;
  chart.data.datasets[0].backgroundColor = newColors.backgroundColor;
  chart.options.plugins.legend.labels.color = getComputedStyle(document.body).getPropertyValue('--text-color');
  chart.options.scales.x.ticks.color = getComputedStyle(document.body).getPropertyValue('--text-color');
  chart.options.scales.x.grid.color = getComputedStyle(document.body).getPropertyValue('--grid-color');
  chart.options.scales.y.ticks.color = getComputedStyle(document.body).getPropertyValue('--text-color');
  chart.options.scales.y.grid.color = getComputedStyle(document.body).getPropertyValue('--grid-color');
  chart.update();
});
</script>

</body>

<script>
// reload every 1 minute (60000 ms)
setTimeout(function() {
   location.reload();
}, 60000);
</script>

<script>
  // Replace with your actual API URL
  const apiUrl = '<?= base_url('titpar/count') ?>';

  fetch(apiUrl)
    .then(response => response.json())
    .then(result => {
      if (result.status === 'success' && result.data && result.data.length > 0) {
        const total = result.data[0].total;
        // Format number with dots (e.g., 1.647)
        const formatted = new Intl.NumberFormat('id-ID').format(total);
        document.getElementById('totalPotensi').textContent = formatted;
      } else {
        document.getElementById('totalPotensi').textContent = '0';
      }
    })
    .catch(error => {
      console.error('Error fetching data:', error);
      document.getElementById('totalPotensi').textContent = 'Error';
    });
</script>


</html>
