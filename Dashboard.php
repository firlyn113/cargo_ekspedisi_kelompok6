<?php

require_once 'config/koneksi.php';

$database = new Database();
$conn = $database->getConnection();

// Ambil statistik dari semua tabel
$stats = [
    'armada' => 0,
    'cargo' => 0,
    'staff' => 0,
    'pelanggan' => 0,
    'pembayaran' => 0,
    'transaksi' => 0
];

// Query statistik
$tables = ['armada', 'Kargo', 'staff', 'pelanggan', 'pembayaran', 'transaksi_pengiriman'];
foreach ($tables as $table) {
    $key = ($table === 'transaksi_pengiriman') ? 'transaksi' : $table;
    $result = $conn->query("SELECT COUNT(*) as total FROM $table");
    if ($result) {
        $row = $result->fetch_assoc();
        $stats[$key] = $row['total'];
    }
}

// Recent transactions
$recentTransaksi = [];
$query = "SELECT tp.*, 
                 s.nama_lengkap as nama_staff,
                 p.nama_lengkap as nama_pelanggan,
                 k.pengirim,
                 a.id_armada_code
          FROM transaksi_pengiriman tp
          LEFT JOIN staff s ON tp.id_staff = s.id_staff
          LEFT JOIN pelanggan p ON tp.id_pelanggan = p.id_pelanggan
          LEFT JOIN kargo k ON tp.id_kargo = k.id_kargo
          LEFT JOIN armada a ON tp.id_armada = a.id_armada
          ORDER BY tp.id_transaksi_pengiriman DESC
          LIMIT 8";

$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recentTransaksi[] = $row;
    }
}

// Status pengiriman count
$statusCount = [
    'Diproses' => 0,
    'Dikirim' => 0,
    'Selesai' => 0,
    'Batal' => 0
];

$result = $conn->query("SELECT status_pengiriman, COUNT(*) as count FROM transaksi_pengiriman GROUP BY status_pengiriman");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $statusCount[$row['status_pengiriman']] = $row['count'];
    }
}

// Staff count by type
$staffByType = [
    'SupirTruk' => 0,
    'AdminGudang' => 0,
    'KurirMotor' => 0
];

$result = $conn->query("SELECT jenis_staff, COUNT(*) as count FROM staff GROUP BY jenis_staff");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $staffByType[$row['jenis_staff']] = $row['count'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Ekspedisi Logistik Terpadu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --info-color: #1abc9c;
            --light-bg: #f8f9fa;
            --border-light: #e9ecef;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
        }

        /* NAVBAR */
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, #34495e 100%);
            box-shadow: 0 2px 8px rgba(44, 62, 80, 0.15);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-size: 1.3rem;
            font-weight: 700;
            color: white !important;
            letter-spacing: 0.5px;
            margin-left: 1rem;
        }

        .navbar-brand i {
            margin-right: 0.7rem;
        }

        /* MAIN CONTAINER */
        .main-container {
            min-height: calc(100vh - 60px);
            padding: 2rem 1rem;
        }

        /* HEADER SECTION */
        .page-header {
            margin-bottom: 2.5rem;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            font-size: 0.95rem;
            color: #7f8c8d;
        }

        /* STAT CARDS GRID */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.75rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid var(--border-light);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--secondary-color), var(--success-color));
        }

        .stat-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
            transform: translateY(-2px);
            border-color: var(--secondary-color);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stat-icon.icon-armada {
            background: rgba(52, 152, 219, 0.12);
            color: var(--secondary-color);
        }

        .stat-icon.icon-cargo {
            background: rgba(39, 174, 96, 0.12);
            color: var(--success-color);
        }

        .stat-icon.icon-staff {
            background: rgba(155, 89, 182, 0.12);
            color: #9b59b6;
        }

        .stat-icon.icon-pelanggan {
            background: rgba(243, 156, 18, 0.12);
            color: var(--warning-color);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.3rem;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #7f8c8d;
            font-weight: 500;
        }

        /* MODULE CARDS GRID */
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .module-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid var(--border-light);
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            display: flex;
            flex-direction: column;
            min-height: 320px;
        }

        .module-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
            border-color: var(--secondary-color);
        }

        .module-icon {
            width: 70px;
            height: 70px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            color: white;
        }

        .module-card.cargo .module-icon {
            background: linear-gradient(135deg, var(--success-color), #229954);
        }

        .module-card.staff .module-icon {
            background: linear-gradient(135deg, #9b59b6, #8e44ad);
        }

        .module-card.pelanggan .module-icon {
            background: linear-gradient(135deg, var(--warning-color), #e67e22);
        }

        .module-card.pembayaran .module-icon {
            background: linear-gradient(135deg, var(--info-color), #16a085);
        }

        .module-name {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .module-desc {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            flex-grow: 1;
        }

        .module-stats {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-light);
        }

        .module-stat {
            flex: 1;
        }

        .module-stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--secondary-color);
        }

        .module-stat-label {
            font-size: 0.8rem;
            color: #95a5a6;
            margin-top: 0.3rem;
        }

        .module-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .module-link:hover {
            gap: 0.8rem;
            color: var(--primary-color);
        }

        /* CHARTS SECTION */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid var(--border-light);
        }

        .chart-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* ACTIVITY SECTION */
        .activity-container {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid var(--border-light);
            margin-bottom: 2rem;
        }

        .activity-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .activity-item {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            border-bottom: 1px solid var(--border-light);
            align-items: flex-start;
            transition: all 0.2s ease;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-item:hover {
            background: var(--light-bg);
            border-radius: 8px;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: white;
            font-size: 1rem;
        }

        .activity-icon.success {
            background: linear-gradient(135deg, var(--success-color), #229954);
        }

        .activity-icon.warning {
            background: linear-gradient(135deg, var(--warning-color), #e67e22);
        }

        .activity-icon.info {
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
        }

        .activity-content {
            flex: 1;
        }

        .activity-text {
            color: var(--primary-color);
            font-weight: 500;
            font-size: 0.9rem;
            margin-bottom: 0.3rem;
        }

        .activity-time {
            font-size: 0.8rem;
            color: #95a5a6;
        }

        .activity-status {
            display: inline-block;
            padding: 0.3rem 0.7rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-diproses {
            background: rgba(243, 156, 18, 0.1);
            color: var(--warning-color);
        }

        .status-dikirim {
            background: rgba(52, 152, 219, 0.1);
            color: var(--secondary-color);
        }

        .status-selesai {
            background: rgba(39, 174, 96, 0.1);
            color: var(--success-color);
        }

        .status-batal {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        /* FOOTER */
        .footer {
            background: var(--primary-color);
            color: white;
            text-align: center;
            padding: 1.5rem;
            margin-top: 3rem;
            font-size: 0.9rem;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .main-container {
                padding: 1.5rem 1rem;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .modules-grid {
                grid-template-columns: 1fr;
            }

            .charts-grid {
                grid-template-columns: 1fr;
            }

            .stat-number {
                font-size: 1.5rem;
            }
        }

        /* SCROLLBAR */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--secondary-color);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color);
        }
    </style>
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-dark">
        <div class="container-fluid">
            <span class="navbar-brand">
                <i class="bi bi-box-seam"></i> Ekspedisi Logistik Terpadu
            </span>
            <div class="ms-auto" style="color: white; padding-right: 1rem;">
                <button class="btn btn-light btn-sm" title="Settings">
                    <i class="bi bi-gear"></i>
                </button>
                <button class="btn btn-light btn-sm ms-2" title="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <main class="main-container">
        <div class="container-lg">
            
            <!-- PAGE HEADER -->
            <div class="page-header">
                <h1 class="page-title">📊 Dashboard</h1>
                <p class="page-subtitle">Selamat datang di Sistem Manajemen Ekspedisi Logistik Terpadu</p>
            </div>

            <!-- STATISTICS -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon icon-armada">
                        <i class="bi bi-truck"></i>
                    </div>
                    <div class="stat-number"><?php echo $stats['armada']; ?></div>
                    <div class="stat-label">Total Armada</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon icon-cargo">
                        <i class="bi bi-box"></i>
                    </div>
                    <div class="stat-number"><?php echo $stats['cargo']; ?></div>
                    <div class="stat-label">Total Cargo</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon icon-staff">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stat-number"><?php echo $stats['staff']; ?></div>
                    <div class="stat-label">Total Staff</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon icon-pelanggan">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div class="stat-number"><?php echo $stats['pelanggan']; ?></div>
                    <div class="stat-label">Total Pelanggan</div>
                </div>
            </div>

            <!-- MODULE CARDS -->
            <div class="modules-grid">
                <a href="armada/" class="module-card">
                    <div class="module-icon">
                        <i class="bi bi-truck"></i>
                    </div>
                    <h3 class="module-name">Manajemen Armada</h3>
                    <p class="module-desc">Kelola seluruh armada kendaraan pengiriman</p>
                    <div class="module-stats">
                        <div class="module-stat">
                            <div class="module-stat-value"><?php echo $stats['armada']; ?></div>
                            <div class="module-stat-label">Aktif</div>
                        </div>
                    </div>
                    <span class="module-link">Buka Modul <i class="bi bi-arrow-right"></i></span>
                </a>

                <a href="cargo/" class="module-card cargo">
                    <div class="module-icon">
                        <i class="bi bi-box"></i>
                    </div>
                    <h3 class="module-name">Manajemen Cargo</h3>
                    <p class="module-desc">Atur dan pantau semua pengiriman cargo</p>
                    <div class="module-stats">
                        <div class="module-stat">
                            <div class="module-stat-value"><?php echo $stats['cargo']; ?></div>
                            <div class="module-stat-label">Terdaftar</div>
                        </div>
                    </div>
                    <span class="module-link">Buka Modul <i class="bi bi-arrow-right"></i></span>
                </a>

                <a href="staff/" class="module-card staff">
                    <div class="module-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <h3 class="module-name">Manajemen Staff</h3>
                    <p class="module-desc">Kelola data karyawan dan evaluasi kinerja</p>
                    <div class="module-stats">
                        <div class="module-stat">
                            <div class="module-stat-value"><?php echo $staffByType['SupirTruk'] ?? 0; ?></div>
                            <div class="module-stat-label">Sopir</div>
                        </div>
                    </div>
                    <span class="module-link">Buka Modul <i class="bi bi-arrow-right"></i></span>
                </a>

                <a href="pelanggan/" class="module-card pelanggan">
                    <div class="module-icon">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <h3 class="module-name">Manajemen Pelanggan</h3>
                    <p class="module-desc">Kelola data dan benefit pelanggan</p>
                    <div class="module-stats">
                        <div class="module-stat">
                            <div class="module-stat-value"><?php echo $stats['pelanggan']; ?></div>
                            <div class="module-stat-label">Pelanggan</div>
                        </div>
                    </div>
                    <span class="module-link">Buka Modul <i class="bi bi-arrow-right"></i></span>
                </a>

                <a href="pembayaran/" class="module-card pembayaran">
                    <div class="module-icon">
                        <i class="bi bi-credit-card"></i>
                    </div>
                    <h3 class="module-name">Manajemen Pembayaran</h3>
                    <p class="module-desc">Proses dan pantau semua transaksi pembayaran</p>
                    <div class="module-stats">
                        <div class="module-stat">
                            <div class="module-stat-value"><?php echo $stats['pembayaran']; ?></div>
                            <div class="module-stat-label">Transaksi</div>
                        </div>
                    </div>
                    <span class="module-link">Buka Modul <i class="bi bi-arrow-right"></i></span>
                </a>
            </div>

            <!-- CHARTS -->
            <div class="charts-grid">
                <div class="chart-container">
                    <h5 class="chart-title">
                        <i class="bi bi-pie-chart"></i> Status Pengiriman
                    </h5>
                    <canvas id="statusChart"></canvas>
                </div>

                <div class="chart-container">
                    <h5 class="chart-title">
                        <i class="bi bi-bar-chart"></i> Komposisi Staff
                    </h5>
                    <canvas id="staffChart"></canvas>
                </div>
            </div>

            <!-- RECENT ACTIVITY -->
            <div class="activity-container">
                <h5 class="activity-title">
                    <i class="bi bi-clock-history"></i> Aktivitas Terbaru
                </h5>

                <?php if (count($recentTransaksi) > 0): ?>
                    <?php foreach ($recentTransaksi as $transaksi): ?>
                        <div class="activity-item">
                            <div class="activity-icon <?php echo strtolower(str_replace(' ', '-', $transaksi['status_pengiriman'])); ?>">
                                <i class="bi bi-box"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-text">
                                    Pengiriman dari <strong><?php echo htmlspecialchars($transaksi['pengirim'] ?? 'N/A'); ?></strong>
                                    untuk <strong><?php echo htmlspecialchars($transaksi['nama_pelanggan'] ?? 'N/A'); ?></strong>
                                </div>
                                <div class="activity-time">
                                    Armada: <?php echo htmlspecialchars($transaksi['id_armada_code'] ?? 'N/A'); ?> | 
                                    Staff: <?php echo htmlspecialchars($transaksi['nama_staff'] ?? 'N/A'); ?>
                                </div>
                            </div>
                            <span class="activity-status status-<?php echo strtolower(str_replace(' ', '-', $transaksi['status_pengiriman'])); ?>">
                                <?php echo htmlspecialchars($transaksi['status_pengiriman']); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="activity-item">
                        <div class="activity-content">
                            <div class="activity-text">Belum ada aktivitas pengiriman</div>
                            <div class="activity-time">Mulai dengan menambahkan pengiriman baru</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>

    <!-- FOOTER -->
    <footer class="footer">
        <p>© 2026 Ekspedisi Logistik Terpadu - All Rights Reserved | Version 1.0.0</p>
    </footer>

    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

    <script>
        // STATUS CHART
        const statusCtx = document.getElementById('statusChart');
        if (statusCtx) {
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Diproses', 'Dikirim', 'Selesai', 'Batal'],
                    datasets: [{
                        data: [
                            <?php echo $statusCount['Diproses']; ?>,
                            <?php echo $statusCount['Dikirim']; ?>,
                            <?php echo $statusCount['Selesai']; ?>,
                            <?php echo $statusCount['Batal']; ?>
                        ],
                        backgroundColor: [
                            '#f39c12',
                            '#3498db',
                            '#27ae60',
                            '#e74c3c'
                        ],
                        borderColor: 'white',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                font: { size: 12, weight: '600' },
                                usePointStyle: true
                            }
                        }
                    }
                }
            });
        }

        // STAFF CHART
        const staffCtx = document.getElementById('staffChart');
        if (staffCtx) {
            new Chart(staffCtx, {
                type: 'bar',
                data: {
                    labels: ['Sopir Truk', 'Admin Gudang', 'Kurir Motor'],
                    datasets: [{
                        label: 'Jumlah Staff',
                        data: [
                            <?php echo $staffByType['SupirTruk']; ?>,
                            <?php echo $staffByType['AdminGudang']; ?>,
                            <?php echo $staffByType['KurirMotor']; ?>
                        ],
                        backgroundColor: [
                            '#3498db',
                            '#9b59b6',
                            '#e74c3c'
                        ],
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: { color: '#f0f0f0', drawBorder: false },
                            ticks: { font: { size: 12 } }
                        },
                        y: {
                            grid: { display: false },
                            ticks: { font: { size: 12 } }
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>