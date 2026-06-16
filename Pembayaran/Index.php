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
            background: #f4f6f9;
            padding: 20px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        /* Header */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px 30px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 8px;
        }
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        /* Card Stats */
        .stats-container {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
        }
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px 25px;
            flex: 1;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 5px solid #667eea;
        }
        .stat-card h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }
        /* Toolbar */
        .toolbar {
            background: white;
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .search-box {
            display: flex;
            gap: 10px;
        }
        .search-box input {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            width: 300px;
            font-size: 14px;
        }
        .search-box button {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .btn-add {
            padding: 10px 25px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
        }
        .btn-add:hover {
            background: #218838;
        }
        /* Table */
        .table-container {
            background: white;
            border-radius: 12px;
            overflow-x: auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e9ecef;
        }
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
            color: #555;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        .badge-lunas {
            background: #d4edda;
            color: #155724;
        }
        .badge-belum {
            background: #f8d7da;
            color: #721c24;
        }
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        .btn-edit {
            background: #ffc107;
            color: #333;
            padding: 5px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 5px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
        }
        .footer-stats {
            background: white;
            border-radius: 12px;
            padding: 15px 20px;
            margin-top: 20px;
            text-align: center;
            color: #666;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Header -->
    <div class="header">
        <h1>💰 Modul PEMBAYARAN - Ekspedisi Logistik</h1>
        <p>Kelola transaksi pembayaran, validasi, dan monitoring status pembayaran</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-container">
        <div class="stat-card">
            <h3>💰 Total Transaksi</h3>
            <div class="number"><?= $totalPembayaran ?></div>
        </div>
        <div class="stat-card">
            <h3>✅ Lunas</h3>
            <div class="number">
                <?php 
                $lunas = $koneksi->query("SELECT COUNT(*) as total FROM pembayaran WHERE status_lunas='Lunas'")->fetch_assoc();
                echo $lunas['total'];
                ?>
            </div>
        </div>
        <div class="stat-card">
            <h3>⏳ Belum Lunas</h3>
            <div class="number">
                <?php 
                $belum = $koneksi->query("SELECT COUNT(*) as total FROM pembayaran WHERE status_lunas='Belum Lunas'")->fetch_assoc();
                echo $belum['total'];
                ?>
            </div>
        </div>
        <div class="stat-card">
            <h3>💵 Total Tagihan (Rp)</h3>
            <div class="number">
                <?php 
                $totalTagihan = $koneksi->query("SELECT SUM(total_tagihan) as total FROM pembayaran")->fetch_assoc();
                echo number_format($totalTagihan['total'] ?? 0, 0, ',', '.');
                ?>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="toolbar">
        <form class="search-box" method="GET">
            <input type="text" name="search" placeholder="Cari ID Transaksi atau Metode Pembayaran..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">🔍 Cari</button>
            <?php if ($search != ''): ?>
                <a href="index.php" style="padding: 10px 15px; background: #6c757d; color: white; border-radius: 8px; text-decoration: none;">Reset</a>
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
                            <td><?= htmlspecialchars($row['id_transaksi']) ?></td>
                            <td>Rp <?= number_format($row['total_tagihan'], 0, ',', '.') ?></td>
                            <td>
                                <span class="badge <?= $row['status_lunas'] == 'Lunas' ? 'badge-lunas' : 'badge-belum' ?>">
                                    <?= $row['status_lunas'] ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($row['metode_pembayaran'] ?? '-') ?></td>
                            <td><?= $row['waktu_pembayaran'] ? date('d/m/Y H:i', strtotime($row['waktu_pembayaran'])) : '-' ?></td>
                            <td class="action-buttons">
                                <a href="edit.php?id=<?= $row['id_pembayaran'] ?>" class="btn-edit">✏️ Edit</a>
                                <a href="hapus.php?id=<?= $row['id_pembayaran'] ?>" class="btn-delete" onclick="return confirm('Yakin hapus data ini?')">🗑️ Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px;">📭 Belum ada data pembayaran</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Footer Stats -->
    <div class="footer-stats">
        📊 Menampilkan <?= $result->num_rows ?> dari <?= $totalPembayaran ?> total data pembayaran
    </div>
</div>
</body>
</html>