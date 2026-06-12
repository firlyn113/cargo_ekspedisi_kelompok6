<?php
/**
 * File: index.php
 * Fungsi: Halaman utama modul kargo (menggabungkan semua fitur)
 */

session_start();
require_once '../config/koneksi.php';
require_once 'KargoReguler.php';
require_once 'KargoBahanKimia.php';
require_once 'KargoPecahBelah.php';

$message = '';
if(isset($_SESSION['kargo_message'])) {
    $message = $_SESSION['kargo_message'];
    unset($_SESSION['kargo_message']);
}

// Ambil semua data kargo
$sql = "SELECT * FROM kargo ORDER BY created_at DESC";
$result = $koneksi->query($sql);
$data_kargo = [];
if($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data_kargo[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modul Kargo - Sistem Ekspedisi Logistik</title>
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
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
        }
        
        .content {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 20px;
        }
        
        .form-section, .table-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-section h2, .table-section h2 {
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
        
        button {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        
        button:hover {
            background: #5a67d8;
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
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .badge-reguler { background: #48bb78; color: white; }
        .badge-kimia { background: #ed8936; color: white; }
        .badge-pecah { background: #e53e3e; color: white; }
        
        .btn-hapus, .btn-edit {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 2px;
        }
        
        .btn-hapus {
            background: #e53e3e;
            color: white;
        }
        
        .btn-edit {
            background: #48bb78;
            color: white;
        }
        
        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #c6f6d5;
            color: #22543d;
            border: 1px solid #9ae6b4;
        }
        
        .alert-error {
            background: #fed7d7;
            color: #742a2a;
            border: 1px solid #fc8181;
        }
        
        .dynamic-fields {
            margin-top: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            display: none;
        }
        
        .dynamic-fields.active {
            display: block;
        }
        
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
            <h1>📦 Modul Manajemen Kargo</h1>
            <p>Sistem Manajemen Reservasi & Ekspedisi Logistik Terpadu</p>
        </div>
        
        <?php if($message): ?>
        <div class="alert alert-<?php echo $message['status'] ? 'success' : 'error'; ?>">
            <?php echo $message['pesan']; ?>
            <?php if(isset($message['tarif'])): ?>
                <br>💰 Total Tarif: Rp <?php echo number_format($message['tarif'], 0, ',', '.'); ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <div class="stats">
            <div class="stat-card">
                <h3>Total Kargo</h3>
                <div class="number"><?php echo count($data_kargo); ?></div>
            </div>
            <div class="stat-card">
                <h3>Kargo Reguler</h3>
                <div class="number">
                    <?php 
                    $reguler = array_filter($data_kargo, function($item) {
                        return $item['jenis_kargo'] == 'Reguler';
                    });
                    echo count($reguler);
                    ?>
                </div>
            </div>
            <div class="stat-card">
                <h3>Kargo Bahan Kimia</h3>
                <div class="number">
                    <?php 
                    $kimia = array_filter($data_kargo, function($item) {
                        return $item['jenis_kargo'] == 'BahanKimia';
                    });
                    echo count($kimia);
                    ?>
                </div>
            </div>
            <div class="stat-card">
                <h3>Kargo Pecah Belah</h3>
                <div class="number">
                    <?php 
                    $pecah = array_filter($data_kargo, function($item) {
                        return $item['jenis_kargo'] == 'PecahBelah';
                    });
                    echo count($pecah);
                    ?>
                </div>
            </div>
        </div>
        
        <div class="content">
            <!-- Form Tambah Kargo -->
            <div class="form-section">
                <h2>➕ Tambah Kargo Baru</h2>
                <form method="POST" action="proses_kargo.php" id="formKargo">
                    <input type="hidden" name="action" value="tambah">
                    
                    <div class="form-group">
                        <label>ID Resi *</label>
                        <input type="text" name="id_resi" required placeholder="Contoh: RESI2026001">
                    </div>
                    
                    <div class="form-group">
                        <label>Nama Pengirim *</label>
                        <input type="text" name="pengirim" required placeholder="Nama lengkap pengirim">
                    </div>
                    
                    <div class="form-group">
                        <label>Kota Tujuan *</label>
                        <input type="text" name="kota_tujuan" required placeholder="Contoh: Jakarta, Surabaya, dll">
                    </div>
                    
                    <div class="form-group">
                        <label>Berat Barang (kg) *</label>
                        <input type="number" name="berat_barang" step="0.01" required placeholder="Berat dalam kilogram">
                    </div>
                    
                    <div class="form-group">
                        <label>Tarif Dasar per kg (Rp) *</label>
                        <input type="number" name="tarif_dasar" step="100" required placeholder="Tarif per kilogram">
                    </div>
                    
                    <div class="form-group">
                        <label>Jenis Kargo *</label>
                        <select name="jenis_kargo" id="jenisKargo" required>
                            <option value="">Pilih Jenis Kargo</option>
                            <option value="Reguler">📦 Reguler</option>
                            <option value="BahanKimia">🧪 Bahan Kimia</option>
                            <option value="PecahBelah">🥚 Pecah Belah</option>
                        </select>
                    </div>
                    
                    <!-- Dynamic Fields untuk Reguler -->
                    <div id="fieldsReguler" class="dynamic-fields">
                        <h3>Data Kargo Reguler</h3>
                        <div class="form-group">
                            <label>Jenis Paket</label>
                            <select name="jenis_paket">
                                <option value="Koli">Koli</option>
                                <option value="Dus">Dus</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Estimasi Hari</label>
                            <input type="number" name="estimasi_hari" value="3">
                        </div>
                    </div>
                    
                    <!-- Dynamic Fields untuk Bahan Kimia -->
                    <div id="fieldsKimia" class="dynamic-fields">
                        <h3>Data Kargo Bahan Kimia</h3>
                        <div class="form-group">
                            <label>Tingkat Bahaya (1-9)</label>
                            <input type="number" name="tingkat_bahaya" min="1" max="9">
                        </div>
                        <div class="form-group">
                            <label>Jenis Sertifikasi</label>
                            <input type="text" name="jenis_sertifikasi" placeholder="Contoh: MSDS, IMDG Code">
                        </div>
                    </div>
                    
                    <!-- Dynamic Fields untuk Pecah Belah -->
                    <div id="fieldsPecah" class="dynamic-fields">
                        <h3>Data Kargo Pecah Belah</h3>
                        <div class="form-group">
                            <label>Ketebalan Bubble Wrap (cm)</label>
                            <input type="number" name="ketebalan_bubble_wrap" step="0.5">
                        </div>
                        <div class="form-group">
                            <label>Biaya Asuransi Wajib (Rp)</label>
                            <input type="number" name="biaya_asuransi_wajib" step="1000" placeholder="Kosongkan untuk auto">
                        </div>
                    </div>
                    
                    <button type="submit">💾 Simpan Kargo</button>
                </form>
            </div>
            
            <!-- Tabel Data Kargo -->
            <div class="table-section">
                <h2>📋 Daftar Kargo</h2>
                <?php if(count($data_kargo) > 0): ?>
                <table>
                    <thead>
                        <table>
                            <th>ID Resi</th>
                            <th>Pengirim</th>
                            <th>Kota Tujuan</th>
                            <th>Berat (kg)</th>
                            <th>Jenis</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data_kargo as $kargo): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($kargo['id_resi']); ?></td>
                            <td><?php echo htmlspecialchars($kargo['pengirim']); ?></td>
                            <td><?php echo htmlspecialchars($kargo['kota_tujuan']); ?></td>
                            <td><?php echo number_format($kargo['berat_barang'], 2); ?></td>
                            <td>
                                <span class="badge badge-<?php 
                                    echo $kargo['jenis_kargo'] == 'Reguler' ? 'reguler' : 
                                        ($kargo['jenis_kargo'] == 'BahanKimia' ? 'kimia' : 'pecah'); 
                                ?>">
                                    <?php echo $kargo['jenis_kargo']; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn-hapus" onclick="hapusKargo(<?php echo $kargo['id_kargo']; ?>)">Hapus</button>
                                <button class="btn-edit" onclick="editKargo(<?php echo $kargo['id_kargo']; ?>)">Edit</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p style="text-align: center; padding: 40px; color: #999;">Belum ada data kargo. Silakan tambah kargo baru.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Modal Edit (Sederhana) -->
    <div id="modalEdit" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
        <div style="background:white; max-width:500px; margin:50px auto; padding:20px; border-radius:10px;">
            <h3>Edit Kargo</h3>
            <form method="POST" action="proses_kargo.php">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id_kargo" id="edit_id">
                <div class="form-group">
                    <label>Pengirim</label>
                    <input type="text" name="pengirim" id="edit_pengirim" required>
                </div>
                <div class="form-group">
                    <label>Kota Tujuan</label>
                    <input type="text" name="kota_tujuan" id="edit_kota" required>
                </div>
                <div class="form-group">
                    <label>Berat Barang (kg)</label>
                    <input type="number" name="berat_barang" id="edit_berat" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Tarif Dasar (Rp)</label>
                    <input type="number" name="tarif_dasar" id="edit_tarif" step="100" required>
                </div>
                <button type="submit">Update</button>
                <button type="button" onclick="tutupModal()" style="background:#ccc; margin-top:10px;">Batal</button>
            </form>
        </div>
    </div>
    
    <script>
        // Dynamic form fields
        const jenisKargo = document.getElementById('jenisKargo');
        const fieldsReguler = document.getElementById('fieldsReguler');
        const fieldsKimia = document.getElementById('fieldsKimia');
        const fieldsPecah = document.getElementById('fieldsPecah');
        
        jenisKargo.addEventListener('change', function() {
            fieldsReguler.classList.remove('active');
            fieldsKimia.classList.remove('active');
            fieldsPecah.classList.remove('active');
            
            if(this.value === 'Reguler') {
                fieldsReguler.classList.add('active');
                setRequired('reguler', true);
            } else if(this.value === 'BahanKimia') {
                fieldsKimia.classList.add('active');
                setRequired('kimia', true);
            } else if(this.value === 'PecahBelah') {
                fieldsPecah.classList.add('active');
                setRequired('pecah', true);
            } else {
                setRequired('none', false);
            }
        });
        
        function setRequired(type, required) {
            // Reguler fields
            const regulerInputs = document.querySelectorAll('#fieldsReguler input, #fieldsReguler select');
            // Kimia fields
            const kimiaInputs = document.querySelectorAll('#fieldsKimia input');
            // Pecah fields
            const pecahInputs = document.querySelectorAll('#fieldsPecah input');
            
            if(type === 'reguler') {
                regulerInputs.forEach(input => input.required = required);
                kimiaInputs.forEach(input => input.required = false);
                pecahInputs.forEach(input => input.required = false);
            } else if(type === 'kimia') {
                regulerInputs.forEach(input => input.required = false);
                kimiaInputs.forEach(input => input.required = required);
                pecahInputs.forEach(input => input.required = false);
            } else if(type === 'pecah') {
                regulerInputs.forEach(input => input.required = false);
                kimiaInputs.forEach(input => input.required = false);
                pecahInputs.forEach(input => input.required = required);
            } else {
                regulerInputs.forEach(input => input.required = false);
                kimiaInputs.forEach(input => input.required = false);
                pecahInputs.forEach(input => input.required = false);
            }
        }
        
        function hapusKargo(id) {
            if(confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'proses_kargo.php';
                form.innerHTML = `
                    <input type="hidden" name="action" value="hapus">
                    <input type="hidden" name="id_kargo" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function editKargo(id) {
            // Fetch data via AJAX (simplifikasi - ambil dari tabel)
            const row = event.target.closest('tr');
            const cells = row.cells;
            
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_pengirim').value = cells[1].innerText;
            document.getElementById('edit_kota').value = cells[2].innerText;
            document.getElementById('edit_berat').value = parseFloat(cells[3].innerText);
            document.getElementById('edit_tarif').value = 10000; // Default, sebaiknya ambil dari DB
            
            document.getElementById('modalEdit').style.display = 'block';
        }
        
        function tutupModal() {
            document.getElementById('modalEdit').style.display = 'none';
        }
        
        // Inisialisasi
        setRequired('none', false);
    </script>
</body>
</html>