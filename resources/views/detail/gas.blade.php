<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Kadar Gas</title>

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
    <h2><i class="fa-solid fa-wind"></i> Detail Kadar Gas</h2>
    <p class="text-secondary">Grafik dan histori data gas realtime</p>
</div>

    <!-- GRAFIK -->
    <div class="card mb-4">
        <div class="card-header">
            Grafik Kadar Gas (Realtime)
        </div>
        <div class="card-body">
            <canvas id="detailGasChart" height="100"></canvas>
        </div>
    </div>
    

    <!-- HISTORY -->
    <div class="card">
        <div class="card-header">
            Riwayat Data Gas
        </div>
        <div class="card-body table-responsive">
            <table class="table table-dark table-bordered table-striped mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kadar Gas (PPM)</th>
                        <th>Waktu</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="gasHistoryTable">
                    <!-- diisi ajax -->
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
/* =======================
   CHART GAS
======================= */

let gasLabels = [];
let gasValues = [];

const ctxGas = document.getElementById('detailGasChart').getContext('2d');

const gasChart = new Chart(ctxGas, {
    type: 'line',
    data: {
        labels: gasLabels,
        datasets: [{
            label: 'Kadar Gas (PPM)',
            data: gasValues,
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
let gasDarurat = 600; // threshold dari sistem

function updateGasDetail() {
    $.ajax({
        url: '/sensor/latest',
        method: 'GET',
        success: function (data) {

            let gas = data.gas;
            let waktu = data.waktu;

            // update grafik
            gasLabels.push(waktu);
            gasValues.push(gas);

            if (gasLabels.length > 20) {
                gasLabels.shift();
                gasValues.shift();
            }

            gasChart.update();

            // status gas
            let statusText = 'NORMAL';
            let statusClass = 'status-normal';

            if (gas >= gasDarurat) {
                statusText = 'DARURAT';
                statusClass = 'status-danger';
            }

            // history table
            $('#gasHistoryTable').prepend(`
                <tr>
                    <td>${no++}</td>
                    <td>${gas}</td>
                    <td>${waktu}</td>
                    <td class="${statusClass}">${statusText}</td>
                </tr>
            `);

            //limit data
            if ($('#gasHistoryTable tr').length > 10) {
        $('#gasHistoryTable tr:last').remove();
    }
        }
    });
}

// realtime update (menunggu IoT)
setInterval(updateGasDetail, 3000);
</script>

</body>
</html>
