<?php
/**
 * dashboard.php
 * Dashboard utama sistem manajemen ekspedisi logistik
 * Menampilkan overview dari semua modul (Armada, Cargo, Staff, Pelanggan, Pembayaran)
 */

require_once 'koneksi.php';

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
$tables = ['armada', 'cargo', 'staff', 'pelanggan', 'pembayaran', 'transaksi_pengiriman'];
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
          LIMIT 10";

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
            --light-color: #ecf0f1;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        /* NAVBAR */
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 4px 20px rgba(44, 62, 80, 0.15);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: white !important;
            letter-spacing: 0.5px;
        }

        .navbar-brand i {
            margin-right: 0.5rem;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        /* SIDEBAR */
        .sidebar {
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.08);
            min-height: 100vh;
            padding: 2rem 0;
            position: fixed;
            width: 250px;
            left: -250px;
            top: 60px;
            transition: all 0.3s ease;
            z-index: 999;
        }

        .sidebar.active {
            left: 0;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
        }

        .sidebar-menu li {
            margin: 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: var(--primary-color);
            text-decoration: none;
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .sidebar-menu a:hover {
            background: var(--light-color);
            border-left-color: var(--secondary-color);
            color: var(--secondary-color);
            padding-left: 2rem;
        }

        .sidebar-menu i {
            width: 25px;
            margin-right: 1rem;
            text-align: center;
        }

        /* MAIN CONTENT */
        .main-content {
            margin-left: 0;
            transition: all 0.3s ease;
            padding-top: 80px;
            padding-left: 2rem;
            padding-right: 2rem;
            padding-bottom: 2rem;
        }

        .main-content.with-sidebar {
            margin-left: 250px;
        }

        /* HEADER SECTION */
        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 3rem 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(44, 62, 80, 0.2);
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: 0.5px;
        }

        .page-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* STAT CARDS */
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--secondary-color), var(--success-color));
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stat-icon.armada {
            background: rgba(52, 152, 219, 0.1);
            color: var(--secondary-color);
        }

        .stat-icon.cargo {
            background: rgba(39, 174, 96, 0.1);
            color: var(--success-color);
        }

        .stat-icon.staff {
            background: rgba(155, 89, 182, 0.1);
            color: #9b59b6;
        }

        .stat-icon.pelanggan {
            background: rgba(243, 156, 18, 0.1);
            color: var(--warning-color);
        }

        .stat-icon.pembayaran {
            background: rgba(26, 188, 156, 0.1);
            color: var(--info-color);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1rem;
            color: #7f8c8d;
            font-weight: 500;
        }

        .stat-link {
            display: inline-block;
            margin-top: 1rem;
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .stat-link:hover {
            color: var(--primary-color);
            margin-left: 0.5rem;
        }

        /* MODULE CARDS */
        .module-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border: none;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .module-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(52, 152, 219, 0.1) 0%, transparent 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .module-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 35px rgba(0,0,0,0.15);
        }

        .module-card:hover::before {
            opacity: 1;
        }

        .module-icon {
            width: 80px;
            height: 80px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-bottom: 1rem;
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
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .module-desc {
            color: #7f8c8d;
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
        }

        .module-stats {
            display: flex;
            gap: 2rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #ecf0f1;
        }

        .module-stat {
            flex: 1;
        }

        .module-stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--secondary-color);
        }

        .module-stat-label {
            font-size: 0.9rem;
            color: #95a5a6;
        }

        .module-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .module-link:hover {
            gap: 1rem;
            color: var(--primary-color);
        }

        /* CHART SECTION */
        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .chart-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* RECENT ACTIVITY */
        .activity-container {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .activity-title {
            font-size: 1.3rem;
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
            border-bottom: 1px solid #ecf0f1;
            align-items: flex-start;
            transition: all 0.3s ease;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-item:hover {
            background: #f9f9f9;
            border-radius: 8px;
            padding-left: 1.5rem;
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
            font-size: 1.2rem;
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
            margin-bottom: 0.3rem;
        }

        .activity-time {
            font-size: 0.85rem;
            color: #95a5a6;
        }

        .activity-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
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

        /* TOGGLE SIDEBAR */
        .toggle-sidebar {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-right: 1rem;
        }

        .toggle-sidebar:hover {
            transform: rotate(90deg);
        }

        /* RESPONSIVE */
        @media (max-width: 992px) {
            .sidebar {
                width: 200px;
                left: -200px;
            }

            .main-content.with-sidebar {
                margin-left: 0;
            }

            .page-title {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 2rem 1rem;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .stat-number {
                font-size: 2rem;
            }

            .module-card {
                padding: 1.5rem;
            }

            .module-icon {
                width: 60px;
                height: 60px;
                font-size: 2rem;
            }

            .main-content {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }

        /* FOOTER */
        .footer {
            background: var(--primary-color);
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 3rem;
        }

        .footer-text {
            margin: 0.5rem 0;
        }

        /* BADGES */
        .badge-count {
            display: inline-block;
            background: var(--danger-color);
            color: white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            margin-left: 0.5rem;
        }

        /* SMOOTH SCROLLBAR */
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
            <button class="toggle-sidebar" id="toggleBtn">
                <i class="bi bi-list"></i>
            </button>
            <span class="navbar-brand">
                <i class="bi bi-box-seam"></i> Ekspedisi Logistik Terpadu
            </span>
            <div class="ms-auto">
                <button class="btn btn-light btn-sm" title="Settings">
                    <i class="bi bi-gear"></i>
                </button>
                <button class="btn btn-light btn-sm ms-2" title="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <ul class="sidebar-menu">
            <li><a href="#dashboard" class="active">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a></li>
            <li><a href="armada/">
                <i class="bi bi-truck"></i> Armada
            </a></li>
            <li><a href="cargo/">
                <i class="bi bi-box"></i> Cargo
            </a></li>
            <li><a href="staff/">
                <i class="bi bi-people"></i> Staff
            </a></li>
            <li><a href="pelanggan/">
                <i class="bi bi-person-badge"></i> Pelanggan
            </a></li>
            <li><a href="pembayaran/">
                <i class="bi bi-credit-card"></i> Pembayaran
            </a></li>
            <li style="margin-top: 2rem; padding: 0 1rem; color: #95a5a6; font-size: 0.85rem;">
                TOOLS
            </li>
            <li><a href="javascript:void(0)">
                <i class="bi bi-graph-up"></i> Reports
            </a></li>
            <li><a href="javascript:void(0)">
                <i class="bi bi-file-text"></i> Documentation
            </a></li>
        </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content" id="mainContent">
        
        <!-- PAGE HEADER -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="bi bi-speedometer2"></i> Dashboard
            </h1>
            <p class="page-subtitle">Selamat datang di Sistem Manajemen Ekspedisi Logistik Terpadu</p>
        </div>

        <!-- STATISTICS ROW -->
        <div class="row mb-4">
            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon armada">
                        <i class="bi bi-truck"></i>
                    </div>
                    <div class="stat-number"><?php echo $stats['armada']; ?></div>
                    <div class="stat-label">Total Armada</div>
                    <a href="armada/" class="stat-link">
                        Lihat Detail <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon cargo">
                        <i class="bi bi-box"></i>
                    </div>
                    <div class="stat-number"><?php echo $stats['cargo']; ?></div>
                    <div class="stat-label">Total Cargo</div>
                    <a href="cargo/" class="stat-link">
                        Lihat Detail <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon staff">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stat-number"><?php echo $stats['staff']; ?></div>
                    <div class="stat-label">Total Staff</div>
                    <a href="staff/" class="stat-link">
                        Lihat Detail <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon pelanggan">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div class="stat-number"><?php echo $stats['pelanggan']; ?></div>
                    <div class="stat-label">Total Pelanggan</div>
                    <a href="pelanggan/" class="stat-link">
                        Lihat Detail <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- MODULE CARDS -->
        <div class="row mb-4">
            <div class="col-lg-6 mb-3">
                <a href="armada/" class="module-card">
                    <div class="module-icon">
                        <i class="bi bi-truck"></i>
                    </div>
                    <h3 class="module-name">Manajemen Armada</h3>
                    <p class="module-desc">Kelola seluruh armada kendaraan pengiriman</p>
                    <div class="module-stats">
                        <div class="module-stat">
                            <div class="module-stat-value"><?php echo $stats['armada']; ?></div>
                            <div class="module-stat-label">Armada Aktif</div>
                        </div>
                    </div>
                    <span class="module-link">
                        Buka Modul <i class="bi bi-arrow-right"></i>
                    </span>
                </a>
            </div>

            <div class="col-lg-6 mb-3">
                <a href="cargo/" class="module-card cargo">
                    <div class="module-icon">
                        <i class="bi bi-box"></i>
                    </div>
                    <h3 class="module-name">Manajemen Cargo</h3>
                    <p class="module-desc">Atur dan pantau semua pengiriman cargo</p>
                    <div class="module-stats">
                        <div class="module-stat">
                            <div class="module-stat-value"><?php echo $stats['cargo']; ?></div>
                            <div class="module-stat-label">Cargo Terdaftar</div>
                        </div>
                    </div>
                    <span class="module-link">
                        Buka Modul <i class="bi bi-arrow-right"></i>
                    </span>
                </a>
            </div>

            <div class="col-lg-6 mb-3">
                <a href="staff/" class="module-card staff">
                    <div class="module-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <h3 class="module-name">Manajemen Staff</h3>
                    <p class="module-desc">Kelola data karyawan dan evaluasi kinerja</p>
                    <div class="module-stats">
                        <div class="module-stat">
                            <div class="module-stat-value"><?php echo $staffByType['SupirTruk']; ?></div>
                            <div class="module-stat-label">Sopir</div>
                        </div>
                        <div class="module-stat">
                            <div class="module-stat-value"><?php echo $staffByType['AdminGudang']; ?></div>
                            <div class="module-stat-label">Admin</div>
                        </div>
                        <div class="module-stat">
                            <div class="module-stat-value"><?php echo $staffByType['KurirMotor']; ?></div>
                            <div class="module-stat-label">Kurir</div>
                        </div>
                    </div>
                    <span class="module-link">
                        Buka Modul <i class="bi bi-arrow-right"></i>
                    </span>
                </a>
            </div>

            <div class="col-lg-6 mb-3">
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
                    <span class="module-link">
                        Buka Modul <i class="bi bi-arrow-right"></i>
                    </span>
                </a>
            </div>
        </div>

        <!-- CHARTS ROW -->
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="chart-container">
                    <h5 class="chart-title">
                        <i class="bi bi-pie-chart"></i> Status Pengiriman
                    </h5>
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="chart-container">
                    <h5 class="chart-title">
                        <i class="bi bi-bar-chart"></i> Komposisi Staff
                    </h5>
                    <canvas id="staffChart"></canvas>
                </div>
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

    </main>

    <!-- FOOTER -->
    <footer class="footer">
        <p class="footer-text">
            <i class="bi bi-c-circle"></i> 2026 Ekspedisi Logistik Terpadu - All Rights Reserved
        </p>
        <p class="footer-text">
            <small>Version 1.0.0 | Last Updated: Juni 2026</small>
        </p>
    </footer>

    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

    <script>
        // Toggle Sidebar
        const toggleBtn = document.getElementById('toggleBtn');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('with-sidebar');
        });

        // Close sidebar when menu item clicked
        const sidebarLinks = sidebar.querySelectorAll('a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 992) {
                    sidebar.classList.remove('active');
                    mainContent.classList.remove('with-sidebar');
                }
            });
        });

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
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                font: { size: 13, weight: '600' },
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
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            ticks: { callback: value => value },
                            grid: { color: '#ecf0f1' }
                        },
                        y: {
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        // Add animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.stat-card, .module-card, .chart-container, .activity-container').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'all 0.6s ease';
            observer.observe(el);
        });
    </script>
</body>
</html>