<?php
// Armada/index.php - PASTIKAN NAMA FILE "index.php" BUKAN "Index.php"
require_once dirname(__DIR__) . '/config/koneksi.php';
require_once __DIR__ . '/Armada.php';
require_once __DIR__ . '/TrukDarat.php';
require_once __DIR__ . '/KapalLaut.php';
require_once __DIR__ . '/PesawatKargo.php';

session_start();

// Proses CRUD
$message = '';
$error = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if (Armada::deleteArmada($conn, $id)) {
        $message = "Armada berhasil dihapus!";
        // Refresh halaman setelah hapus
        header("Location: index.php?msg=" . urlencode($message));
        exit();
    } else {
        $error = "Gagal menghapus armada!";
    }
}

// Handle pesan dari redirect
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $jenis_armada = $_POST['jenis_armada'];
    $armada = null;
    
    try {
        switch ($jenis_armada) {
            case 'TrukDarat':
                $armada = new TrukDarat($conn);
                $armada->setIdArmadaCode($_POST['id_armada_code']);
                $armada->setKapasitasMaksimal($_POST['kapasitas']);
                $armada->setStatusKelaikan($_POST['status']);
                $armada->setBiayaOperasionalDasar($_POST['biaya_dasar']);
                $armada->setJumlahRoda($_POST['jumlah_roda']);
                $armada->setRuteTol($_POST['rute_tol']);
                break;
                
            case 'KapalLaut':
                $armada = new KapalLaut($conn);
                $armada->setIdArmadaCode($_POST['id_armada_code']);
                $armada->setKapasitasMaksimal($_POST['kapasitas']);
                $armada->setStatusKelaikan($_POST['status']);
                $armada->setBiayaOperasionalDasar($_POST['biaya_dasar']);
                $armada->setNamaDermaga($_POST['nama_dermaga']);
                $armada->setJenisKontainer($_POST['jenis_kontainer']);
                break;
                
            case 'PesawatKargo':
                $armada = new PesawatKargo($conn);
                $armada->setIdArmadaCode($_POST['id_armada_code']);
                $armada->setKapasitasMaksimal($_POST['kapasitas']);
                $armada->setStatusKelaikan($_POST['status']);
                $armada->setBiayaOperasionalDasar($_POST['biaya_dasar']);
                $armada->setBatasKetinggian($_POST['batas_ketinggian']);
                $armada->setIzinPenerbangan($_POST['izin_penerbangan']);
                break;
        }
        
        if ($armada && $armada->simpanArmada()) {
            $message = "Armada berhasil ditambahkan!";
            header("Location: index.php?msg=" . urlencode($message));
            exit();
        } else {
            $error = "Gagal menambahkan armada!";
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Get all armada
$armada_list = Armada::getAllArmada($conn);
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            color: #333;
            margin-bottom: 5px;
        }
        
        .header p {
            color: #666;
        }
        
        .dashboard-link {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .dashboard-link:hover {
            background: #5a67d8;
        }
        
        .content {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 20px;
        }
        
        .form-section, .list-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-section h2, .list-section h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        textarea {
            resize: vertical;
            min-height: 60px;
        }
        
        button {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        
        button:hover {
            background: #5a67d8;
        }
        
        .alert {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
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
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #f8f9fa;
            color: #333;
            font-weight: 600;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 12px;
        }
        
        .btn-delete:hover {
            background: #c82333;
        }
        
        .card-detail {
            margin-top: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            font-size: 12px;
        }
        
        .jenis-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .truk { background: #28a745; color: white; }
        .kapal { background: #17a2b8; color: white; }
        .pesawat { background: #ffc107; color: #333; }
        
        @media (max-width: 768px) {
            .content {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚛 Manajemen Armada Ekspedisi</h1>
            <p>Sistem Manajemen Armada - Truk Darat | Kapal Laut | Pesawat Kargo</p>
            <a href="../Dashboard.php" class="dashboard-link">← Kembali ke Dashboard</a>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="content">
            <!-- Form Tambah Armada -->
            <div class="form-section">
                <h2>➕ Tambah Armada Baru</h2>
                <form method="POST" onsubmit="return validateForm()">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="form-group">
                        <label>Jenis Armada</label>
                        <select name="jenis_armada" id="jenis_armada" required>
                            <option value="">Pilih Jenis Armada</option>
                            <option value="TrukDarat">🚛 Truk Darat</option>
                            <option value="KapalLaut">⛴️ Kapal Laut</option>
                            <option value="PesawatKargo">✈️ Pesawat Kargo</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>ID Armada Code</label>
                        <input type="text" name="id_armada_code" required placeholder="Contoh: TRK001/KPL001/PSW001">
                    </div>
                    
                    <div class="form-group">
                        <label>Kapasitas Maksimal (kg)</label>
                        <input type="number" step="0.01" name="kapasitas" required>
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
                        <input type="number" step="0.01" name="biaya_dasar" required>
                    </div>
                    
                    <!-- Field untuk Truk Darat -->
                    <div id="truk_fields" style="display:none;">
                        <div class="form-group">
                            <label>Jumlah Roda</label>
                            <input type="number" name="jumlah_roda" placeholder="Contoh: 6, 8, 10">
                        </div>
                        <div class="form-group">
                            <label>Rute Tol (pisah dengan koma)</label>
                            <textarea name="rute_tol" placeholder="Contoh: Tol Dalam Kota, Tol Lingkar Luar"></textarea>
                        </div>
                    </div>
                    
                    <!-- Field untuk Kapal Laut -->
                    <div id="kapal_fields" style="display:none;">
                        <div class="form-group">
                            <label>Nama Dermaga</label>
                            <input type="text" name="nama_dermaga" placeholder="Contoh: Tanjung Priok">
                        </div>
                        <div class="form-group">
                            <label>Jenis Kontainer</label>
                            <select name="jenis_kontainer">
                                <option value="Standard">📦 Standard</option>
                                <option value="Refrigerated">❄️ Refrigerated</option>
                                <option value="Open Top">🔓 Open Top</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Field untuk Pesawat Kargo -->
                    <div id="pesawat_fields" style="display:none;">
                        <div class="form-group">
                            <label>Batas Ketinggian (meter)</label>
                            <input type="number" step="0.01" name="batas_ketinggian" placeholder="Contoh: 10000">
                        </div>
                        <div class="form-group">
                            <label>Izin Penerbangan Khusus</label>
                            <input type="text" name="izin_penerbangan" placeholder="Contoh: Cargo Malam, Internasional">
                        </div>
                    </div>
                    
                    <button type="submit">💾 Simpan Armada</button>
                </form>
            </div>
            
            <!-- Daftar Armada -->
            <div class="list-section">
                <h2>📋 Daftar Armada</h2>
                <?php if (count($armada_list) > 0): ?>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Jenis</th>
                                    <th>Kapasitas</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($armada_list as $armada): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($armada['id_armada_code']); ?></td>
                                        <td>
                                            <span class="jenis-badge 
                                                <?php 
                                                    echo $armada['jenis_armada'] == 'TrukDarat' ? 'truk' : 
                                                        ($armada['jenis_armada'] == 'KapalLaut' ? 'kapal' : 'pesawat'); 
                                                ?>">
                                                <?php 
                                                    echo $armada['jenis_armada'] == 'TrukDarat' ? '🚛 Truk Darat' : 
                                                        ($armada['jenis_armada'] == 'KapalLaut' ? '⛴️ Kapal Laut' : '✈️ Pesawat Kargo'); 
                                                ?>
                                            </span>
                                        </td>
                                        <td><?php echo number_format($armada['kapasitas_maksimal_kg'], 0, ',', '.'); ?> kg</td>
                                        <td>
                                            <?php 
                                                $status_color = $armada['status_kelaikan'] == 'Laik' ? '#28a745' : '#dc3545';
                                                $status_icon = $armada['status_kelaikan'] == 'Laik' ? '✅' : '❌';
                                                echo '<span style="color: ' . $status_color . '; font-weight: bold;">' . $status_icon . ' ' . $armada['status_kelaikan'] . '</span>';
                                            ?>
                                        </td>
                                        <td>
                                            <a href="?delete=<?php echo $armada['id_armada']; ?>" 
                                               class="btn-delete" 
                                               onclick="return confirm('Yakin hapus armada ini?')">🗑️ Hapus</a>
                                        </td>
                                    </tr>
                                    
                                    <!-- Demo Polymorphism: Tampilkan hasil perhitungan biaya & kelayakan -->
                                    <tr style="background:#f8f9fa;">
                                        <td colspan="5" style="padding: 10px;">
                                            <div class="card-detail">
                                                <strong>📊 Hasil Perhitungan (Polymorphism):</strong><br>
                                                <?php
                                                // Demonstrasi polymorphism
                                                try {
                                                    switch ($armada['jenis_armada']) {
                                                        case 'TrukDarat':
                                                            $demoArmada = new TrukDarat($conn);
                                                            $demoArmada->setBiayaOperasionalDasar($armada['biaya_operasional_dasar']);
                                                            $demoArmada->setStatusKelaikan($armada['status_kelaikan']);
                                                            $demoArmada->setKapasitasMaksimal($armada['kapasitas_maksimal_kg']);
                                                            $demoArmada->setRuteTol($armada['rute_tol'] ?? '');
                                                            break;
                                                        case 'KapalLaut':
                                                            $demoArmada = new KapalLaut($conn);
                                                            $demoArmada->setBiayaOperasionalDasar($armada['biaya_operasional_dasar']);
                                                            $demoArmada->setStatusKelaikan($armada['status_kelaikan']);
                                                            $demoArmada->setNamaDermaga($armada['nama_dermaga'] ?? '');
                                                            $demoArmada->setJenisKontainer($armada['jenis_kontainer'] ?? '');
                                                            break;
                                                        default:
                                                            $demoArmada = new PesawatKargo($conn);
                                                            $demoArmada->setBiayaOperasionalDasar($armada['biaya_operasional_dasar']);
                                                            $demoArmada->setStatusKelaikan($armada['status_kelaikan']);
                                                            $demoArmada->setBatasKetinggian($armada['batas_ketinggian'] ?? 0);
                                                            $demoArmada->setIzinPenerbangan($armada['izin_penerbangan_khusus'] ?? '');
                                                            break;
                                                    }
                                                    
                                                    if (isset($demoArmada)) {
                                                        echo "💰 Biaya Operasional Total: Rp " . number_format($demoArmada->hitungBiayaOperasional(), 0, ',', '.') . "<br>";
                                                        echo "🔍 Hasil Cek Kelayakan:<br>";
                                                        $hasilKelayakan = $demoArmada->cekKelayakanJalan();
                                                        foreach ($hasilKelayakan as $cek) {
                                                            echo "&nbsp;&nbsp;&nbsp;• " . $cek . "<br>";
                                                        }
                                                    }
                                                } catch (Exception $e) {
                                                    echo "⚠️ Error demo: " . $e->getMessage();
                                                }
                                                ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; padding: 40px; color: #999;">Belum ada data armada. Silakan tambah armada baru.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        const jenisArmada = document.getElementById('jenis_armada');
        const trukFields = document.getElementById('truk_fields');
        const kapalFields = document.getElementById('kapal_fields');
        const pesawatFields = document.getElementById('pesawat_fields');
        
        function validateForm() {
            const jenis = jenisArmada.value;
            if (jenis === 'TrukDarat') {
                const jumlahRoda = document.querySelector('input[name="jumlah_roda"]').value;
                if (!jumlahRoda) {
                    alert('Jumlah roda harus diisi untuk Truk Darat!');
                    return false;
                }
            } else if (jenis === 'KapalLaut') {
                const namaDermaga = document.querySelector('input[name="nama_dermaga"]').value;
                if (!namaDermaga) {
                    alert('Nama dermaga harus diisi untuk Kapal Laut!');
                    return false;
                }
            } else if (jenis === 'PesawatKargo') {
                const batasKetinggian = document.querySelector('input[name="batas_ketinggian"]').value;
                if (!batasKetinggian) {
                    alert('Batas ketinggian harus diisi untuk Pesawat Kargo!');
                    return false;
                }
            }
            return true;
        }
        
        if (jenisArmada) {
            jenisArmada.addEventListener('change', function() {
                trukFields.style.display = 'none';
                kapalFields.style.display = 'none';
                pesawatFields.style.display = 'none';
                
                if (this.value === 'TrukDarat') {
                    trukFields.style.display = 'block';
                } else if (this.value === 'KapalLaut') {
                    kapalFields.style.display = 'block';
                } else if (this.value === 'PesawatKargo') {
                    pesawatFields.style.display = 'block';
                }
            });
        }
    </script>
</body>
</html>