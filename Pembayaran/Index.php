<?php
require_once __DIR__ . '/../Config/koneksi.php';

$database = new Database();
$koneksi = $database->getConnection();

// Hitung total pembayaran
$totalQuery = "SELECT COUNT(*) as total FROM pembayaran";
$totalResult = $koneksi->query($totalQuery);
$totalData = $totalResult->fetch_assoc();
$totalPembayaran = $totalData['total'];

// Ambil data pembayaran
$search = isset($_GET['search']) ? $_GET['search'] : '';
if ($search != '') {
    $query = "SELECT * FROM pembayaran WHERE id_transaksi LIKE '%$search%' OR metode_pembayaran LIKE '%$search%'";
} else {
    $query = "SELECT * FROM pembayaran ORDER BY created_at DESC";
}
$result = $koneksi->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pembayaran - Cargo Ekspedisi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            display: flex;
            min-height: 100vh;
        }
        
        /* ===== SIDEBAR NAV ===== */
        .sidebar {
            width: 250px;
            background: #1e3c72;
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .sidebar .brand {
            padding: 0 20px 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        .sidebar .brand h2 {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .sidebar .brand p {
            font-size: 12px;
            opacity: 0.7;
            letter-spacing: 2px;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            padding: 0;
        }
        .sidebar ul li a {
            display: block;
            padding: 12px 20px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.3s;
            font-size: 14px;
            border-left: 3px solid transparent;
        }
        .sidebar ul li a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: #ffc107;
        }
        .sidebar ul li a.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: #ffc107;
        }
        .sidebar ul li a .icon {
            margin-right: 10px;
        }
        
        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 25px 30px;
        }
        
        /* Header */
        .page-header {
            margin-bottom: 25px;
        }
        .page-header h1 {
            font-size: 26px;
            color: #1e3c72;
            font-weight: 600;
        }
        .page-header p {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        
        /* Toolbar - Tombol Tambah */
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .btn-add {
            background: #1e3c72;
            color: white;
            padding: 10px 22px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: background 0.3s;
        }
        .btn-add:hover {
            background: #2a5298;
        }
        .search-box {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .search-box input {
            padding: 9px 14px;
            border: 1px solid #ddd;
            border-radius: 6px;
            width: 280px;
            font-size: 13px;
            background: white;
        }
        .search-box input:focus {
            outline: none;
            border-color: #1e3c72;
            box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.1);
        }
        .search-box button {
            padding: 9px 18px;
            background: #1e3c72;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
        }
        .search-box button:hover {
            background: #2a5298;
        }
        .reset-link {
            color: #666;
            text-decoration: none;
            padding: 9px 14px;
            background: #f0f0f0;
            border-radius: 6px;
            font-size: 13px;
        }
        .reset-link:hover {
            background: #e0e0e0;
        }
        
        /* ===== TABLE ===== */
        .table-container {
            background: white;
            border-radius: 10px;
            overflow-x: auto;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            border: 1px solid #e9ecef;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        th {
            background: #f8f9fa;
            padding: 13px 18px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e9ecef;
        }
        td {
            padding: 12px 18px;
            border-bottom: 1px solid #f0f0f0;
            color: #555;
        }
        tr:hover {
            background: #f8f9fa;
        }
        
        /* Badge Status */
        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
        .badge-lunas {
            background: #d4edda;
            color: #155724;
        }
        .badge-belum {
            background: #f8d7da;
            color: #721c24;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        .btn-edit {
            background: #ffc107;
            color: #333;
            padding: 4px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px;
        }
        .btn-edit:hover {
            background: #e0a800;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 4px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px;
        }
        .btn-delete:hover {
            background: #c82333;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #999;
        }
        .empty-state .icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .empty-state p {
            font-size: 16px;
        }
        
        /* Footer */
        .footer-info {
            margin-top: 20px;
            background: white;
            border-radius: 10px;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #666;
            font-size: 13px;
            border: 1px solid #e9ecef;
        }
        .footer-info strong {
            color: #1e3c72;
        }
    </style>
</head>
<body>

<!-- ===== SIDEBAR ===== -->
<div class="sidebar">
    <div class="brand">
        <h2> Cargo Ekspedisi</h2>
        <p>LOGISTIK SYSTEM</p>
    </div>
    <ul>
        <li><a href="../dashboard.php"><span class="icon">📊</span> Dashboard</a></li>
        <li><a href="../Armada/index.php"><span class="icon">🚛</span> Armada</a></li>
        <li><a href="../Kargo/index.php"><span class="icon">📦</span> Kargo</a></li>
        <li><a href="../Pelanggan/index.php"><span class="icon">👤</span> Pelanggan</a></li>
        <li><a href="index.php" class="active"><span class="icon">💰</span> Pembayaran</a></li>
        <li><a href="../Staff/index.php"><span class="icon">👨‍💼</span> Staff</a></li>
    </ul>
</div>

<!-- ===== MAIN CONTENT ===== -->
<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <h1> Manajemen Pembayaran</h1>
        <p>Kelola transaksi pembayaran, validasi, dan monitoring status pembayaran</p>
    </div>

    <!-- Toolbar -->
    <div class="toolbar">
        <form class="search-box" method="GET">
            <input type="text" name="search" placeholder="Cari ID Transaksi atau Metode..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">🔍 Cari</button>
            <?php if ($search != ''): ?>
                <a href="index.php" class="reset-link">Reset</a>
            <?php endif; ?>
        </form>
        <a href="tambah.php" class="btn-add">+ Tambah Pembayaran Baru</a>
    </div>

    <!-- Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID Transaksi</th>
                    <th>Total Tagihan</th>
                    <th>Status</th>
                    <th>Metode Pembayaran</th>
                    <th>Waktu Pembayaran</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['id_transaksi']) ?></strong></td>
                            <td>Rp <?= number_format($row['total_tagihan'], 0, ',', '.') ?></td>
                            <td>
                                <span class="badge <?= $row['status_lunas'] == 'Lunas' ? 'badge-lunas' : 'badge-belum' ?>">
                                    <?= $row['status_lunas'] ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                $icon = '';
                                if ($row['metode_pembayaran'] == 'CashOnDelivery') $icon = '💵';
                                elseif ($row['metode_pembayaran'] == 'TransferBank') $icon = '🏦';
                                elseif ($row['metode_pembayaran'] == 'EWallet') $icon = '📱';
                                echo $icon . ' ' . htmlspecialchars($row['metode_pembayaran'] ?? '-');
                                ?>
                            </td>
                            <td><?= $row['waktu_pembayaran'] ? date('d/m/Y H:i', strtotime($row['waktu_pembayaran'])) : '-' ?></td>
                            <td class="action-buttons">
                                <a href="edit.php?id=<?= $row['id_pembayaran'] ?>" class="btn-edit">✏️ Edit</a>
                                <a href="hapus.php?id=<?= $row['id_pembayaran'] ?>" class="btn-delete" onclick="return confirm('Yakin hapus data ini?')">🗑️ Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <div class="icon">📭</div>
                                <p>Belum ada data pembayaran</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer-info">
        <span> <strong>Daftar Pembayaran</strong></span>
        <span>Total Data: <strong><?= $totalPembayaran ?></strong></span>
    </div>
</div>

</body>
</html>