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
    $kode = mysqli_real_escape_string($koneksi, $_POST['id_armada_code']);
    $jenis = mysqli_real_escape_string($koneksi, $_POST['jenis_armada']);
    $kapasitas = floatval($_POST['kapasitas_maksimal_kg']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status_kelaikan']);
    $biaya = floatval($_POST['biaya_operasional_dasar']);
    $jumlah_roda = intval($_POST['jumlah_roda']);
    $rute_tol = mysqli_real_escape_string($koneksi, $_POST['rute_tol']);
    $nama_dermaga = mysqli_real_escape_string($koneksi, $_POST['nama_dermaga']);
    $jenis_kontainer = mysqli_real_escape_string($koneksi, $_POST['jenis_kontainer']);
    $batas_ketinggian = floatval($_POST['batas_ketinggian']);
    $izin_penerbangan = mysqli_real_escape_string($koneksi, $_POST['izin_penerbangan_khusus']);
    
    // Validasi kapasitas tidak boleh negatif atau 0
    if ($kapasitas <= 0) {
        $message = "Kapasitas harus lebih dari 0 Kg!";
        $messageType = "danger";
    } else {
        $sql = "INSERT INTO armada (
                    id_armada_code, 
                    jenis_armada, 
                    kapasitas_maksimal_kg, 
                    status_kelaikan, 
                    biaya_operasional_dasar, 
                    jumlah_roda, 
                    rute_tol, 
                    nama_dermaga, 
                    jenis_kontainer, 
                    batas_ketinggian, 
                    izin_penerbangan_khusus
                ) VALUES (
                    '$kode', 
                    '$jenis', 
                    '$kapasitas', 
                    '$status', 
                    '$biaya', 
                    '$jumlah_roda', 
                    '$rute_tol', 
                    '$nama_dermaga', 
                    '$jenis_kontainer', 
                    '$batas_ketinggian', 
                    '$izin_penerbangan'
                )";
        
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

// Get all armada
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

        .badge-truk {
            background-color: #3498db;
            color: white;
        }
        
        .badge-kapal {
            background-color: #1abc9c;
            color: white;
        }
        
        .badge-pesawat {
            background-color: #9b59b6;
            color: white;
        }
        
        .badge-laik {
            background-color: #27ae60;
            color: white;
        }
        
        .badge-tidak-laik {
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
                                        <th>Jenis Armada</th>
                                        <th>Kapasitas (Kg)</th>
                                        <th>Status</th>
                                        <th>Biaya Operasional</th>
                                        <th>Lokasi/Dermaga</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result && $result->num_rows > 0): ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($row['id_armada_code']) ?></strong></td>
                                            <td>
                                                <?php
                                                $jenisClass = 'badge-truk';
                                                $icon = '🚛';
                                                $label = 'Truk Darat';
                                                if ($row['jenis_armada'] == 'KapalLaut') {
                                                    $jenisClass = 'badge-kapal';
                                                    $icon = '🚢';
                                                    $label = 'Kapal Laut';
                                                } else if ($row['jenis_armada'] == 'PesawatKargo') {
                                                    $jenisClass = 'badge-pesawat';
                                                    $icon = '✈️';
                                                    $label = 'Pesawat Kargo';
                                                }
                                                ?>
                                                <span class="badge <?= $jenisClass ?>" style="font-size:0.9rem;">
                                                    <?= $icon ?> <?= $label ?>
                                                </span>
                                            </td>
                                            <td><?= number_format($row['kapasitas_maksimal_kg'], 0, ',', '.') ?></td>
                                            <td>
                                                <?php
                                                $statusClass = $row['status_kelaikan'] == 'Laik' ? 'badge-laik' : 'badge-tidak-laik';
                                                ?>
                                                <span class="badge <?= $statusClass ?>">
                                                    <?= htmlspecialchars($row['status_kelaikan']) ?>
                                                </span>
                                            </td>
                                            <td>Rp <?= number_format($row['biaya_operasional_dasar'], 0, ',', '.') ?></td>
                                            <td>
                                                <?php 
                                                if ($row['jenis_armada'] == 'TrukDarat') {
                                                    echo htmlspecialchars($row['rute_tol']);
                                                } else if ($row['jenis_armada'] == 'KapalLaut') {
                                                    echo htmlspecialchars($row['nama_dermaga']);
                                                } else if ($row['jenis_armada'] == 'PesawatKargo') {
                                                    echo htmlspecialchars($row['izin_penerbangan_khusus']);
                                                }
                                                ?>
                                            </td>
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
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" onsubmit="return validateForm()">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Armada Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kode Armada</label>
                                <input type="text" class="form-control" name="id_armada_code" required placeholder="Contoh: TRK-001">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis Armada</label>
                                <select class="form-select" name="jenis_armada" id="jenisArmada" required onchange="toggleFields()">
                                    <option value="">Pilih Jenis Armada</option>
                                    <option value="TrukDarat">🚛 Truk Darat</option>
                                    <option value="KapalLaut">🚢 Kapal Laut</option>
                                    <option value="PesawatKargo">✈️ Pesawat Kargo</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kapasitas Maksimal (Kg)</label>
                                <input type="number" class="form-control" name="kapasitas_maksimal_kg" id="kapasitas" required min="1" step="0.01" placeholder="Kapasitas dalam Kg">
                                <small class="text-muted">Minimal 1 Kg</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status Kelaikan</label>
                                <select class="form-select" name="status_kelaikan" required>
                                    <option value="Laik">✅ Laik</option>
                                    <option value="Tidak Laik">❌ Tidak Laik</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Biaya Operasional Dasar (Rp)</label>
                                <input type="number" class="form-control" name="biaya_operasional_dasar" required min="0" step="0.01" placeholder="Biaya operasional">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jumlah Roda</label>
                                <input type="number" class="form-control" name="jumlah_roda" min="0" placeholder="Jumlah roda">
                            </div>
                        </div>

                        <!-- Field khusus Truk Darat -->
                        <div id="trukFields" style="display:none;">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Rute Tol</label>
                                    <textarea class="form-control" name="rute_tol" rows="2" placeholder="Rute tol yang dilalui"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Field khusus Kapal Laut -->
                        <div id="kapalFields" style="display:none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nama Dermaga</label>
                                    <input type="text" class="form-control" name="nama_dermaga" placeholder="Nama dermaga">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Jenis Kontainer</label>
                                    <input type="text" class="form-control" name="jenis_kontainer" placeholder="Jenis kontainer">
                                </div>
                            </div>
                        </div>

                        <!-- Field khusus Pesawat Kargo -->
                        <div id="pesawatFields" style="display:none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Batas Ketinggian (meter)</label>
                                    <input type="number" class="form-control" name="batas_ketinggian" step="0.01" placeholder="Batas ketinggian">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Izin Penerbangan Khusus</label>
                                    <input type="text" class="form-control" name="izin_penerbangan_khusus" placeholder="Izin penerbangan">
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
    
    <?php if (isset($_GET['edit'])): 
        $id = intval($_GET['edit']);
        $editSql = "SELECT * FROM armada WHERE id_armada = $id";
        $editResult = $koneksi->query($editSql);
        $editData = $editResult->fetch_assoc();
    ?>
    <!-- Edit Armada Modal -->
    <div class="modal fade show" id="editArmadaModal" tabindex="-1" style="display:block; background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="update_armada.php">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Armada</h5>
                        <a href="index.php" class="btn-close"></a>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_armada" value="<?= $editData['id_armada'] ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kode Armada</label>
                                <input type="text" class="form-control" name="id_armada_code" value="<?= htmlspecialchars($editData['id_armada_code']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis Armada</label>
                                <select class="form-select" name="jenis_armada" required>
                                    <option value="TrukDarat" <?= $editData['jenis_armada'] == 'TrukDarat' ? 'selected' : '' ?>>🚛 Truk Darat</option>
                                    <option value="KapalLaut" <?= $editData['jenis_armada'] == 'KapalLaut' ? 'selected' : '' ?>>🚢 Kapal Laut</option>
                                    <option value="PesawatKargo" <?= $editData['jenis_armada'] == 'PesawatKargo' ? 'selected' : '' ?>>✈️ Pesawat Kargo</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kapasitas Maksimal (Kg)</label>
                                <input type="number" class="form-control" name="kapasitas_maksimal_kg" value="<?= $editData['kapasitas_maksimal kg'] ?>" required min="1" step="0.01">
                                <small class="text-muted">Minimal 1 Kg</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status Kelaikan</label>
                                <select class="form-select" name="status_kelaikan" required>
                                    <option value="Laik" <?= $editData['status_kelaikan'] == 'Laik' ? 'selected' : '' ?>>✅ Laik</option>
                                    <option value="Tidak Laik" <?= $editData['status_kelaikan'] == 'Tidak Laik' ? 'selected' : '' ?>>❌ Tidak Laik</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Biaya Operasional Dasar (Rp)</label>
                                <input type="number" class="form-control" name="biaya_operasional_dasar" value="<?= $editData['biaya_operasional_dasar'] ?>" required min="0" step="0.01">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jumlah Roda</label>
                                <input type="number" class="form-control" name="jumlah_roda" value="<?= $editData['jumlah_roda'] ?>" min="0">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Rute Tol</label>
                                <textarea class="form-control" name="rute_tol" rows="2"><?= htmlspecialchars($editData['rute_tol']) ?></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Dermaga</label>
                                <input type="text" class="form-control" name="nama_dermaga" value="<?= htmlspecialchars($editData['nama_dermaga']) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis Kontainer</label>
                                <input type="text" class="form-control" name="jenis_kontainer" value="<?= htmlspecialchars($editData['jenis_kontainer']) ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Batas Ketinggian (meter)</label>
                                <input type="number" class="form-control" name="batas_ketinggian" value="<?= $editData['batas_ketinggian'] ?>" step="0.01">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Izin Penerbangan Khusus</label>
                                <input type="text" class="form-control" name="izin_penerbangan_khusus" value="<?= htmlspecialchars($editData['izin_penerbangan_khusus']) ?>">
                            </div>
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
        function toggleFields() {
            const jenis = document.getElementById('jenisArmada').value;
            
            document.getElementById('trukFields').style.display = 'none';
            document.getElementById('kapalFields').style.display = 'none';
            document.getElementById('pesawatFields').style.display = 'none';
            
            if (jenis === 'TrukDarat') {
                document.getElementById('trukFields').style.display = 'block';
            } else if (jenis === 'KapalLaut') {
                document.getElementById('kapalFields').style.display = 'block';
            } else if (jenis === 'PesawatKargo') {
                document.getElementById('pesawatFields').style.display = 'block';
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