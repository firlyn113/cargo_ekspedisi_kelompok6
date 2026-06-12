<?php
/**
 * Index.php - Modul STAFF
 * Interface utama untuk mengelola semua staff logistik
 * Fitur: CREATE, READ, UPDATE, DELETE, Hitung Take Home Pay, Evaluasi SOP Kerja
 */

// Koneksi database
require_once '../config/koneksi.php';
require_once 'StaffManager.php';

$database = new Database();
$conn = $database->getConnection();
$staffManager = new StaffManager($conn);

// Variabel untuk mode tampilan
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'list';
$message = '';
$messageType = '';

// ===== PROSES FORM =====

// CREATE: Tambah Staff Baru
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $mode == 'add') {
    $idStaffCode = $_POST['id_staff_code'] ?? '';
    $namaLengkap = $_POST['nama_lengkap'] ?? '';
    $gajiPokok = $_POST['gaji_pokok'] ?? 0;
    $jamKerja = $_POST['jam_kerja'] ?? 0;
    $jenisStaff = $_POST['jenis_staff'] ?? '';
    
    $dataEkstra = [];
    
    if ($jenisStaff == 'SupirTruk') {
        $dataEkstra = [
            'nomor_sim_b' => $_POST['nomor_sim_b'] ?? '',
            'uang_makan_jalan' => $_POST['uang_makan_jalan'] ?? 0,
            'rute_operasional' => $_POST['rute_operasional'] ?? ''
        ];
    } elseif ($jenisStaff == 'AdminGudang') {
        $dataEkstra = [
            'shift_kerja' => $_POST['shift_kerja'] ?? 'Pagi',
            'zona_gudang' => $_POST['zona_gudang'] ?? ''
        ];
    } elseif ($jenisStaff == 'KurirMotor') {
        $dataEkstra = [
            'plat_nomor_motor' => $_POST['plat_nomor_motor'] ?? '',
            'area_cakupan' => $_POST['area_cakupan'] ?? ''
        ];
    }
    
    $result = $staffManager->addStaff($idStaffCode, $namaLengkap, $gajiPokok, $jamKerja, $jenisStaff, $dataEkstra);
    
    if ($result['success']) {
        $message = $result['message'];
        $messageType = 'success';
        $mode = 'list';
    } else {
        $message = $result['message'];
        $messageType = 'danger';
    }
}

// UPDATE: Perbarui Staff
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $mode == 'edit') {
    $idStaff = $_POST['id_staff'] ?? 0;
    $namaLengkap = $_POST['nama_lengkap'] ?? null;
    $gajiPokok = $_POST['gaji_pokok'] ?? null;
    $jamKerja = $_POST['jam_kerja'] ?? null;
    
    $dataEkstra = [];
    $jenisStaff = $_POST['jenis_staff'] ?? '';
    
    if ($jenisStaff == 'SupirTruk') {
        $dataEkstra = [
            'nomor_sim_b' => $_POST['nomor_sim_b'] ?? null,
            'uang_makan_jalan' => $_POST['uang_makan_jalan'] ?? null,
            'rute_operasional' => $_POST['rute_operasional'] ?? null
        ];
    } elseif ($jenisStaff == 'AdminGudang') {
        $dataEkstra = [
            'shift_kerja' => $_POST['shift_kerja'] ?? null,
            'zona_gudang' => $_POST['zona_gudang'] ?? null
        ];
    } elseif ($jenisStaff == 'KurirMotor') {
        $dataEkstra = [
            'plat_nomor_motor' => $_POST['plat_nomor_motor'] ?? null,
            'area_cakupan' => $_POST['area_cakupan'] ?? null
        ];
    }
    
    $result = $staffManager->updateStaff($idStaff, $namaLengkap, $gajiPokok, $jamKerja, $dataEkstra);
    
    if ($result['success']) {
        $message = $result['message'];
        $messageType = 'success';
        $mode = 'list';
    } else {
        $message = $result['message'];
        $messageType = 'danger';
    }
}

// DELETE: Hapus Staff
if ($mode == 'delete' && isset($_GET['id'])) {
    $result = $staffManager->deleteStaff($_GET['id']);
    if ($result['success']) {
        $message = $result['message'];
        $messageType = 'success';
    } else {
        $message = $result['message'];
        $messageType = 'danger';
    }
    $mode = 'list';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modul STAFF - Ekspedisi Logistik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
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
        
        .container-main {
            margin-top: 2rem;
            margin-bottom: 2rem;
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
        
        .table {
            margin-bottom: 0;
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
        
        .tab-content {
            background-color: white;
            border-radius: 0 0 8px 8px;
            padding: 2rem;
        }
        
        .nav-tabs {
            border-bottom: 2px solid var(--secondary-color);
        }
        
        .nav-tabs .nav-link {
            color: var(--primary-color);
            border: none;
            border-bottom: 3px solid transparent;
            font-weight: 500;
        }
        
        .nav-tabs .nav-link:hover {
            border-color: var(--secondary-color);
        }
        
        .nav-tabs .nav-link.active {
            color: var(--secondary-color);
            border-color: var(--secondary-color);
            background-color: transparent;
        }
        
        .stat-box {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--secondary-color);
        }
        
        .stat-label {
            color: #7f8c8d;
            margin-top: 0.5rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-dark">
        <div class="container-fluid">
            <span class="navbar-brand">
                <i class="bi bi-people-fill"></i> Modul STAFF - Ekspedisi Logistik
            </span>
        </div>
    </nav>

    <div class="container-main">
        <!-- MESSAGE ALERT -->
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-<?php echo ($messageType == 'success' ? 'check-circle' : 'exclamation-circle'); ?>"></i>
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- MODE: LIST SEMUA STAFF -->
        <?php if ($mode == 'list'): ?>
            
            <!-- STATISTICS -->
            <div class="row mb-4">
                <?php 
                    $stats = $staffManager->getStaffStatistics();
                    if ($stats['success']): 
                ?>
                    <div class="col-md-3">
                        <div class="stat-box">
                            <div class="stat-number"><?php echo $stats['data']['SupirTruk']; ?></div>
                            <div class="stat-label">Sopir Truk</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-box">
                            <div class="stat-number"><?php echo $stats['data']['AdminGudang']; ?></div>
                            <div class="stat-label">Admin Gudang</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-box">
                            <div class="stat-number"><?php echo $stats['data']['KurirMotor']; ?></div>
                            <div class="stat-label">Kurir Motor</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-box">
                            <div class="stat-number"><?php echo $stats['total']; ?></div>
                            <div class="stat-label">Total Staff</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-list-ul"></i> Daftar Staff Logistik</h5>
                    <a href="?mode=add" class="btn btn-light btn-sm">
                        <i class="bi bi-plus-lg"></i> Tambah Staff Baru
                    </a>
                </div>
                <div class="card-body">
                    <!-- FILTER -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" id="searchInput" class="form-control" placeholder="Cari nama atau ID staff...">
                        </div>
                        <div class="col-md-6">
                            <select id="filterJenis" class="form-select">
                                <option value="">Semua Jenis Staff</option>
                                <option value="SupirTruk">Sopir Truk</option>
                                <option value="AdminGudang">Admin Gudang</option>
                                <option value="KurirMotor">Kurir Motor</option>
                            </select>
                        </div>
                    </div>

                    <!-- TABEL STAFF -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID Code</th>
                                    <th>Nama Lengkap</th>
                                    <th>Jenis Staff</th>
                                    <th>Gaji Pokok</th>
                                    <th>Jam Kerja</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="staffTable">
                                <?php 
                                    $staffResult = $staffManager->getAllStaff();
                                    if ($staffResult['success'] && count($staffResult['data']) > 0):
                                        foreach ($staffResult['data'] as $staff):
                                ?>
                                    <tr>
                                        <td><code><?php echo htmlspecialchars($staff['id_staff_code']); ?></code></td>
                                        <td><strong><?php echo htmlspecialchars($staff['nama_lengkap']); ?></strong></td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $staff['jenis_staff']; ?></span>
                                        </td>
                                        <td>Rp <?php echo number_format($staff['gaji_pokok'], 0, ',', '.'); ?></td>
                                        <td><?php echo $staff['jam_kerja']; ?> jam</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="?mode=view&id=<?php echo $staff['id_staff']; ?>" class="btn btn-sm btn-primary" title="Lihat Detail">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="?mode=edit&id=<?php echo $staff['id_staff']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="?mode=delete&id=<?php echo $staff['id_staff']; ?>" class="btn btn-sm btn-danger" title="Hapus" onclick="return confirm('Yakin ingin menghapus?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php 
                                        endforeach;
                                    else:
                                ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox"></i> Tidak ada data staff
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <!-- MODE: TAMBAH STAFF -->
        <?php elseif ($mode == 'add'): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-person-plus"></i> Tambah Staff Baru</h5>
                </div>
                <div class="card-body">
                    <form method="POST" class="needs-validation">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ID Staff Code</label>
                                <input type="text" class="form-control" name="id_staff_code" required placeholder="Contoh: STF001">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" name="nama_lengkap" required placeholder="Nama lengkap staff">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis Staff</label>
                                <select class="form-select" name="jenis_staff" id="jenisStaffForm" required onchange="updateFormFields()">
                                    <option value="">-- Pilih Jenis Staff --</option>
                                    <option value="SupirTruk">Sopir Truk</option>
                                    <option value="AdminGudang">Admin Gudang</option>
                                    <option value="KurirMotor">Kurir Motor</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gaji Pokok (Rp)</label>
                                <input type="number" class="form-control" name="gaji_pokok" required placeholder="2000000">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jam Kerja / Bulan</label>
                                <input type="number" class="form-control" name="jam_kerja" required placeholder="200">
                            </div>
                        </div>

                        <!-- FIELD DINAMIS BERDASARKAN JENIS STAFF -->
                        <div id="dinamicFields"></div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-lg"></i> Simpan Data
                            </button>
                            <a href="?mode=list" class="btn btn-secondary">
                                <i class="bi bi-x-lg"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>

        <!-- MODE: EDIT STAFF -->
        <?php elseif ($mode == 'edit' && isset($_GET['id'])): 
            $staffData = $staffManager->getStaffById($_GET['id']);
            if ($staffData['success']):
                $staff = $staffData['data'];
        ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Edit Data Staff</h5>
                </div>
                <div class="card-body">
                    <form method="POST" class="needs-validation">
                        <input type="hidden" name="id_staff" value="<?php echo $staff['id_staff']; ?>">
                        <input type="hidden" name="jenis_staff" value="<?php echo $staff['jenis_staff']; ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ID Staff Code</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($staff['id_staff_code']); ?>" disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" name="nama_lengkap" value="<?php echo htmlspecialchars($staff['nama_lengkap']); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gaji Pokok (Rp)</label>
                                <input type="number" class="form-control" name="gaji_pokok" value="<?php echo $staff['gaji_pokok']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jam Kerja / Bulan</label>
                                <input type="number" class="form-control" name="jam_kerja" value="<?php echo $staff['jam_kerja']; ?>" required>
                            </div>
                        </div>

                        <!-- FIELD DINAMIS BERDASARKAN JENIS STAFF -->
                        <?php if ($staff['jenis_staff'] == 'SupirTruk'): ?>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nomor SIM B</label>
                                    <input type="text" class="form-control" name="nomor_sim_b" value="<?php echo htmlspecialchars($staff['nomor_sim_b'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Uang Makan Jalan (Rp)</label>
                                    <input type="number" class="form-control" name="uang_makan_jalan" value="<?php echo $staff['uang_makan_jalan'] ?? 0; ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Rute Operasional</label>
                                    <input type="text" class="form-control" name="rute_operasional" value="<?php echo htmlspecialchars($staff['rute_tol'] ?? ''); ?>" placeholder="Contoh: Jakarta-Bandung">
                                </div>
                            </div>

                        <?php elseif ($staff['jenis_staff'] == 'AdminGudang'): ?>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Shift Kerja</label>
                                    <select class="form-select" name="shift_kerja">
                                        <option value="Pagi" <?php echo ($staff['shift_kerja'] == 'Pagi' ? 'selected' : ''); ?>>Pagi</option>
                                        <option value="Siang" <?php echo ($staff['shift_kerja'] == 'Siang' ? 'selected' : ''); ?>>Siang</option>
                                        <option value="Malam" <?php echo ($staff['shift_kerja'] == 'Malam' ? 'selected' : ''); ?>>Malam</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Zona Gudang</label>
                                    <input type="text" class="form-control" name="zona_gudang" value="<?php echo htmlspecialchars($staff['zona_gudang'] ?? ''); ?>" placeholder="Contoh: Zona A">
                                </div>
                            </div>

                        <?php elseif ($staff['jenis_staff'] == 'KurirMotor'): ?>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Plat Nomor Motor</label>
                                    <input type="text" class="form-control" name="plat_nomor_motor" value="<?php echo htmlspecialchars($staff['plat_nomor_motor'] ?? ''); ?>" placeholder="Contoh: B 1234 XYZ">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Area Cakupan</label>
                                    <input type="text" class="form-control" name="area_cakupan" value="<?php echo htmlspecialchars($staff['area_cakupan'] ?? ''); ?>" placeholder="Contoh: Jakarta Pusat">
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-lg"></i> Simpan Perubahan
                            </button>
                            <a href="?mode=list" class="btn btn-secondary">
                                <i class="bi bi-x-lg"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php 
            else:
                echo '<div class="alert alert-danger">Staff tidak ditemukan!</div>';
            endif;
        
        // MODE: VIEW DETAIL & EVALUASI
        elseif ($mode == 'view' && isset($_GET['id'])):
            $staffData = $staffManager->getStaffById($_GET['id']);
            if ($staffData['success']):
                $staff = $staffData['data'];
        ?>
            <div class="row">
                <!-- INFORMASI STAFF -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-person-badge"></i> Informasi Staff</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>ID Code:</strong></td>
                                    <td><?php echo htmlspecialchars($staff['id_staff_code']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Nama:</strong></td>
                                    <td><?php echo htmlspecialchars($staff['nama_lengkap']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Jenis Staff:</strong></td>
                                    <td><span class="badge bg-info"><?php echo $staff['jenis_staff']; ?></span></td>
                                </tr>
                                <tr>
                                    <td><strong>Gaji Pokok:</strong></td>
                                    <td>Rp <?php echo number_format($staff['gaji_pokok'], 0, ',', '.'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Jam Kerja:</strong></td>
                                    <td><?php echo $staff['jam_kerja']; ?> jam/bulan</td>
                                </tr>

                                <?php if ($staff['jenis_staff'] == 'SupirTruk'): ?>
                                    <tr>
                                        <td><strong>No. SIM B:</strong></td>
                                        <td><?php echo htmlspecialchars($staff['nomor_sim_b'] ?? '-'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Uang Makan Jalan:</strong></td>
                                        <td>Rp <?php echo number_format($staff['uang_makan_jalan'] ?? 0, 0, ',', '.'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Rute Operasional:</strong></td>
                                        <td><?php echo htmlspecialchars($staff['rute_tol'] ?? '-'); ?></td>
                                    </tr>

                                <?php elseif ($staff['jenis_staff'] == 'AdminGudang'): ?>
                                    <tr>
                                        <td><strong>Shift Kerja:</strong></td>
                                        <td><?php echo htmlspecialchars($staff['shift_kerja'] ?? '-'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Zona Gudang:</strong></td>
                                        <td><?php echo htmlspecialchars($staff['zona_gudang'] ?? '-'); ?></td>
                                    </tr>

                                <?php elseif ($staff['jenis_staff'] == 'KurirMotor'): ?>
                                    <tr>
                                        <td><strong>Plat Motor:</strong></td>
                                        <td><?php echo htmlspecialchars($staff['plat_nomor_motor'] ?? '-'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Area Cakupan:</strong></td>
                                        <td><?php echo htmlspecialchars($staff['area_cakupan'] ?? '-'); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </table>

                            <div class="d-flex gap-2 mt-3">
                                <a href="?mode=edit&id=<?php echo $staff['id_staff']; ?>" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <a href="?mode=list" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-arrow-left"></i> Kembali
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- HITUNG GAJI & EVALUASI -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-calculator"></i> Perhitungan & Evaluasi</h5>
                        </div>
                        <div class="card-body">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#takeHomePay">Take Home Pay</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#evaluasi">Evaluasi SOP</a>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <!-- TAB 1: TAKE HOME PAY -->
                                <div id="takeHomePay" class="tab-pane fade show active">
                                    <div class="mt-4">
                                        <?php
                                            $thpResult = $staffManager->hitungTakeHomePay($staff['id_staff']);
                                            if ($thpResult['success']):
                                                $thpData = $thpResult['data'];
                                        ?>
                                            <div class="alert alert-info">
                                                <h6>Perhitungan Take Home Pay</h6>
                                                <table class="table table-sm">
                                                    <tr>
                                                        <td>Gaji Pokok</td>
                                                        <td class="text-end"><strong>Rp <?php echo number_format($thpData['gaji_pokok'], 0, ',', '.'); ?></strong></td>
                                                    </tr>
                                                    <?php if ($staff['jenis_staff'] == 'SupirTruk'): ?>
                                                        <tr>
                                                            <td>Uang Makan Jalan</td>
                                                            <td class="text-end"><strong>Rp <?php echo number_format($staff['uang_makan_jalan'] ?? 0, 0, ',', '.'); ?></strong></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Tunjangan Lembur (0 jam)</td>
                                                            <td class="text-end"><strong>Rp 0</strong></td>
                                                        </tr>
                                                    <?php elseif ($staff['jenis_staff'] == 'AdminGudang'): ?>
                                                        <tr>
                                                            <td>Tunjangan Shift (<?php echo htmlspecialchars($staff['shift_kerja'] ?? '-'); ?>)</td>
                                                            <td class="text-end"><strong>Rp <?php echo number_format($thpData['gaji_pokok'] * ($staff['shift_kerja'] == 'Malam' ? 0.10 : 0.05), 0, ',', '.'); ?></strong></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Bonus Produktivitas</td>
                                                            <td class="text-end"><strong>Rp 0</strong></td>
                                                        </tr>
                                                    <?php elseif ($staff['jenis_staff'] == 'KurirMotor'): ?>
                                                        <tr>
                                                            <td>Insentif Per Paket (0 paket)</td>
                                                            <td class="text-end"><strong>Rp 0</strong></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Bonus Accuracy</td>
                                                            <td class="text-end"><strong>Rp 0</strong></td>
                                                        </tr>
                                                    <?php endif; ?>
                                                    <tr class="table-active">
                                                        <td><strong>TOTAL TAKE HOME PAY</strong></td>
                                                        <td class="text-end"><strong style="color: var(--success-color); font-size: 1.2rem;">Rp <?php echo number_format($thpData['take_home_pay'], 0, ',', '.'); ?></strong></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- TAB 2: EVALUASI SOP KERJA -->
                                <div id="evaluasi" class="tab-pane fade">
                                    <div class="mt-4">
                                        <?php
                                            $evalData = [];
                                            if ($staff['jenis_staff'] == 'AdminGudang') {
                                                $evalData = ['jumlah_barang' => 50, 'jumlah_error' => 1];
                                            } elseif ($staff['jenis_staff'] == 'KurirMotor') {
                                                $evalData = ['jumlah_paket_antar' => 20, 'jumlah_paket_terima' => 20, 'accuracy_persen' => 98];
                                            }
                                            
                                            $evalResult = $staffManager->evaluasiSOPKerja($staff['id_staff'], $evalData);
                                            if ($evalResult['success']):
                                                $eval = $evalResult['data'];
                                        ?>
                                            <div class="alert alert-warning">
                                                <h6 class="mb-3"><strong>Hasil Evaluasi SOP Kerja</strong></h6>
                                                
                                                <div class="mb-3">
                                                    <?php if ($eval['skor_total'] >= 85): ?>
                                                        <span class="badge bg-success" style="font-size: 1.1rem; padding: 0.7rem;">LULUS</span>
                                                    <?php elseif ($eval['skor_total'] >= 75): ?>
                                                        <span class="badge bg-info" style="font-size: 1.1rem; padding: 0.7rem;">LULUS KONDISIONAL</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger" style="font-size: 1.1rem; padding: 0.7rem;">TIDAK LULUS</span>
                                                    <?php endif; ?>
                                                </div>

                                                <p class="mb-2"><strong>Status Keseluruhan:</strong> <?php echo $eval['status_keseluruhan']; ?></p>
                                                <p><strong>Total Skor:</strong> <span style="font-size: 1.2rem; color: var(--secondary-color);"><?php echo $eval['skor_total']; ?> / 100</span></p>

                                                <table class="table table-sm table-striped">
                                                    <?php foreach ($eval['detail'] as $detail): ?>
                                                        <tr>
                                                            <td><strong><?php echo $detail['nama_kriteria']; ?></strong></td>
                                                            <td>
                                                                <span class="badge bg-primary"><?php echo $detail['skor']; ?></span>
                                                                <small class="text-muted"><?php echo $detail['status']; ?></small>
                                                            </td>
                                                        </tr>
                                                        <?php if (isset($detail['detail'])): ?>
                                                            <tr>
                                                                <td colspan="2"><small class="text-muted"><?php echo $detail['detail']; ?></small></td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </table>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php 
            else:
                echo '<div class="alert alert-danger">Staff tidak ditemukan!</div>';
            endif;

        endif; // END MODE VIEW
        ?>
    </div>

    <!-- BOOTSTRAP JS & CUSTOM JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update form fields dynamically based on staff type
        function updateFormFields() {
            const jenisStaff = document.getElementById('jenisStaffForm').value;
            const dinamicFields = document.getElementById('dinamicFields');
            
            let html = '';

            if (jenisStaff === 'SupirTruk') {
                html = `
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nomor SIM B</label>
                            <input type="text" class="form-control" name="nomor_sim_b" required placeholder="Nomor SIM B">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Uang Makan Jalan (Rp)</label>
                            <input type="number" class="form-control" name="uang_makan_jalan" required placeholder="500000">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rute Operasional</label>
                            <input type="text" class="form-control" name="rute_operasional" required placeholder="Contoh: Jakarta-Bandung">
                        </div>
                    </div>
                `;
            } else if (jenisStaff === 'AdminGudang') {
                html = `
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Shift Kerja</label>
                            <select class="form-select" name="shift_kerja" required>
                                <option>-- Pilih Shift --</option>
                                <option value="Pagi">Pagi</option>
                                <option value="Siang">Siang</option>
                                <option value="Malam">Malam</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Zona Gudang</label>
                            <input type="text" class="form-control" name="zona_gudang" required placeholder="Contoh: Zona A">
                        </div>
                    </div>
                `;
            } else if (jenisStaff === 'KurirMotor') {
                html = `
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Plat Nomor Motor</label>
                            <input type="text" class="form-control" name="plat_nomor_motor" required placeholder="Contoh: B 1234 XYZ">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Area Cakupan</label>
                            <input type="text" class="form-control" name="area_cakupan" required placeholder="Contoh: Jakarta Pusat">
                        </div>
                    </div>
                `;
            }

            dinamicFields.innerHTML = html;
        }

        // Filter table by search and jenis staff
        document.getElementById('searchInput')?.addEventListener('keyup', filterTable);
        document.getElementById('filterJenis')?.addEventListener('change', filterTable);

        function filterTable() {
            const searchValue = document.getElementById('searchInput')?.value.toLowerCase() || '';
            const filterValue = document.getElementById('filterJenis')?.value || '';
            const rows = document.querySelectorAll('#staffTable tr');

            rows.forEach(row => {
                const nama = row.cells[1]?.textContent.toLowerCase() || '';
                const idCode = row.cells[0]?.textContent.toLowerCase() || '';
                const jenis = row.cells[2]?.textContent || '';

                const matchSearch = nama.includes(searchValue) || idCode.includes(searchValue);
                const matchFilter = !filterValue || jenis.includes(filterValue);

                row.style.display = (matchSearch && matchFilter) ? '' : 'none';
            });
        }
    </script>
</body>
</html>