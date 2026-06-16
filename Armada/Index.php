<?php
// Armada/Index.php - GABUNGAN SEMUA CLASS + CRUD
require_once '../config/koneksi.php';

// ============================================
// 1. ABSTRACT CLASS ARMADA (OTAK UTAMA)
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
    
    // SETTERS
    public function setIdArmada($id) { $this->id_armada = $id; }
    public function setIdArmadaCode($code) { $this->id_armada_code = $code; }
    public function setKapasitasMaksimal($kapasitas) { $this->kapasitas_maksimal_kg = $kapasitas; }
    public function setStatusKelaikan($status) { $this->status_kelaikan = $status; }
    public function setBiayaOperasionalDasar($biaya) { $this->biaya_operasional_dasar = $biaya; }
    public function setJenisArmada($jenis) { $this->jenis_armada = $jenis; }
    
    // GETTERS
    public function getIdArmada() { return $this->id_armada; }
    public function getIdArmadaCode() { return $this->id_armada_code; }
    public function getKapasitasMaksimal() { return $this->kapasitas_maksimal_kg; }
    public function getStatusKelaikan() { return $this->status_kelaikan; }
    public function getBiayaOperasionalDasar() { return $this->biaya_operasional_dasar; }
    public function getJenisArmada() { return $this->jenis_armada; }
    
    // ABSTRACT METHODS (Polymorphism)
    public abstract function hitungBiayaOperasional();
    public abstract function cekKelayakanJalan();
    
    // Method simpan ke database
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
    
    // Get all armada
    public static function getAll($conn) {
        $result = $conn->query("SELECT * FROM armada ORDER BY id_armada DESC");
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
    
    // Delete armada
    public static function delete($conn, $id) {
        $stmt = $conn->prepare("DELETE FROM armada WHERE id_armada = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}

// ============================================
// 2. CLASS TRUKDARAT (INHERITANCE)
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
    
    // POLYMORPHISM 1
    public function hitungBiayaOperasional() {
        $biaya = $this->getBiayaOperasionalDasar();
        if (!empty($this->rute_tol)) {
            $jumlah_tol = substr_count($this->rute_tol, ',') + 1;
            $biaya += $jumlah_tol * 75000;
        }
        return $biaya;
    }
    
    // POLYMORPHISM 2
    public function cekKelayakanJalan() {
        $hasil = [];
        if ($this->getStatusKelaikan() == 'Laik') {
            $hasil[] = "✅ Mesin dalam kondisi baik";
            $hasil[] = "✅ Rem berfungsi normal";
            $hasil[] = "✅ Ban dalam kondisi baik";
            $hasil[] = "🎉 STATUS: LAIK BEROPERASI";
        } else {
            $hasil[] = "❌ Mesin bermasalah";
            $hasil[] = "❌ STATUS: TIDAK LAIK OPERASI";
        }
        return $hasil;
    }
}

// ============================================
// 3. CLASS KAPALLAUT (INHERITANCE)
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
    
    // POLYMORPHISM 1
    public function hitungBiayaOperasional() {
        $biaya = $this->getBiayaOperasionalDasar() + 150000;
        if ($this->jenis_kontainer == 'Refrigerated') {
            $biaya += 200000;
        } elseif ($this->jenis_kontainer == 'Open Top') {
            $biaya += 100000;
        }
        return $biaya;
    }
    
    // POLYMORPHISM 2
    public function cekKelayakanJalan() {
        $hasil = [];
        if ($this->getStatusKelaikan() == 'Laik') {
            $hasil[] = "✅ Manifes laut lengkap";
            $hasil[] = "✅ Sistem navigasi berfungsi";
            $hasil[] = "✅ Mesin kapal prima";
            $hasil[] = "🎉 STATUS: LAIK BERLAYAR";
        } else {
            $hasil[] = "❌ Kapal tidak laik laut";
            $hasil[] = "❌ STATUS: TIDAK LAIK OPERASI";
        }
        return $hasil;
    }
}

// ============================================
// 4. CLASS PESAWATKARGO (INHERITANCE)
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
    
    // POLYMORPHISM 1
    public function hitungBiayaOperasional() {
        $biaya = $this->getBiayaOperasionalDasar() + 250000;
        if ($this->izin_penerbangan_khusus == 'Cargo Malam') {
            $biaya += 150000;
        } elseif ($this->izin_penerbangan_khusus == 'Internasional') {
            $biaya += 500000;
        }
        return $biaya;
    }
    
    // POLYMORPHISM 2
    public function cekKelayakanJalan() {
        $hasil = [];
        if ($this->getStatusKelaikan() == 'Laik') {
            $hasil[] = "✅ Izin navigasi udara valid";
            $hasil[] = "✅ Mesin pesawat siap";
            $hasil[] = "✅ Sistem hidrolik normal";
            $hasil[] = "🎉 STATUS: LAIK TERBANG";
        } else {
            $hasil[] = "❌ Pesawat tidak laik terbang";
            $hasil[] = "❌ STATUS: TIDAK LAIK OPERASI";
        }
        return $hasil;
    }
}

// ============================================
// 5. PROSES CRUD
// ============================================
$message = '';
$error = '';

// HAPUS DATA
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if (Armada::delete($conn, $id)) {
        $message = "✅ Armada berhasil dihapus!";
    } else {
        $error = "❌ Gagal menghapus armada!";
    }
}

// TAMBAH DATA
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
        $message = "✅ Armada " . $jenis . " berhasil ditambahkan!";
    } else {
        $error = "❌ Gagal menambahkan armada!";
    }
}

// Ambil semua data
$armada_list = Armada::getAll($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manajemen Armada - Ekspedisi Logistik</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',Arial,sans-serif; background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); padding:20px; min-height:100vh; }
        .container { max-width:1400px; margin:0 auto; }
        .header { background:white; padding:20px; border-radius:10px; margin-bottom:20px; box-shadow:0 2px 10px rgba(0,0,0,0.1); }
        .header h1 { color:#333; margin-bottom:5px; }
        .header p { color:#666; }
        .dashboard-link { display:inline-block; margin-top:10px; padding:8px 20px; background:#667eea; color:white; text-decoration:none; border-radius:5px; transition:0.3s; }
        .dashboard-link:hover { background:#5a67d8; }
        .content { display:grid; grid-template-columns:1fr 1.6fr; gap:20px; }
        .form-section, .list-section { background:white; padding:20px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.1); }
        .form-section h2, .list-section h2 { color:#333; margin-bottom:20px; border-bottom:2px solid #667eea; padding-bottom:10px; }
        .form-group { margin-bottom:15px; }
        label { display:block; margin-bottom:5px; font-weight:600; color:#333; }
        input, select, textarea { width:100%; padding:10px; border:1px solid #ddd; border-radius:5px; font-size:14px; }
        input:focus, select:focus { outline:none; border-color:#667eea; }
        button { background:#667eea; color:white; border:none; padding:12px; border-radius:5px; cursor:pointer; font-size:16px; font-weight:bold; width:100%; transition:0.3s; }
        button:hover { background:#5a67d8; }
        .alert { padding:10px 15px; border-radius:5px; margin-bottom:20px; }
        .alert-success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
        .alert-error { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:10px; text-align:left; border-bottom:1px solid #eee; }
        th { background:#f8f9fa; font-weight:600; color:#333; }
        tr:hover { background:#f8f9fa; }
        .btn-hapus { background:#dc3545; color:white; padding:5px 12px; border-radius:3px; text-decoration:none; font-size:12px; transition:0.3s; }
        .btn-hapus:hover { background:#c82333; }
        .badge { display:inline-block; padding:3px 10px; border-radius:15px; font-size:11px; font-weight:bold; }
        .badge-truk { background:#28a745; color:white; }
        .badge-kapal { background:#17a2b8; color:white; }
        .badge-pesawat { background:#ffc107; color:#333; }
        @media (max-width:900px) { .content { grid-template-columns:1fr; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚛 Manajemen Armada Ekspedisi</h1>
            <p>Implementasi OOP - Encapsulation, Inheritance, Polymorphism</p>
            <a href="../Dashboard.php" class="dashboard-link">← Kembali ke Dashboard</a>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="content">
            <!-- FORM -->
            <div class="form-section">
                <h2>➕ Tambah Armada Baru</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Jenis Armada</label>
                        <select name="jenis_armada" id="jenis" required>
                            <option value="">-- Pilih Jenis Armada --</option>
                            <option value="TrukDarat">🚛 Truk Darat</option>
                            <option value="KapalLaut">⛴️ Kapal Laut</option>
                            <option value="PesawatKargo">✈️ Pesawat Kargo</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>ID Armada Code</label>
                        <input type="text" name="id_armada_code" required placeholder="Contoh: TRK001">
                    </div>
                    
                    <div class="form-group">
                        <label>Kapasitas Maksimal (kg)</label>
                        <input type="number" name="kapasitas" step="0.01" required>
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
                        <input type="number" name="biaya_dasar" step="0.01" required>
                    </div>
                    
                    <!-- Truk Darat -->
                    <div id="truk" style="display:none;">
                        <div class="form-group">
                            <label>Jumlah Roda</label>
                            <input type="number" name="jumlah_roda" value="6">
                        </div>
                        <div class="form-group">
                            <label>Rute Tol</label>
                            <input type="text" name="rute_tol" placeholder="Tol Dalam Kota, Tol Lingkar Luar">
                        </div>
                    </div>
                    
                    <!-- Kapal Laut -->
                    <div id="kapal" style="display:none;">
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
                    
                    <!-- Pesawat Kargo -->
                    <div id="pesawat" style="display:none;">
                        <div class="form-group">
                            <label>Batas Ketinggian (meter)</label>
                            <input type="number" name="batas_ketinggian" value="10000">
                        </div>
                        <div class="form-group">
                            <label>Izin Penerbangan</label>
                            <input type="text" name="izin_penerbangan" placeholder="Cargo Malam / Internasional">
                        </div>
                    </div>
                    
                    <button type="submit">💾 Simpan Armada</button>
                </form>
            </div>
            
            <!-- LIST -->
            <div class="list-section">
                <h2>📋 Daftar Armada</h2>
                <?php if (!empty($armada_list)): ?>
                    <table>
                        <thead>
                            <tr><th>Kode</th><th>Jenis</th><th>Kapasitas</th><th>Status</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($armada_list as $a): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($a['id_armada_code']); ?></strong></td>
                                    <td>
                                        <span class="badge <?php 
                                            echo $a['jenis_armada'] == 'TrukDarat' ? 'badge-truk' : 
                                                ($a['jenis_armada'] == 'KapalLaut' ? 'badge-kapal' : 'badge-pesawat'); 
                                        ?>">
                                            <?php echo $a['jenis_armada'] == 'TrukDarat' ? '🚛 Truk' : 
                                                ($a['jenis_armada'] == 'KapalLaut' ? '⛴️ Kapal' : '✈️ Pesawat'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format($a['kapasitas_maksimal_kg'], 0, ',', '.'); ?> kg</td>
                                    <td><?php echo $a['status_kelaikan'] == 'Laik' ? '✅ Laik' : '❌ Tidak Laik'; ?></td>
                                    <td>
                                        <a href="?hapus=<?php echo $a['id_armada']; ?>" class="btn-hapus" onclick="return confirm('Yakin hapus armada ini?')">🗑️ Hapus</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="text-align:center;padding:40px;color:#999;">Belum ada data armada. Silakan tambah armada baru.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('jenis').addEventListener('change', function() {
            document.getElementById('truk').style.display = 'none';
            document.getElementById('kapal').style.display = 'none';
            document.getElementById('pesawat').style.display = 'none';
            if (this.value === 'TrukDarat') document.getElementById('truk').style.display = 'block';
            else if (this.value === 'KapalLaut') document.getElementById('kapal').style.display = 'block';
            else if (this.value === 'PesawatKargo') document.getElementById('pesawat').style.display = 'block';
        });
    </script>
</body>
</html>