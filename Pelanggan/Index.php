<?php
require_once '../Config/koneksi.php';
require_once 'PelangganRetail.php';
require_once 'PelangganVIP.php';
require_once 'MitraKorporat.php';

// Buat koneksi dari class Database
$database = new Database();
$koneksi = $database->getConnection();

// Start session for messages
session_start();

// Handle form submissions
$message = '';
$messageType = '';

// Create new pelanggan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'create') {
        $jenis = $_POST['jenis_pelanggan'];
        $kode = $_POST['id_pelanggan_code'];
        $nama = $_POST['nama_lengkap'];
        
        try {
            switch ($jenis) {
                case 'Retail':
                    $pelanggan = new PelangganRetail($kode, $nama, $_POST['promo_voucher'] ?? null, $_POST['batas_berat_max'] ?? 50);
                    break;
                case 'VIP':
                    $pelanggan = new PelangganVIP($kode, $nama, true, $_POST['personal_assistant'] ?? null);
                    break;
                case 'MitraKorporat':
                    $pelanggan = new MitraKorporat($kode, $nama, $_POST['npwp_perusahaan'], $_POST['batas_tempo_pembayaran'] ?? null);
                    break;
                default:
                    throw new Exception("Jenis pelanggan tidak valid");
            }
            
            if ($pelanggan->saveToDatabase($koneksi)) {
                $message = "Pelanggan berhasil ditambahkan!";
                $messageType = "success";
            } else {
                $message = "Gagal menambahkan pelanggan!";
                $messageType = "danger";
            }
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = "danger";
        }
    }
    
    // Calculate discount
    if ($_POST['action'] == 'calculate_discount') {
        $id_pelanggan = $_POST['id_pelanggan'];
        $total_biaya = $_POST['total_biaya'];
        
        $sql = "SELECT * FROM pelanggan WHERE id_pelanggan = $id_pelanggan";
        $result = $koneksi->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $data = $result->fetch_assoc();
            
            switch ($data['jenis_pelanggan']) {
                case 'Retail':
                    $pelanggan = new PelangganRetail($data['id_pelanggan_code'], $data['nama_lengkap'], $data['promo_voucher'], $data['batas_berat_max']);
                    break;
                case 'VIP':
                    $pelanggan = new PelangganVIP($data['id_pelanggan_code'], $data['nama_lengkap'], $data['akses_layanan_prioritas'], $data['personal_assistant']);
                    break;
                case 'MitraKorporat':
                    $pelanggan = new MitraKorporat($data['id_pelanggan_code'], $data['nama_lengkap'], $data['npwp_perusahaan'], $data['batas_tempo_pembayaran']);
                    break;
            }
            
            $pelanggan->setTotalTransaksiBulanIni($data['total_transaksi_bulan_ini']);
            $pelanggan->setPoinReward($data['poin_reward']);
            
            $diskon = $pelanggan->hitungDiskonPengiriman($total_biaya);
            $total_akhir = $total_biaya - $diskon;
            $benefits = $pelanggan->dapatkanBenefitTambahan();
            
            // Store in session for display
            $_SESSION['calculation'] = [
                'nama' => $pelanggan->getNamaLengkap(),
                'jenis' => $pelanggan->getJenisPelanggan(),
                'total_awal' => $total_biaya,
                'diskon' => $diskon,
                'total_akhir' => $total_akhir,
                'benefits' => $benefits
            ];
            
            header('Location: index.php?show_calculation=1');
            exit();
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $sql = "DELETE FROM pelanggan WHERE id_pelanggan = $id";
    if ($koneksi->query($sql)) {
        $message = "Pelanggan berhasil dihapus!";
        $messageType = "success";
    } else {
        $message = "Gagal menghapus pelanggan!";
        $messageType = "danger";
    }
}

// Get all customers
$sql = "SELECT * FROM pelanggan ORDER BY created_at DESC";
$result = $koneksi->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Pelanggan - Ekspedisi Logistik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
        }
        
        body {
            background-color: #ecf0f1;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 600;
            font-size: 1.3rem;
        }
        
        .container-fluid {
            background-color: #ecf0f1;
        }
        
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 8px 8px 0 0;
            border: none;
            padding: 1.5rem;
        }
        
        .btn-primary {
            background: var(--secondary-color);
            border: none;
            border-radius: 5px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
        }
        
        .table thead {
            background-color: #f8f9fa;
            border-bottom: 2px solid var(--secondary-color);
        }
        
        .table-hover tbody tr:hover {
            background-color: #f0f7ff;
        }
        
        .badge {
            padding: 0.5rem 0.8rem;
            font-weight: 500;
            border-radius: 4px;
        }
        
        .form-control, .form-select {
            border-radius: 5px;
            border: 1px solid #ddd;
            padding: 0.7rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .alert {
            border-radius: 6px;
            border: none;
            padding: 1rem 1.5rem;
        }
        
        .benefit-list {
            list-style: none;
            padding-left: 0;
        }
        .benefit-list li {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        
        .discount-badge {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .bg-dark {
            background-color: var(--primary-color) !important;
        }
        
        .bg-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%) !important;
        }
        
        .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .btn-info {
            background: #1abc9c;
            border: none;
            color: white;
        }
        
        .btn-info:hover {
            background: #16a085;
            color: white;
        }
        
        .btn-danger {
            background: var(--danger-color);
            border: none;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .btn-success {
            background: var(--success-color);
            border: none;
        }
        
        .btn-success:hover {
            background: #219a52;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 bg-dark min-vh-100 p-0">
                <div class="bg-primary p-3 text-white text-center">
                    <h4>Cargo Ekspedisi</h4>
                    <small>Logistik System</small>
                </div>
                <nav class="nav flex-column mt-3">
                    <a class="nav-link text-white" href="../Dashboard.php">
                        <i class="fas fa-dashboard"></i> Dashboard
                    </a>
                    <a class="nav-link text-white" href="../Armada/index.php">
                        <i class="fas fa-truck"></i> Armada
                    </a>
                    <a class="nav-link text-white" href="../Cargo/index.php">
                        <i class="fas fa-box"></i> Cargo
                    </a>
                    <a class="nav-link bg-primary text-white" href="index.php">
                        <i class="fas fa-users"></i> Pelanggan
                    </a>
                    <a class="nav-link text-white" href="../Pembayaran/index.php">
                        <i class="fas fa-credit-card"></i> Pembayaran
                    </a>
                    <a class="nav-link text-white" href="../Staff/index.php">
                        <i class="fas fa-user-tie"></i> Staff
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <h2 class="mb-4"><i class="fas fa-users"></i> Manajemen Pelanggan</h2>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                        <?= $message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['calculation']) && isset($_GET['show_calculation'])): ?>
                    <?php $calc = $_SESSION['calculation']; ?>
                    <div class="card mb-4 border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-calculator"></i> Hasil Perhitungan Diskon</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Nama Pelanggan:</strong> <?= htmlspecialchars($calc['nama']) ?></p>
                                    <p><strong>Jenis Pelanggan:</strong> 
                                        <span class="badge bg-primary"><?= htmlspecialchars($calc['jenis']) ?></span>
                                    </p>
                                    <p><strong>Total Biaya Awal:</strong> Rp <?= number_format($calc['total_awal'], 0, ',', '.') ?></p>
                                    <p><strong>Diskon:</strong> 
                                        <span class="badge discount-badge">Rp <?= number_format($calc['diskon'], 0, ',', '.') ?></span>
                                    </p>
                                    <h4><strong>Total Akhir:</strong> Rp <?= number_format($calc['total_akhir'], 0, ',', '.') ?></h4>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-gift"></i> Benefit yang Didapatkan:</h6>
                                    <ul class="benefit-list">
                                        <?php foreach ($calc['benefits'] as $benefit): ?>
                                            <li><?= htmlspecialchars($benefit) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php unset($_SESSION['calculation']); ?>
                <?php endif; ?>
                
                <!-- Button to trigger modal -->
                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addPelangganModal">
                    <i class="fas fa-plus"></i> Tambah Pelanggan Baru
                </button>
                
                <!-- Customer List -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Daftar Pelanggan</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nama Lengkap</th>
                                        <th>Jenis</th>
                                        <th>Total Transaksi Bulan Ini</th>
                                        <th>Poin Reward</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result && $result->num_rows > 0): ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['id_pelanggan_code']) ?></td>
                                            <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                            <td>
                                                <span class="badge <?= $row['jenis_pelanggan'] == 'VIP' ? 'bg-warning' : ($row['jenis_pelanggan'] == 'MitraKorporat' ? 'bg-info' : 'bg-secondary') ?>">
                                                    <?= htmlspecialchars($row['jenis_pelanggan']) ?>
                                                </span>
                                            </td>
                                            <td>Rp <?= number_format($row['total_transaksi_bulan_ini'], 0, ',', '.') ?></td>
                                            <td><?= $row['poin_reward'] ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#calculateModal<?= $row['id_pelanggan'] ?>">
                                                    <i class="fas fa-calculator"></i> Hitung Diskon
                                                </button>
                                                <a href="?delete=<?= $row['id_pelanggan'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </a>
                                            </td>
                                        </tr>
                                        
                                        <!-- Calculate Modal for each customer -->
                                        <div class="modal fade" id="calculateModal<?= $row['id_pelanggan'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Hitung Diskon - <?= htmlspecialchars($row['nama_lengkap']) ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="action" value="calculate_discount">
                                                            <input type="hidden" name="id_pelanggan" value="<?= $row['id_pelanggan'] ?>">
                                                            <div class="mb-3">
                                                                <label class="form-label">Total Biaya Pengiriman (Rp)</label>
                                                                <input type="number" class="form-control" name="total_biaya" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-primary">Hitung Diskon</button>
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">Belum ada data pelanggan</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Customer Modal -->
    <div class="modal fade" id="addPelangganModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Pelanggan Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis Pelanggan</label>
                                <select class="form-select" name="jenis_pelanggan" id="jenisPelanggan" required onchange="toggleFields()">
                                    <option value="Retail">Retail</option>
                                    <option value="VIP">VIP</option>
                                    <option value="MitraKorporat">Mitra Korporat</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kode Pelanggan</label>
                                <input type="text" class="form-control" name="id_pelanggan_code" required placeholder="ex: PLG001">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Nama Lengkap / Perusahaan</label>
                                <input type="text" class="form-control" name="nama_lengkap" required>
                            </div>
                            
                            <!-- Retail Fields -->
                            <div id="retailFields" class="col-md-12">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Promo Voucher</label>
                                        <input type="text" class="form-control" name="promo_voucher" placeholder="VOUCHER10">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Batas Berat Maksimal (kg)</label>
                                        <input type="number" class="form-control" name="batas_berat_max" value="50">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- VIP Fields -->
                            <div id="vipFields" class="col-md-12" style="display:none;">
                                <div class="mb-3">
                                    <label class="form-label">Personal Assistant</label>
                                    <input type="text" class="form-control" name="personal_assistant" placeholder="Nama Personal Assistant">
                                </div>
                            </div>
                            
                            <!-- Corporate Fields -->
                            <div id="corporateFields" class="col-md-12" style="display:none;">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">NPWP Perusahaan</label>
                                        <input type="text" class="form-control" name="npwp_perusahaan" placeholder="00.000.000.0-000.000">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Batas Tempo Pembayaran</label>
                                        <input type="date" class="form-control" name="batas_tempo_pembayaran">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleFields() {
            var jenis = document.getElementById('jenisPelanggan').value;
            document.getElementById('retailFields').style.display = jenis == 'Retail' ? 'block' : 'none';
            document.getElementById('vipFields').style.display = jenis == 'VIP' ? 'block' : 'none';
            document.getElementById('corporateFields').style.display = jenis == 'MitraKorporat' ? 'block' : 'none';
        }
        
        // Initialize
        toggleFields();
    </script>
</body>
</html>