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
    <title>Modul Pembayaran - Ekspedisi Logistik</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            padding: 20px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 20px 25px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .header h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .header h1 span {
            font-weight: normal;
            font-size: 14px;
            background: rgba(255,255,255,0.2);
            padding: 2px 8px;
            border-radius: 20px;
            margin-left: 10px;
        }
        .header p {
            opacity: 0.85;
            font-size: 13px;
        }
        
        /* Stat Cards - Simple putih dengan border left */
        .stats-container {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
        }
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 15px 20px;
            flex: 1;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-left: 4px solid #2a5298;
        }
        .stat-card h3 {
            font-size: 13px;
            color: #666;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .stat-card .number {
            font-size: 28px;
            font-weight: bold;
            color: #1e3c72;
        }
        
        /* Toolbar - Search dan Tombol Tambah */
        .toolbar {
            background: white;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .search-box {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .search-box label {
            font-size: 14px;
            color: #333;
        }
        .search-box input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 260px;
            font-size: 13px;
        }
        .search-box button {
            padding: 8px 16px;
            background: #2a5298;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
        }
        .search-box button:hover {
            background: #1e3c72;
        }
        .btn-add {
            padding: 8px 20px;
            background: #2a5298;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 13px;
        }
        .btn-add:hover {
            background: #1e3c72;
        }
        
        /* Table - Rapi seperti gambar */
        .table-container {
            background: white;
            border-radius: 8px;
            overflow-x: auto;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        th {
            background: #f8f9fa;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 1px solid #e0e0e0;
        }
        td {
            padding: 10px 15px;
            border-bottom: 1px solid #f0f0f0;
            color: #555;
        }
        tr:hover {
            background: #fafafa;
        }
        
        /* Badge Status */
        .badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
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
            padding: 4px 10px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 11px;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 4px 10px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 11px;
        }
        
        /* Footer */
        .footer-stats {
            background: white;
            border-radius: 8px;
            padding: 12px 20px;
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #666;
            font-size: 13px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .footer-stats .total-data {
            font-weight: 600;
            color: #1e3c72;
        }
        
        /* Reset link style */
        .reset-link {
            color: #666;
            text-decoration: none;
            padding: 8px 12px;
            background: #e9ecef;
            border-radius: 4px;
            font-size: 13px;
        }
        .reset-link:hover {
            background: #dee2e6;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Header - Biru seperti gambar -->
    <div class="header">
        <h1>Modul PEMBAYARAN - Ekspedisi Logistik </h1>
        <p>Kelola transaksi pembayaran, validasi, dan monitoring status pembayaran</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-container">
        <div class="stat-card">
            <h3> Total Transaksi</h3>
            <div class="number"><?= $totalPembayaran ?></div>
        </div>
        <div class="stat-card">
            <h3> Lunas</h3>
            <div class="number">
                <?php 
                $lunas = $koneksi->query("SELECT COUNT(*) as total FROM pembayaran WHERE status_lunas='Lunas'")->fetch_assoc();
                echo $lunas['total'];
                ?>
            </div>
        </div>
        <div class="stat-card">
            <h3> Belum Lunas</h3>
            <div class="number">
                <?php 
                $belum = $koneksi->query("SELECT COUNT(*) as total FROM pembayaran WHERE status_lunas='Belum Lunas'")->fetch_assoc();
                echo $belum['total'];
                ?>
            </div>
        </div>
        <div class="stat-card">
            <h3>Total Tagihan</h3>
            <div class="number">
                <?php 
                $totalTagihan = $koneksi->query("SELECT SUM(total_tagihan) as total FROM pembayaran")->fetch_assoc();
                echo 'Rp ' . number_format($totalTagihan['total'] ?? 0, 0, ',', '.');
                ?>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="toolbar">
        <form class="search-box" method="GET">
            <label>Cari nama atau ID transaksi...</label>
            <input type="text" name="search" placeholder="Cari ID Transaksi atau Metode..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit"> Cari</button>
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
                        <td colspan="6" style="text-align: center; padding: 40px; color: #999;">
                            📭 Belum ada data pembayaran
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Footer Stats - Persis seperti gambar -->
    <div class="footer-stats">
        <div> <span class="total-data">Daftar Pembayaran</span></div>
        <div>Total Data: <strong><?= $totalPembayaran ?></strong></div>
    </div>
</div>
</body>
</html>