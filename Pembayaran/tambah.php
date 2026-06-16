<?php
require_once __DIR__ . '/../Config/koneksi.php';
require_once 'CashOnDelivery.php';
require_once 'TransferBank.php';
require_once 'EWallet.php';

$database = new Database();
$koneksi = $database->getConnection();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_transaksi = $_POST['id_transaksi'];
    $total_tagihan = $_POST['total_tagihan'];
    $metode_pembayaran = $_POST['metode_pembayaran'];
    $waktu_pembayaran = !empty($_POST['waktu_pembayaran']) ? $_POST['waktu_pembayaran'] : null;
    
    // OOP: Buat objek sesuai metode pembayaran
    switch ($metode_pembayaran) {
        case 'CashOnDelivery':
            $pembayaran = new CashOnDelivery($_POST['biaya_penanganan'] ?? 0, $_POST['batas_nominal'] ?? 0);
            $biaya_admin = $pembayaran->hitungBiayaAdmin();
            $validasi = $pembayaran->prosesValidasiBayar();
            break;
        case 'TransferBank':
            $pembayaran = new TransferBank($_POST['kode_va'] ?? '', $_POST['nama_bank'] ?? '');
            $biaya_admin = $pembayaran->hitungBiayaAdmin();
            $validasi = $pembayaran->prosesValidasiBayar();
            break;
        case 'EWallet':
            $pembayaran = new EWallet($_POST['nomor_hp'] ?? '', $_POST['biaya_layanan'] ?? 0);
            $biaya_admin = $pembayaran->hitungBiayaAdmin();
            $validasi = $pembayaran->prosesValidasiBayar();
            break;
        default:
            $biaya_admin = 0;
            $validasi = "Metode tidak dikenal";
    }
    
    $total_akhir = $total_tagihan + $biaya_admin;
    $status_lunas = isset($_POST['status_lunas']) ? $_POST['status_lunas'] : 'Belum Lunas';
    
    $query = "INSERT INTO pembayaran (id_transaksi, total_tagihan, status_lunas, waktu_pembayaran, metode_pembayaran, 
              biaya_penanganan_kurir, batas_maksimal_nominal, kode_virtual_account, nama_bank, nomor_hp, biaya_layanan_aplikasi) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $koneksi->prepare($query);
    $biaya_penanganan = ($metode_pembayaran == 'CashOnDelivery') ? $_POST['biaya_penanganan'] ?? 0 : null;
    $batas_nominal = ($metode_pembayaran == 'CashOnDelivery') ? $_POST['batas_nominal'] ?? 0 : null;
    $kode_va = ($metode_pembayaran == 'TransferBank') ? $_POST['kode_va'] ?? '' : null;
    $nama_bank = ($metode_pembayaran == 'TransferBank') ? $_POST['nama_bank'] ?? '' : null;
    $nomor_hp = ($metode_pembayaran == 'EWallet') ? $_POST['nomor_hp'] ?? '' : null;
    $biaya_layanan = ($metode_pembayaran == 'EWallet') ? $_POST['biaya_layanan'] ?? 0 : null;
    
    $stmt->bind_param("sdsssssssss", $id_transaksi, $total_tagihan, $status_lunas, $waktu_pembayaran, 
                      $metode_pembayaran, $biaya_penanganan, $batas_nominal, $kode_va, $nama_bank, $nomor_hp, $biaya_layanan);
    
    if ($stmt->execute()) {
        $message = "Pembayaran berhasil ditambahkan!<br>Validasi: $validasi<br>Biaya Admin: Rp " . number_format($biaya_admin, 0, ',', '.');
    } else {
        $error = "Gagal menambahkan: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Pembayaran - Ekspedisi Logistik</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; padding: 30px; }
        .container { max-width: 700px; margin: 0 auto; }
        .card { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        h2 { color: #333; margin-bottom: 10px; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #667eea; text-decoration: none; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; color: #333; }
        input, select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; }
        button { background: #667eea; color: white; padding: 12px 25px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; }
        .message { background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .dynamic-field { display: none; margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; }
        hr { margin: 20px 0; }
    </style>
    <script>
        function toggleFields() {
            var metode = document.getElementById('metode').value;
            var fields = document.querySelectorAll('.dynamic-field');
            fields.forEach(function(field) { field.style.display = 'none'; });
            if (metode === 'CashOnDelivery') document.getElementById('cod-fields').style.display = 'block';
            else if (metode === 'TransferBank') document.getElementById('transfer-fields').style.display = 'block';
            else if (metode === 'EWallet') document.getElementById('ewallet-fields').style.display = 'block';
        }
    </script>
</head>
<body>
<div class="container">
    <a href="index.php" class="back-link">← Kembali ke Daftar Pembayaran</a>
    <div class="card">
        <h2>➕ Tambah Pembayaran Baru</h2>
        <p style="color: #666; margin-bottom: 20px;">Input data transaksi pembayaran ekspedisi</p>
        
        <?php if ($message): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>ID Transaksi *</label>
                <input type="text" name="id_transaksi" required placeholder="Contoh: TRX001">
            </div>
            <div class="form-group">
                <label>Total Tagihan (Rp) *</label>
                <input type="number" name="total_tagihan" required placeholder="0">
            </div>
            <div class="form-group">
                <label>Metode Pembayaran *</label>
                <select name="metode_pembayaran" id="metode" onchange="toggleFields()" required>
                    <option value="">-- Pilih Metode --</option>
                    <option value="CashOnDelivery">Cash On Delivery (COD)</option>
                    <option value="TransferBank">Transfer Bank</option>
                    <option value="EWallet">E-Wallet</option>
                </select>
            </div>
            
            <!-- COD Fields -->
            <div id="cod-fields" class="dynamic-field">
                <h4>💵 Cash On Delivery</h4>
                <div class="form-group">
                    <label>Biaya Penanganan Kurir</label>
                    <input type="number" name="biaya_penanganan" placeholder="Rp">
                </div>
                <div class="form-group">
                    <label>Batas Maksimal Nominal</label>
                    <input type="number" name="batas_nominal" placeholder="Rp">
                </div>
            </div>
            
            <!-- Transfer Bank Fields -->
            <div id="transfer-fields" class="dynamic-field">
                <h4>🏦 Transfer Bank</h4>
                <div class="form-group">
                    <label>Kode Virtual Account</label>
                    <input type="text" name="kode_va" placeholder="Contoh: 881231234567">
                </div>
                <div class="form-group">
                    <label>Nama Bank</label>
                    <input type="text" name="nama_bank" placeholder="BCA / Mandiri / BRI">
                </div>
            </div>
            
            <!-- E-Wallet Fields -->
            <div id="ewallet-fields" class="dynamic-field">
                <h4>📱 E-Wallet</h4>
                <div class="form-group">
                    <label>Nomor HP</label>
                    <input type="text" name="nomor_hp" placeholder="08123456789">
                </div>
                <div class="form-group">
                    <label>Biaya Layanan Aplikasi</label>
                    <input type="number" name="biaya_layanan" placeholder="Rp">
                </div>
            </div>
            
            <div class="form-group">
                <label>Status Lunas</label>
                <select name="status_lunas">
                    <option value="Belum Lunas">Belum Lunas</option>
                    <option value="Lunas">Lunas</option>
                </select>
            </div>
            <div class="form-group">
                <label>Waktu Pembayaran (opsional)</label>
                <input type="datetime-local" name="waktu_pembayaran">
            </div>
            
            <button type="submit">💾 Simpan Pembayaran</button>
        </form>
        
        <hr>
        <small style="color: #888;">✨ Sistem menggunakan OOP: Abstract Class SistemPembayaran dengan inheritance COD, TransferBank, EWallet</small>
    </div>
</div>
</body>
</html>