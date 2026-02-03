<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Ketinggian Sampah</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <style>
        body {
            background-color: #0f172a;
            color: #e5e7eb;
        }
        .card {
            background-color: #020617;
            border: 1px solid #1e293b;
        }
        .card-header {
            background-color: #020617;
            border-bottom: 1px solid #1e293b;
            font-weight: 600;
        }
        table th, table td {
            vertical-align: middle;
            text-align: center;
        }
        .status-normal {
            color: #22c55e;
            font-weight: 600;
        }
        .status-warning {
            color: #facc15;
            font-weight: 600;
        }
        .status-danger {
            color: #ef4444;
            font-weight: 600;
        }
    </style>
</head>

<body>

<div class="container py-4">

<div class="mb-3">
    <a href="/" class="btn btn-outline-light btn-sm">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
</div>

    <div class="mb-4">
        <h2><i class="fa-solid fa-trash"></i> Detail Ketinggian Sampah</h2>
        <p class="text-secondary">Monitoring ketinggian sampah realtime</p>
    </div>

    <!-- GRAFIK -->
    <div class="card mb-4">
        <div class="card-header">
            Grafik Ketinggian Sampah (Realtime)
        </div>
        <div class="card-body">
            <canvas id="tinggiChart" height="100"></canvas>
        </div>
    </div>

    <!-- HISTORY -->
    <div class="card">
        <div class="card-header">
            Riwayat Ketinggian Sampah
        </div>
        <div class="card-body table-responsive">
            <table class="table table-dark table-bordered table-striped mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Ketinggian (cm)</th>
                        <th>Waktu</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="tinggiHistoryTable">
                    <!-- ajax -->
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
/* =======================
   CHART KETINGGIAN
======================= */

let tinggiLabels = [];
let tinggiValues = [];

const ctxTinggi = document.getElementById('tinggiChart').getContext('2d');

const tinggiChart = new Chart(ctxTinggi, {
    type: 'line',
    data: {
        labels: tinggiLabels,
        datasets: [{
            label: 'Ketinggian Sampah (cm)',
            data: tinggiValues,
            borderWidth: 2,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

/* =======================
   AJAX SENSOR
======================= */

let no = 1;

// ambang batas (dari sistem, bukan dummy visual)
let tinggiWarning = 70;
let tinggiDanger  = 90;

function updateTinggiDetail() {
    $.ajax({
        url: '/sensor/latest',
        method: 'GET',
        success: function (data) {

            let tinggi = data.ketinggian;
            let waktu  = data.waktu;

            // update grafik
            tinggiLabels.push(waktu);
            tinggiValues.push(tinggi);

            if (tinggiLabels.length > 20) {
                tinggiLabels.shift();
                tinggiValues.shift();
            }

            tinggiChart.update();

            // status
            let statusText  = 'AMAN';
            let statusClass = 'status-normal';

            if (tinggi >= tinggiWarning && tinggi < tinggiDanger) {
                statusText = 'WASPADA';
                statusClass = 'status-warning';
            }

            if (tinggi >= tinggiDanger) {
                statusText = 'PENUH';
                statusClass = 'status-danger';
            }

            // tambah ke tabel
            $('#tinggiHistoryTable').prepend(`
                <tr>
                    <td>${no++}</td>
                    <td>${tinggi}</td>
                    <td>${waktu}</td>
                    <td class="${statusClass}">${statusText}</td>
                </tr>
            `);

            // 🔥 LIMIT 10 DATA TERAKHIR
            if ($('#tinggiHistoryTable tr').length > 10) {
                $('#tinggiHistoryTable tr:last').remove();
            }
        }
    });
}

// realtime (menunggu IoT)
setInterval(updateTinggiDetail, 3000);
</script>

</body>
</html>
