<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Pengguna - Monitoring TPA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        body {
            background-color: #0f172a;
            color: #e2e8f0;
        }
        .card {
            background-color: #1e293b;
            border: 1px solid #334155;
        }
        .table th {
            color: #94a3b8;
            font-size: 0.8rem;
            text-transform: uppercase;
        }
        .badge-aktif {
            background-color: #10b981;
        }
        .badge-nonaktif {
            background-color: #64748b;
        }
    </style>
</head>
<body>

<div class="container py-4" style="max-width: 1200px">

    <!-- Tombol Kembali -->
    <div class="mb-3">
        <a href="/" class="btn btn-outline-light btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Header -->
    <div class="mb-4">
        <h3><i class="fa-solid fa-users"></i> Detail Pengguna</h3>
        <p class="text-secondary">Data hasil tap RFID masuk dan keluar area TPA</p>
    </div>

    <!-- Tabel -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode RFID</th>
                            <th>Nama</th>
                            <th>Masuk</th>
                            <th>Keluar</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="userTable">
                        <tr>
                            <td colspan="6" class="text-center py-4">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <small class="text-secondary">Menampilkan 10 data terakhir</small>
        </div>
    </div>
</div>

<script>
function loadUserLog() {
    $.get('/user-log/data', function (data) {

        let html = '';

        if (data.length === 0) {
            html = `<tr>
                        <td colspan="6" class="text-center">Belum ada data</td>
                    </tr>`;
        } else {
            data.slice(0, 10).forEach((log, index) => {

                const masuk = log.masuk
                    ? new Date(log.masuk).toLocaleString('id-ID')
                    : '-';

                const keluar = log.keluar
                    ? new Date(log.keluar).toLocaleString('id-ID')
                    : '-';

                const statusBadge = log.status === 'masuk'
                    ? `<span class="badge badge-aktif">Aktif</span>`
                    : `<span class="badge badge-nonaktif">Tidak Aktif</span>`;

                html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${log.user.rfid}</td>
                        <td>${log.user.nama}</td>
                        <td>${masuk}</td>
                        <td>${keluar}</td>
                        <td>${statusBadge}</td>
                    </tr>
                `;
            });
        }

        $('#userTable').html(html);
    });
}

// initial load
loadUserLog();

// realtime refresh
setInterval(loadUserLog, 3000);
</script>

</body>
</html>
