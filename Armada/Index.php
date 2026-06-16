<?php
// Armada/Index.php - TAMPILAN PERSIS PELANGGAN
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "ekspedisi_logistik";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// ============================================
// ABSTRACT CLASS ARMADA
// ============================================
abstract class Armada {
    protected $conn;
    private $id_armada;
    private $id_armada_code;
    private $kapasitas_maksimal_kg;
    private $status_kelaikan;
    private $biaya_operasional_dasar;
    private $jenis_armada;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function setIdArmada($id) { $this->id_armada = $id; }
    public function setIdArmadaCode($code) { $this->id_armada_code = $code; }
    public function setKapasitasMaksimal($kapasitas) { $this->kapasitas_maksimal_kg = $kapasitas; }
    public function setStatusKelaikan($status) { $this->status_kelaikan = $status; }
    public function setBiayaOperasionalDasar($biaya) { $this->biaya_operasional_dasar = $biaya; }
    public function setJenisArmada($jenis) { $this->jenis_armada = $jenis; }
    
    public function getIdArmada() { return $this->id_armada; }
    public function getIdArmadaCode() { return $this->id_armada_code; }
    public function getKapasitasMaksimal() { return $this->kapasitas_maksimal_kg; }
    public function getStatusKelaikan() { return $this->status_kelaikan; }
    public function getBiayaOperasionalDasar() { return $this->biaya_operasional_dasar; }
    public function getJenisArmada() { return $this->jenis_armada; }
    
    public abstract function hitungBiayaOperasional();
    public abstract function cekKelayakanJalan();
    
    public function simpan() {
        $query = "INSERT INTO armada (id_armada_code, kapasitas_maksimal_kg, status_kelaikan, biaya_operasional_dasar, jenis_armada) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sssss", 
            $this->id_armada_code, 
            $this->kapasitas_maksimal_kg, 
            $this->status_kelaikan, 
            $this->biaya_operasional_dasar, 
            $this->jenis_armada
        );
        
        if ($stmt->execute()) {
            $id = $this->conn->insert_id;
            $stmt->close();
            return $id;
        }
        $stmt->close();
        return false;
    }
    
    public static function getAll($conn) {
        $result = $conn->query("SELECT * FROM armada ORDER BY id_armada DESC");
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
    
    public static function delete($conn, $id) {
        $stmt = $conn->prepare("DELETE FROM armada WHERE id_armada = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}

// ============================================
// CLASS TRUKDARAT
// ============================================
class TrukDarat extends Armada {
    private $jumlah_roda;
    private $rute_tol;
    
    public function __construct($conn) {
        parent::__construct($conn);
        $this->setJenisArmada('TrukDarat');
    }
    
    public function setJumlahRoda($roda) { $this->jumlah_roda = $roda; }
    public function getJumlahRoda() { return $this->jumlah_roda; }
    public function setRuteTol($rute) { $this->rute_tol = $rute; }
    public function getRuteTol() { return $this->rute_tol; }
    
    public function hitungBiayaOperasional() {
        $biaya = $this->getBiayaOperasionalDasar();
        if (!empty($this->rute_tol)) {
            $jumlah_tol = substr_count($this->rute_tol, ',') + 1;
            $biaya += $jumlah_tol * 75000;
        }
        return $biaya;
    }
    
    public function cekKelayakanJalan() {
        $hasil = [];
        if ($this->getStatusKelaikan() == 'Laik') {
            $hasil[] = "✅ Mesin dalam kondisi baik";
            $hasil[] = "✅ Rem berfungsi normal";
            $hasil[] = "🎉 STATUS: LAIK BEROPERASI";
        } else {
            $hasil[] = "❌ Mesin bermasalah";
            $hasil[] = "❌ STATUS: TIDAK LAIK OPERASI";
        }
        return $hasil;
    }
}

// ============================================
// CLASS KAPALLAUT
// ============================================
class KapalLaut extends Armada {
    private $nama_dermaga;
    private $jenis_kontainer;
    
    public function __construct($conn) {
        parent::__construct($conn);
        $this->setJenisArmada('KapalLaut');
    }
    
    public function setNamaDermaga($dermaga) { $this->nama_dermaga = $dermaga; }
    public function getNamaDermaga() { return $this->nama_dermaga; }
    public function setJenisKontainer($kontainer) { $this->jenis_kontainer = $kontainer; }
    public function getJenisKontainer() { return $this->jenis_kontainer; }
    
    public function hitungBiayaOperasional() {
        $biaya = $this->getBiayaOperasionalDasar() + 150000;
        if ($this->jenis_kontainer == 'Refrigerated') {
            $biaya += 200000;
        }
        return $biaya;
    }
    
    public function cekKelayakanJalan() {
        $hasil = [];
        if ($this->getStatusKelaikan() == 'Laik') {
            $hasil[] = "✅ Manifes laut lengkap";
            $hasil[] = "✅ Sistem navigasi berfungsi";
            $hasil[] = "🎉 STATUS: LAIK BERLAYAR";
        } else {
            $hasil[] = "❌ Kapal tidak laik laut";
            $hasil[] = "❌ STATUS: TIDAK LAIK OPERASI";
        }
        return $hasil;
    }
}

// ============================================
// CLASS PESAWATKARGO
// ============================================
class PesawatKargo extends Armada {
    private $batas_ketinggian;
    private $izin_penerbangan_khusus;
    
    public function __construct($conn) {
        parent::__construct($conn);
        $this->setJenisArmada('PesawatKargo');
    }
    
    public function setBatasKetinggian($ketinggian) { $this->batas_ketinggian = $ketinggian; }
    public function getBatasKetinggian() { return $this->batas_ketinggian; }
    public function setIzinPenerbangan($izin) { $this->izin_penerbangan_khusus = $izin; }
    public function getIzinPenerbangan() { return $this->izin_penerbangan_khusus; }
    
    public function hitungBiayaOperasional() {
        $biaya = $this->getBiayaOperasionalDasar() + 250000;
        if ($this->izin_penerbangan_khusus == 'Cargo Malam') {
            $biaya += 150000;
        }
        return $biaya;
    }
    
    public function cekKelayakanJalan() {
        $hasil = [];
        if ($this->getStatusKelaikan() == 'Laik') {
            $hasil[] = "✅ Izin navigasi udara valid";
            $hasil[] = "✅ Mesin pesawat siap";
            $hasil[] = "🎉 STATUS: LAIK TERBANG";
        } else {
            $hasil[] = "❌ Pesawat tidak laik terbang";
            $hasil[] = "❌ STATUS: TIDAK LAIK OPERASI";
        }
        return $hasil;
    }
}

// ============================================
// PROSES CRUD
// ============================================
$message = '';
$error = '';

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if (Armada::delete($conn, $id)) {
        $message = "✅ Armada berhasil dihapus!";
    } else {
        $error = "❌ Gagal menghapus armada!";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jenis = $_POST['jenis_armada'];
    $armada = null;
    
    switch ($jenis) {
        case 'TrukDarat':
            $armada = new TrukDarat($conn);
            $armada->setIdArmadaCode($_POST['id_armada_code']);
            $armada->setKapasitasMaksimal($_POST['kapasitas']);
            $armada->setStatusKelaikan($_POST['status']);
            $armada->setBiayaOperasionalDasar($_POST['biaya_dasar']);
            $armada->setJumlahRoda($_POST['jumlah_roda'] ?? 4);
            $armada->setRuteTol($_POST['rute_tol'] ?? '');
            break;
            
        case 'KapalLaut':
            $armada = new KapalLaut($conn);
            $armada->setIdArmadaCode($_POST['id_armada_code']);
            $armada->setKapasitasMaksimal($_POST['kapasitas']);
            $armada->setStatusKelaikan($_POST['status']);
            $armada->setBiayaOperasionalDasar($_POST['biaya_dasar']);
            $armada->setNamaDermaga($_POST['nama_dermaga'] ?? '');
            $armada->setJenisKontainer($_POST['jenis_kontainer'] ?? 'Standard');
            break;
            
        case 'PesawatKargo':
            $armada = new PesawatKargo($conn);
            $armada->setIdArmadaCode($_POST['id_armada_code']);
            $armada->setKapasitasMaksimal($_POST['kapasitas']);
            $armada->setStatusKelaikan($_POST['status']);
            $armada->setBiayaOperasionalDasar($_POST['biaya_dasar']);
            $armada->setBatasKetinggian($_POST['batas_ketinggian'] ?? 10000);
            $armada->setIzinPenerbangan($_POST['izin_penerbangan'] ?? '');
            break;
    }
    
    if ($armada && $armada->simpan()) {
        $message = "✅ Armada berhasil ditambahkan!";
        header("Location: Index.php?msg=" . urlencode($message));
        exit();
    } else {
        $error = "❌ Gagal menambahkan armada!";
    }
}

if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
}

$armada_list = Armada::getAll($conn);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Armada - Ekspedisi Logistik</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
            display: flex;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 250px;
            background: #1a1a2e;
            color: white;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 2px 0 15px rgba(0,0,0,0.3);
        }

        .sidebar-brand {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-brand .logo-icon {
            font-size: 40px;
            display: block;
            margin-bottom: 8px;
        }

        .sidebar-brand h2 {
            font-size: 20px;
            color: white;
        }

        .sidebar-brand small {
            color: #aaa;
            font-size: 11px;
        }

        .sidebar-menu {
            padding: 15px 0;
        }

        .sidebar-menu .menu-label {
            padding: 10px 20px;
            color: #666;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: bold;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #ccc;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
            gap: 12px;
        }

        .sidebar-menu a:hover {
            background: rgba(255,255,255,0.05);
            color: white;
            border-left-color: #3498db;
        }

        .sidebar-menu a.active {
            background: rgba(52, 152, 219, 0.15);
            color: white;
            border-left-color: #3498db;
        }

        .sidebar-menu a .icon {
            font-size: 18px;
            width: 28px;
            text-align: center;
        }

        .sidebar-menu a .menu-text {
            font-size: 14px;
        }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 25px;
            min-height: 100vh;
        }

        /* ===== HEADER ===== */
        .header {
            background: white;
            padding: 20px 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .header h1 {
            color: #2c3e50;
            font-size: 24px;
        }

        .header p {
            color: #888;
            font-size: 14px;
            margin-top: 3px;
        }

        .hamburger {
            display: none;
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #2c3e50;
            padding: 5px 10px;
            float: right;
        }

        /* ===== TABLE ===== */
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .table-header {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .table-header h2 {
            font-size: 18px;
            color: #2c3e50;
        }

        .btn-add {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-add:hover {
            background: #2c3e50;
            transform: scale(1.02);
        }

        .table-wrapper {
            overflow-x: auto;
            padding: 0 20px 20px 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-truk {
            background: #27ae60;
            color: white;
        }

        .badge-kapal {
            background: #2980b9;
            color: white;
        }

        .badge-pesawat {
            background: #f39c12;
            color: white;
        }

        .btn-hapus {
            background: #e74c3c;
            color: white;
            padding: 5px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
            transition: all 0.3s;
            display: inline-block;
        }

        .btn-hapus:hover {
            background: #c0392b;
            transform: scale(1.05);
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #999;
        }

        .empty-state .empty-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        /* ===== MODAL ===== */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .modal.open {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }

        .modal-content .close-btn {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 28px;
            cursor: pointer;
            color: #999;
            background: none;
            border: none;
        }

        .modal-content h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
        }

        .modal-content .form-group {
            margin-bottom: 15px;
        }

        .modal-content label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .modal-content input,
        .modal-content select {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }

        .modal-content input:focus,
        .modal-content select:focus {
            outline: none;
            border-color: #3498db;
        }

        .modal-content button[type="submit"] {
            background: #3498db;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
        }

        .modal-content button[type="submit"]:hover {
            background: #2c3e50;
        }

        .modal-content .field-group {
            display: none;
        }

        /* ===== ALERT ===== */
        .alert {
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .sidebar {
                left: -100%;
                width: 280px;
            }

            .sidebar.open {
                left: 0;
            }

            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .hamburger {
                display: block;
            }

            .header {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .header h1 {
                font-size: 20px;
            }
        }

        .sidebar::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: #1a1a2e;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #3498db;
            border-radius: 10px;
        }
    </style>
</head>
<body>

    <!-- ===== SIDEBAR ===== -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <span class="logo-icon">🚚</span>
            <h2>Cargo Ekspedisi</h2>
            <small>Logistik System</small>
        </div>
        <div class="sidebar-menu">
            <div class="menu-label">Menu Utama</div>
            <a href="../Dashboard.php">
                <span class="icon">📊</span>
                <span class="menu-text">Dashboard</span>
            </a>
            <a href="Index.php" class="active">
                <span class="icon">🚛</span>
                <span class="menu-text">Armada</span>
            </a>
            <a href="../Cargo/Index.php">
                <span class="icon">📦</span>
                <span class="menu-text">Cargo</span>
            </a>
            <a href="../Pelanggan/Index.php">
                <span class="icon">👤</span>
                <span class="menu-text">Pelanggan</span>
            </a>
            <a href="../Pembayaran/Index.php">
                <span class="icon">💳</span>
                <span class="menu-text">Pembayaran</span>
            </a>
            <a href="../Staff/Index.php">
                <span class="icon">👨‍💼</span>
                <span class="menu-text">Staff</span>
            </a>
        </div>
    </div>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="main-content">
        <!-- HEADER -->
        <div class="header">
            <div>
                <h1>🚛 Manajemen Armada</h1>
                <p>Kelola data armada - Truk Darat | Kapal Laut | Pesawat Kargo</p>
            </div>
            <button class="hamburger" id="hamburgerBtn">☰</button>
        </div>

        <!-- ALERT -->
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- TABLE -->
        <div class="table-container">
            <div class="table-header">
                <h2>📋 Daftar Armada</h2>
                <button class="btn-add" onclick="openModal()">➕ Tambah Armada Baru</button>
            </div>
            <div class="table-wrapper">
                <?php if (!empty($armada_list)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Armada</th>
                                <th>Jenis</th>
                                <th>Kapasitas</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($armada_list as $a): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($a['id_armada_code']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($a['id_armada_code']); ?></td>
                                    <td>
                                        <span class="badge <?php 
                                            echo $a['jenis_armada'] == 'TrukDarat' ? 'badge-truk' : 
                                                ($a['jenis_armada'] == 'KapalLaut' ? 'badge-kapal' : 'badge-pesawat'); 
                                        ?>">
                                            <?php 
                                                echo $a['jenis_armada'] == 'TrukDarat' ? '🚛 Truk Darat' : 
                                                    ($a['jenis_armada'] == 'KapalLaut' ? '⛴️ Kapal Laut' : '✈️ Pesawat Kargo'); 
                                            ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format($a['kapasitas_maksimal_kg'], 0, ',', '.'); ?> kg</td>
                                    <td>
                                        <?php echo $a['status_kelaikan'] == 'Laik' ? '✅ Laik' : '❌ Tidak Laik'; ?>
                                    </td>
                                    <td>
                                        <a href="?hapus=<?php echo $a['id_armada']; ?>" class="btn-hapus" onclick="return confirm('Yakin hapus armada ini?')">🗑️ Hapus</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">📭</div>
                        <p>Belum ada data armada</p>
                        <p style="font-size:12px; margin-top:5px;">Klik tombol "Tambah Armada Baru" untuk menambahkan</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ===== MODAL TAMBAH ARMADA ===== -->
    <div class="modal" id="modalArmada">
        <div class="modal-content">
            <button class="close-btn" onclick="closeModal()">×</button>
            <h2>➕ Tambah Armada Baru</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Jenis Armada</label>
                    <select name="jenis_armada" id="jenisArmada" required>
                        <option value="">-- Pilih Jenis Armada --</option>
                        <option value="TrukDarat">🚛 Truk Darat</option>
                        <option value="KapalLaut">⛴️ Kapal Laut</option>
                        <option value="PesawatKargo">✈️ Pesawat Kargo</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Kode Armada</label>
                    <input type="text" name="id_armada_code" required placeholder="TRK001">
                </div>

                <div class="form-group">
                    <label>Kapasitas Maksimal (kg)</label>
                    <input type="number" name="kapasitas" step="0.01" required placeholder="1000">
                </div>

                <div class="form-group">
                    <label>Status Kelaikan</label>
                    <select name="status" required>
                        <option value="Laik">✅ Laik</option>
                        <option value="Tidak Laik">❌ Tidak Laik</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Biaya Operasional Dasar</label>
                    <input type="number" name="biaya_dasar" step="0.01" required placeholder="500000">
                </div>

                <!-- Field Truk Darat -->
                <div class="field-group" id="fieldTruk">
                    <div class="form-group">
                        <label>Jumlah Roda</label>
                        <input type="number" name="jumlah_roda" value="6">
                    </div>
                    <div class="form-group">
                        <label>Rute Tol</label>
                        <input type="text" name="rute_tol" placeholder="Tol Dalam Kota">
                    </div>
                </div>

                <!-- Field Kapal Laut -->
                <div class="field-group" id="fieldKapal">
                    <div class="form-group">
                        <label>Nama Dermaga</label>
                        <input type="text" name="nama_dermaga" placeholder="Tanjung Priok">
                    </div>
                    <div class="form-group">
                        <label>Jenis Kontainer</label>
                        <select name="jenis_kontainer">
                            <option value="Standard">Standard</option>
                            <option value="Refrigerated">Refrigerated</option>
                            <option value="Open Top">Open Top</option>
                        </select>
                    </div>
                </div>

                <!-- Field Pesawat Kargo -->
                <div class="field-group" id="fieldPesawat">
                    <div class="form-group">
                        <label>Batas Ketinggian (meter)</label>
                        <input type="number" name="batas_ketinggian" value="10000">
                    </div>
                    <div class="form-group">
                        <label>Izin Penerbangan Khusus</label>
                        <input type="text" name="izin_penerbangan" placeholder="Cargo Malam">
                    </div>
                </div>

                <button type="submit">💾 Simpan Armada</button>
            </form>
        </div>
    </div>

    <script>
        // HAMBURGER
        document.getElementById('hamburgerBtn').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('open');
        });

        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const hamburger = document.getElementById('hamburgerBtn');
            if (!sidebar.contains(event.target) && !hamburger.contains(event.target) && window.innerWidth <= 768) {
                sidebar.classList.remove('open');
            }
        });

        // MODAL
        function openModal() {
            document.getElementById('modalArmada').classList.add('open');
        }

        function closeModal() {
            document.getElementById('modalArmada').classList.remove('open');
        }

        // Toggle field berdasarkan jenis armada
        document.getElementById('jenisArmada').addEventListener('change', function() {
            document.getElementById('fieldTruk').style.display = 'none';
            document.getElementById('fieldKapal').style.display = 'none';
            document.getElementById('fieldPesawat').style.display = 'none';

            if (this.value === 'TrukDarat') {
                document.getElementById('fieldTruk').style.display = 'block';
            } else if (this.value === 'KapalLaut') {
                document.getElementById('fieldKapal').style.display = 'block';
            } else if (this.value === 'PesawatKargo') {
                document.getElementById('fieldPesawat').style.display = 'block';
            }
        });

        // Close modal klik di luar
        document.getElementById('modalArmada').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>

</body>
</html>