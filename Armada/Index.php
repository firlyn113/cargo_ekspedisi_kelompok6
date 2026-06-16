<?php
require_once '../Config/koneksi.php';

// Buat koneksi dari class Database
$database = new Database();
$koneksi = $database->getConnection();

// Start session for messages
session_start();

// Handle form submissions
$message = '';
$messageType = '';

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $sql = "DELETE FROM armada WHERE id_armada = $id";
    if ($koneksi->query($sql)) {
        $message = "Armada berhasil dihapus!";
        $messageType = "success";
        header('Location: index.php?message=' . urlencode($message) . '&type=' . $messageType);
        exit();
    } else {
        $message = "Gagal menghapus armada!";
        $messageType = "danger";
    }
}

// Handle create
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'create') {
    $kode = mysqli_real_escape_string($koneksi, $_POST['kode_armada']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_armada']);
    $jenis = mysqli_real_escape_string($koneksi, $_POST['jenis_armada']);
    $sub_jenis = mysqli_real_escape_string($koneksi, $_POST['sub_jenis_armada']);
    $kapasitas = intval($_POST['kapasitas']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    $lokasi = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
    
    // Validasi kapasitas tidak boleh negatif atau 0
    if ($kapasitas <= 0) {
        $message = "Kapasitas harus lebih dari 0 Kg!";
        $messageType = "danger";
    } else {
        // PERHATIAN: Gunakan 'code_armada' karena di database Anda pakai 'code_armada'
        $sql = "INSERT INTO armada (code_armada, nama_armada, jenis_armada, sub_jenis, kapasitas, status, lokasi) 
                VALUES ('$kode', '$nama', '$jenis', '$sub_jenis', '$kapasitas', '$status', '$lokasi')";
        
        if ($koneksi->query($sql)) {
            $message = "Armada berhasil ditambahkan!";
            $messageType = "success";
            header('Location: index.php?message=' . urlencode($message) . '&type=' . $messageType);
            exit();
        } else {
            $message = "Gagal menambahkan armada: " . $koneksi->error;
            $messageType = "danger";
        }
    }
}

// Get message from URL
if (isset($_GET['message'])) {
    $message = $_GET['message'];
    $messageType = isset($_GET['type']) ? $_GET['type'] : 'success';
}

// Get all armada - PERHATIAN: gunakan 'code_armada'
$sql = "SELECT * FROM armada ORDER BY created_at DESC";
$result = $koneksi->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Armada - Ekspedisi Logistik</title>
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

        .badge-darat {
            background-color: #3498db;
            color: white;
        }
        
        .badge-laut {
            background-color: #1abc9c;
            color: white;
        }
        
        .badge-udara {
            background-color: #9b59b6;
            color: white;
        }
        
        .badge-tersedia {
            background-color: #27ae60;
            color: white;
        }
        
        .badge-perawatan {
            background-color: #f39c12;
            color: white;
        }
        
        .badge-disewa {
            background-color: #e74c3c;
            color: white;
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
                    <a class="nav-link bg-primary text-white" href="index.php">
                        <i class="fas fa-truck"></i> Armada
                    </a>
                    <a class="nav-link text-white" href="../Cargo/index.php">
                        <i class="fas fa-box"></i> Cargo
                    </a>
                    <a class="nav-link text-white" href="../Pelanggan/index.php">
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
                <h2 class="mb-4"><i class="fas fa-truck"></i> Manajemen Armada</h2>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Button to trigger modal -->
                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addArmadaModal">
                    <i class="fas fa-plus"></i> Tambah Armada Baru
                </button>
                
                <!-- Armada List -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Daftar Armada</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nama Armada</th>
                                        <th>Jenis Armada</th>
                                        <th>Kapasitas (Kg)</th>
                                        <th>Status</th>
                                        <th>Lokasi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result && $result->num_rows > 0): ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <!-- PERHATIAN: gunakan 'code_armada' -->
                                            <td><strong><?= htmlspecialchars($row['code_armada']) ?></strong></td>
                                            <td><?= htmlspecialchars($row['nama_armada']) ?></td>
                                            <td>
                                                <?php
                                                $jenisClass = 'badge-secondary';
                                                $icon = '';
                                                if ($row['jenis_armada'] == 'Darat') {
                                                    $jenisClass = 'badge-darat';
                                                    $icon = '🚛';
                                                } else if ($row['jenis_armada'] == 'Laut') {
                                                    $jenisClass = 'badge-laut';
                                                    $icon = '🚢';
                                                } else if ($row['jenis_armada'] == 'Udara') {
                                                    $jenisClass = 'badge-udara';
                                                    $icon = '✈️';
                                                }
                                                ?>
                                                <span class="badge <?= $jenisClass ?>" style="font-size:0.9rem;">
                                                    <?= $icon ?> <?= htmlspecialchars($row['sub_jenis']) ?>
                                                </span>
                                            </td>
                                            <td><?= number_format($row['kapasitas'], 0, ',', '.') ?></td>
                                            <td>
                                                <?php
                                                $statusClass = 'badge-secondary';
                                                if ($row['status'] == 'Tersedia') $statusClass = 'badge-tersedia';
                                                else if ($row['status'] == 'Dalam Perawatan') $statusClass = 'badge-perawatan';
                                                else if ($row['status'] == 'Disewa') $statusClass = 'badge-disewa';
                                                ?>
                                                <span class="badge <?= $statusClass ?>">
                                                    <?= htmlspecialchars($row['status']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($row['lokasi']) ?></td>
                                            <td>
                                                <a href="?edit=<?= $row['id_armada'] ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="?delete=<?= $row['id_armada'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus armada ini?')">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <i class="fas fa-truck fa-3x d-block mb-3 text-muted"></i>
                                                <p class="mb-2">Belum ada data armada</p>
                                                <small class="text-muted">Klik tombol "Tambah Armada Baru" untuk menambahkan</small>
                                            </td>
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
    
    <!-- Add Armada Modal -->
    <div class="modal fade" id="addArmadaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" onsubmit="return validateForm()">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Armada Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-3">
                            <label class="form-label">Kode Armada</label>
                            <input type="text" class="form-control" name="kode_armada" required placeholder="Contoh: TRK-001">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Armada</label>
                            <input type="text" class="form-control" name="nama_armada" required placeholder="Nama armada">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jenis Armada</label>
                            <select class="form-select" name="jenis_armada" id="jenisArmada" required onchange="updateSubJenis()">
                                <option value="">Pilih Jenis Armada</option>
                                <option value="Darat">🚛 Darat</option>
                                <option value="Laut">🚢 Laut</option>
                                <option value="Udara">✈️ Udara</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sub Jenis Armada</label>
                            <select class="form-select" name="sub_jenis_armada" id="subJenisArmada" required>
                                <option value="">Pilih Sub Jenis</option>
                            </select>
                            <small class="text-muted">Pilih jenis armada terlebih dahulu untuk memfilter sub jenis</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kapasitas (Kg)</label>
                            <input type="number" class="form-control" name="kapasitas" id="kapasitas" required min="1" placeholder="Kapasitas dalam Kg">
                            <small class="text-muted">Minimal 1 Kg</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="Tersedia">✅ Tersedia</option>
                                <option value="Dalam Perawatan">🔧 Dalam Perawatan</option>
                                <option value="Disewa">📋 Disewa</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Lokasi</label>
                            <input type="text" class="form-control" name="lokasi" required placeholder="Lokasi armada saat ini">
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
    
    <?php if (isset($_GET['edit'])): 
        $id = intval($_GET['edit']);
        $editSql = "SELECT * FROM armada WHERE id_armada = $id";
        $editResult = $koneksi->query($editSql);
        $editData = $editResult->fetch_assoc();
    ?>
    <!-- Edit Armada Modal -->
    <div class="modal fade show" id="editArmadaModal" tabindex="-1" style="display:block; background: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="update_armada.php">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Armada</h5>
                        <a href="index.php" class="btn-close"></a>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_armada" value="<?= $editData['id_armada'] ?>">
                        <div class="mb-3">
                            <label class="form-label">Kode Armada</label>
                            <!-- PERHATIAN: gunakan 'code_armada' -->
                            <input type="text" class="form-control" name="kode_armada" value="<?= htmlspecialchars($editData['code_armada']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Armada</label>
                            <input type="text" class="form-control" name="nama_armada" value="<?= htmlspecialchars($editData['nama_armada']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jenis Armada</label>
                            <select class="form-select" name="jenis_armada" required>
                                <option value="Darat" <?= $editData['jenis_armada'] == 'Darat' ? 'selected' : '' ?>>🚛 Darat</option>
                                <option value="Laut" <?= $editData['jenis_armada'] == 'Laut' ? 'selected' : '' ?>>🚢 Laut</option>
                                <option value="Udara" <?= $editData['jenis_armada'] == 'Udara' ? 'selected' : '' ?>>✈️ Udara</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sub Jenis Armada</label>
                            <select class="form-select" name="sub_jenis_armada" required>
                                <option value="Truk Darat" <?= $editData['sub_jenis'] == 'Truk Darat' ? 'selected' : '' ?>>🚛 Truk Darat</option>
                                <option value="Kapal Laut" <?= $editData['sub_jenis'] == 'Kapal Laut' ? 'selected' : '' ?>>🚢 Kapal Laut</option>
                                <option value="Pesawat Kargo" <?= $editData['sub_jenis'] == 'Pesawat Kargo' ? 'selected' : '' ?>>✈️ Pesawat Kargo</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kapasitas (Kg)</label>
                            <input type="number" class="form-control" name="kapasitas" value="<?= $editData['kapasitas'] ?>" required min="1">
                            <small class="text-muted">Minimal 1 Kg</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="Tersedia" <?= $editData['status'] == 'Tersedia' ? 'selected' : '' ?>>✅ Tersedia</option>
                                <option value="Dalam Perawatan" <?= $editData['status'] == 'Dalam Perawatan' ? 'selected' : '' ?>>🔧 Dalam Perawatan</option>
                                <option value="Disewa" <?= $editData['status'] == 'Disewa' ? 'selected' : '' ?>>📋 Disewa</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Lokasi</label>
                            <input type="text" class="form-control" name="lokasi" value="<?= htmlspecialchars($editData['lokasi']) ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="index.php" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateSubJenis() {
            const jenis = document.getElementById('jenisArmada').value;
            const subSelect = document.getElementById('subJenisArmada');
            
            subSelect.innerHTML = '<option value="">Pilih Sub Jenis</option>';
            
            if (jenis === 'Darat') {
                subSelect.innerHTML += '<option value="Truk Darat">🚛 Truk Darat</option>';
            } else if (jenis === 'Laut') {
                subSelect.innerHTML += '<option value="Kapal Laut">🚢 Kapal Laut</option>';
            } else if (jenis === 'Udara') {
                subSelect.innerHTML += '<option value="Pesawat Kargo">✈️ Pesawat Kargo</option>';
            }
        }

        function validateForm() {
            const kapasitas = document.getElementById('kapasitas');
            if (kapasitas.value <= 0) {
                alert('Kapasitas harus lebih dari 0 Kg!');
                kapasitas.focus();
                return false;
            }
            return true;
        }
    </script>
</body>
</html>