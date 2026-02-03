<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Monitoring TPA Real-time</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --color-dark-bg: #0f172a;
            --color-card-bg: #1e293b;
            --color-border: #334155;
            --color-text-primary: #e2e8f0;
            --color-text-secondary: #94a3b8;
            --color-primary: #3b82f6;
            --color-success: #10b981;
            --color-warning: #f59e0b;
            --color-danger: #ef4444;
            --color-info: #06b6d4;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--color-dark-bg);
            color: var(--color-text-primary);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
            line-height: 1.6;
            min-height: 100vh;
        }

        .navbar {
            background-color: var(--color-card-bg);
            border-bottom: 1px solid var(--color-border);
            padding: 1rem 0;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--color-primary) !important;
        }

        .main-container {
            padding: 2rem 1rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header Section */
        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--color-text-primary);
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            font-size: 0.95rem;
            color: var(--color-text-secondary);
        }

        /* Card Styles */
        .card {
            background-color: var(--color-card-bg);
            border: 1px solid var(--color-border);
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            transition: border-color 0.2s ease;
        }

        .card:hover {
            border-color: var(--color-primary);
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid var(--color-border);
            padding: 1.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--color-text-primary);
            margin-bottom: 0;
        }

        /* Status Bar */
        .status-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            border-radius: 0.75rem;
            margin-bottom: 2rem;
            font-weight: 600;
        }

        .status-bar.normal {
            background-color: rgba(16, 185, 129, 0.1);
            border: 1px solid var(--color-success);
            color: var(--color-success);
        }

        .status-bar.warning {
            background-color: rgba(245, 158, 11, 0.1);
            border: 1px solid var(--color-warning);
            color: var(--color-warning);
        }

        .status-bar.danger {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--color-danger);
            color: var(--color-danger);
            animation: pulse-danger 2s infinite;
        }

        @keyframes pulse-danger {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }

        .status-icon {
            font-size: 1.5rem;
            margin-right: 0.75rem;
        }

        .status-text {
            flex: 1;
        }

        .status-time {
            font-size: 0.85rem;
            color: inherit;
            opacity: 0.8;
        }

        /* Metrics Grid */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .metric-card {
            background-color: var(--color-card-bg);
            border: 1px solid var(--color-border);
            border-radius: 0.75rem;
            padding: 1.5rem;
            text-align: center;
        }

        .metric-icon {
            font-size: 2rem;
            margin-bottom: 0.75rem;
            color: var(--color-primary);
        }

        .metric-label {
            font-size: 0.85rem;
            color: var(--color-text-secondary);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 500;
        }

        .metric-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--color-text-primary);
            margin-bottom: 0.5rem;
        }

        .metric-unit {
            font-size: 0.85rem;
            color: var(--color-text-secondary);
        }

        .metric-threshold {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--color-border);
            font-size: 0.8rem;
            color: var(--color-text-secondary);
        }

        /* Chart Container */
        .chart-container {
            position: relative;
            height: 300px;
            margin: 1.5rem 0;
        }

        /* Table Styles */
        .table {
            color: var(--color-text-primary);
            border-color: transparent;
            margin-bottom: 0;
        }

        .table thead {
            background-color: #0d1829;
            border-bottom: 1px solid var(--color-border);
        }

        .table thead th {
            color: var(--color-text-secondary);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            border-color: transparent;
            padding: 1.25rem 0.75rem;
            vertical-align: middle;
            background-color: transparent;
        }

        .table tbody {
            background-color: transparent;
        }

        .table tbody tr {
            border-bottom: 1px solid var(--color-border);
            transition: background-color 0.2s ease, border-color 0.2s ease;
            background-color: transparent;
        }

        .table tbody tr:hover {
            background-color: rgba(59, 130, 246, 0.1);
            border-bottom-color: rgba(59, 130, 246, 0.5);
        }

        .table tbody td {
            border-color: transparent;
            padding: 1.25rem 0.75rem;
            vertical-align: middle;
            color: var(--color-text-primary);
            background-color: transparent;
        }

        .table tbody tr:last-child {
            border-bottom: 1px solid var(--color-border);
        }

        /* Status Badge */
        .badge-status {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
        }

        .badge-masuk {
            background-color: var(--color-success);
            color: #ffffff;
            border: none;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
        }

        .badge-masuk:hover {
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .badge-keluar {
            background-color: var(--color-text-secondary);
            color: var(--color-card-bg);
            border: none;
            box-shadow: 0 2px 8px rgba(148, 163, 184, 0.2);
        }

        .badge-keluar:hover {
            box-shadow: 0 4px 12px rgba(148, 163, 184, 0.3);
        }

        /* Form Styles */
        .form-control,
        .form-select {
            background-color: transparent;
            border: 1px solid var(--color-border);
            color: var(--color-text-primary);
            border-radius: 0.5rem;
            padding: 0.625rem 0.875rem;
            transition: border-color 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: transparent;
            border-color: var(--color-primary);
            color: var(--color-text-primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-label {
            color: var(--color-text-primary);
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-text {
            color: var(--color-text-secondary);
            font-size: 0.85rem;
        }

        /* Button Styles */
        .btn {
            border-radius: 0.5rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 0.85rem;
            padding: 0.6rem 1.25rem;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background-color: var(--color-primary);
            border-color: var(--color-primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: #2563eb;
            border-color: #2563eb;
        }

        .btn-outline-primary {
            border: 1px solid var(--color-primary);
            color: var(--color-primary);
            background-color: transparent;
        }

        .btn-outline-primary:hover {
            background-color: rgba(59, 130, 246, 0.1);
        }

        /* Alert Styles */
        .alert {
            background-color: transparent;
            border-radius: 0.75rem;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }

        .alert-danger {
            border: 1px solid var(--color-danger);
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--color-danger);
        }

        .alert-success {
            border: 1px solid var(--color-success);
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--color-success);
        }

        .alert h5 {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        /* Settings Section */
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .setting-group {
            padding: 1rem;
            border: 1px solid var(--color-border);
            border-radius: 0.5rem;
            background-color: rgba(0, 0, 0, 0.2);
        }

        /* Last Update */
        .last-update {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 0.5rem;
            margin-top: 1rem;
            font-size: 0.85rem;
            color: var(--color-text-secondary);
        }

        .last-update-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .last-update-time {
            font-weight: 600;
            color: var(--color-text-primary);
        }

        .metric-action {
            margin-top: 1rem;
        }

        .metric-action a {
            font-size: 0.75rem;
            padding: 0.35rem 0.75rem;
        }


        /* Responsive */
        @media (max-width: 768px) {
            .main-container {
                padding: 1rem 0.5rem;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .metrics-grid {
                grid-template-columns: 1fr;
            }

            .settings-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Emergency Alert */
        .alert-emergency {
            display: none;
            border-left: 4px solid var(--color-danger);
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid" style="max-width: 1400px; margin: 0 auto;">
            <a class="navbar-brand" href="/">
                <i class="fas fa-trash-alt me-2"></i>TPA Monitor
            </a>
            <span class="navbar-text" style="color: var(--color-text-secondary); font-size: 0.9rem;">
                Sistem Pemantauan Gas & Sampah Terintegrasi
            </span>
        </div>
    </nav>

    <div class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Monitoring Real-Time</h1>
            <p class="page-subtitle">Pantau status TPA dan data sensor secara langsung</p>
        </div>

        <!-- Emergency Alert -->
        <div class="alert alert-danger alert-emergency" id="emergencyAlert" role="alert">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>PERINGATAN DARURAT GAS!</h5>
            <p class="mb-0">Semua pengguna telah dievakuasi dari area TPA</p>
        </div>

        <!-- Status Overview -->
        <div id="statusContainer" class="status-bar normal">
            <div class="status-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="status-text">
                <div>STATUS SISTEM: <span id="statusText">NORMAL</span></div>
            </div>
            <div class="status-time" id="lastUpdate">Terakhir update: --:--:--</div>
        </div>

        <!-- Metrics Cards -->
        <div class="metrics-grid">
            <!-- Gas Level Card -->
            <div class="metric-card">
                <div class="metric-icon">
                    <i class="fas fa-wind"></i>
                </div>
                <div class="metric-label">Kadar Gas</div>
                <div class="metric-value" id="gasValue">0</div>
                <div class="metric-unit">PPM</div>
                <div class="metric-threshold">
                    <div>Normal: <span id="gasNormalDisplay">500</span></div>
                    <div>Darurat: <span id="gasDaruratDisplay">600</span></div>
                    <div class="metric-action">
                    <a href="/detail/gas" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-eye me-1"></i> Detail
                    </a>
                    </div>
                    </div>
            </div>

            <!-- Waste Height Card -->
            <div class="metric-card">
                <div class="metric-icon">
                    <i class="fas fa-ruler-vertical"></i>
                </div>
                <div class="metric-label">Ketinggian Sampah</div>
                <div class="metric-value" id="ketinggianValue">0</div>
                <div class="metric-unit">CM</div>
                <div class="metric-threshold">
                    <div>Status: <span id="sampahStatus">-</span></div>
                     <div class="metric-action">
                    <a href="/detail/sampah" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-eye me-1"></i> Detail</a>
                    </div>
                </div>
            </div>

            <!-- User Count Card -->
            <div class="metric-card">
                <div class="metric-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="metric-label">Pengguna Aktif</div>
                <div class="metric-value" id="userCount">0</div>
                <div class="metric-unit">Orang</div>
                <div class="metric-threshold">
                    <div id="userStatus">Tidak ada pengguna</div>
                        <div class="metric-action">
                        <a href="/detail/pengguna" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye me-1"></i> Detail
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <!-- Gas Chart -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-chart-line me-2"></i>Grafik Kadar Gas
                    </h5>
                    <div class="chart-container">
                        <canvas id="gasChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Waste Height Chart -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-chart-area me-2"></i>Grafik Ketinggian Sampah
                    </h5>
                    <div class="chart-container">
                        <canvas id="ketinggianChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Log Table
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-users me-2"></i>Log Pengguna Masuk/Keluar
                    <span class="badge bg-primary ms-2" id="userMasukCount">0</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>RFID</th>
                                <th>Waktu Masuk</th>
                                <th>Waktu Keluar</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="userLogTable">
                            <tr>
                                <td colspan="6" class="text-center" style="padding: 2rem;">Memuat data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
 -->
        <!-- Settings Section -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-cog me-2"></i>Pengaturan Threshold Gas
                </h5>
            </div>
            <div class="card-body">
                <!-- Success/Error Messages -->
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <ul class="mb-0" style="padding-left: 1.5rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <!-- Settings Form -->
                <form action="/gas-control" method="POST">
                    @csrf
                    <div class="settings-grid">
                        <div class="setting-group">
                            <label class="form-label">
                                <i class="fas fa-check-circle me-2" style="color: var(--color-success);"></i>
                                Batas Normal (PPM)
                            </label>
                            <input type="number" 
                                   name="gas_normal" 
                                   class="form-control" 
                                   value="{{ $gasNormal ?? 500 }}" 
                                   required>
                            <small class="form-text">Gas di bawah nilai ini dianggap normal</small>
                        </div>

                        <div class="setting-group">
                            <label class="form-label">
                                <i class="fas fa-exclamation-triangle me-2" style="color: var(--color-danger);"></i>
                                Batas Darurat (PPM)
                            </label>
                            <input type="number" 
                                   name="gas_darurat" 
                                   class="form-control" 
                                   value="{{ $gasDarurat ?? 600 }}" 
                                   required>
                            <small class="form-text">Gas di atas nilai ini memicu status darurat</small>
                        </div>
                    </div>

                    <div style="margin-top: 1.5rem; text-align: center;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // Configuration
        const maxDataPoints = 20;
        
        let gasData = {
            labels: [],
            values: []
        };
        
        let ketinggianData = {
            labels: [],
            values: []
        };

        // Gas Chart
        const gasCtx = document.getElementById('gasChart').getContext('2d');
        const gasChart = new Chart(gasCtx, {
            type: 'line',
            data: {
                labels: gasData.labels,
                datasets: [{
                    label: 'Kadar Gas (PPM)',
                    data: gasData.values,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: '#ef4444',
                    pointBorderColor: '#1e293b'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            color: '#94a3b8',
                            font: { size: 12 }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(51, 65, 85, 0.5)'
                        },
                        ticks: {
                            color: '#94a3b8',
                            callback: function(value) {
                                return value + ' PPM';
                            }
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(51, 65, 85, 0.5)'
                        },
                        ticks: {
                            color: '#94a3b8'
                        }
                    }
                }
            }
        });

        // Ketinggian Chart
        const ketinggianCtx = document.getElementById('ketinggianChart').getContext('2d');
        const ketinggianChart = new Chart(ketinggianCtx, {
            type: 'line',
            data: {
                labels: ketinggianData.labels,
                datasets: [{
                    label: 'Ketinggian Sampah (cm)',
                    data: ketinggianData.values,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#1e293b'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            color: '#94a3b8',
                            font: { size: 12 }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(51, 65, 85, 0.5)'
                        },
                        ticks: {
                            color: '#94a3b8',
                            callback: function(value) {
                                return value + ' cm';
                            }
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(51, 65, 85, 0.5)'
                        },
                        ticks: {
                            color: '#94a3b8'
                        }
                    }
                }
            }
        });

        let gasNormal = {{ $gasNormal ?? 500 }};
        let gasDarurat = {{ $gasDarurat ?? 600 }};

        // Update sensor data
        function updateSensorData() {
            $.ajax({
                url: '/sensor/latest',
                method: 'GET',
                success: function(data) {
                    if (data) {
                        $('#gasValue').text(data.gas);
                        $('#ketinggianValue').text(data.ketinggian);
                        $('#lastUpdate').text('Terakhir update: ' + data.waktu);
                        
                        updateStatus(data.gas, data.ketinggian, data.status);
                        updateCharts(data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching sensor data:', error);
                }
            });
        }

        // Update status
        function updateStatus(gas, ketinggian, status) {
            const statusContainer = $('#statusContainer');
            const alert = $('#emergencyAlert');
            
            if (gas >= gasDarurat) {
                statusContainer.removeClass('normal warning').addClass('danger');
                statusContainer.html(`
                    <div class="status-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="status-text"><div>STATUS SISTEM: <span id="statusText">GAS DARURAT</span></div></div>
                    <div class="status-time" id="lastUpdate">Terakhir update: ${new Date().toLocaleTimeString('id-ID')}</div>
                `);
                alert.slideDown();
                $('#sampahStatus').text('EVAKUASI');
            } else if (ketinggian < 50) {
                statusContainer.removeClass('normal danger').addClass('warning');
                statusContainer.html(`
                    <div class="status-icon"><i class="fas fa-exclamation-circle"></i></div>
                    <div class="status-text"><div>STATUS SISTEM: <span id="statusText">SAMPAH PENUH</span></div></div>
                    <div class="status-time" id="lastUpdate">Terakhir update: ${new Date().toLocaleTimeString('id-ID')}</div>
                `);
                alert.slideUp();
                $('#sampahStatus').text('PENUH - Segera Kosongkan!');
            } else {
                statusContainer.removeClass('danger warning').addClass('normal');
                statusContainer.html(`
                    <div class="status-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="status-text"><div>STATUS SISTEM: <span id="statusText">NORMAL</span></div></div>
                    <div class="status-time" id="lastUpdate">Terakhir update: ${new Date().toLocaleTimeString('id-ID')}</div>
                `);
                alert.slideUp();
                $('#sampahStatus').text('AMAN');
            }
        }

        // Update charts
        function updateCharts(data) {
            const time = new Date(data.waktu).toLocaleTimeString('id-ID');
            
            gasData.labels.push(time);
            gasData.values.push(data.gas);
            
            if (gasData.labels.length > maxDataPoints) {
                gasData.labels.shift();
                gasData.values.shift();
            }
            
            gasChart.update();
            
            ketinggianData.labels.push(time);
            ketinggianData.values.push(data.ketinggian);
            
            if (ketinggianData.labels.length > maxDataPoints) {
                ketinggianData.labels.shift();
                ketinggianData.values.shift();
            }
            
            ketinggianChart.update();
        }

        // Update user log
        function updateUserLog() {
            $.ajax({
                url: '/user-log/data',
                method: 'GET',
                success: function(data) {
                    let html = '';
                    let masukCount = 0;
                    
                    if (data.length === 0) {
                        html = '<tr><td colspan="6" class="text-center">Belum ada data</td></tr>';
                    } else {
                        data.forEach(function(log, index) {
                            const masuk = log.masuk ? new Date(log.masuk).toLocaleString('id-ID') : '-';
                            const keluar = log.keluar ? new Date(log.keluar).toLocaleString('id-ID') : '-';
                            const status = log.keluar ? 'keluar' : 'masuk';
                            const statusBadge = status === 'masuk' ? 
                                `<span class="badge-status badge-masuk">Masuk</span>` :
                                `<span class="badge-status badge-keluar">Keluar</span>`;
                            
                            if (!log.keluar) masukCount++;
                            
                            html += `<tr>
                                <td>${index + 1}</td>
                                <td>${log.nama}</td>
                                <td>${log.rfid}</td>
                                <td>${masuk}</td>
                                <td>${keluar}</td>
                                <td>${statusBadge}</td>
                            </tr>`;
                        });
                    }
                    
                    $('#userLogTable').html(html);
                    $('#userMasukCount').text(masukCount);
                    $('#userCount').text(masukCount);
                    
                    if (masukCount === 0) {
                        $('#userStatus').text('Tidak ada pengguna');
                    } else {
                        $('#userStatus').text(`${masukCount} pengguna aktif`);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching user log:', error);
                }
            });
        }

        // Initialize
        $(document).ready(function() {
            updateSensorData();
            updateUserLog();
            
            setInterval(updateSensorData, 3000);
            setInterval(updateUserLog, 5000);
        });
    </script>
</body>
</html>
