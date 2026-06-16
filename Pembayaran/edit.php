<?php
require_once __DIR__ . '/../Config/koneksi.php';

$database = new Database();
$koneksi = $database->getConnection();

$id = $_GET['id'] ?? 0;
$message = '';
$error = '';

// Ambil data lama
$query = "SELECT * FROM pembayaran WHERE id_pembayaran = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_transaksi = $_POST['id_transaksi'];
    $total_tagihan = $_POST['total_tagihan'];
    $status_lunas = $_POST['status_lunas'];
    $metode_pembayaran = $_POST['metode_pembayaran'];
    $waktu_pembayaran = !empty($_POST['waktu_pembayaran']) ? $_POST['waktu_pembayaran'] : null;
    
    $update = "UPDATE pembayaran SET id_transaksi=?, total_tagihan=?, status_lunas=?, metode_pembayaran=?, waktu_pembayaran=? WHERE id_pembayaran=?";
    $stmt = $koneksi->prepare($update);
    $stmt->bind_param("sdsssi", $id_transaksi, $total_tagihan, $status_lunas, $metode_pembayaran, $waktu_pembayaran, $id);
    
    if ($stmt->execute()) {
        $message = "Data berhasil diupdate!";
        header("refresh:2;url=index.php");
    } else {
        $error = "Gagal update: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Pembayaran</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; padding: 30px; }
        .container { max-width: 600px; margin: 0 auto; }
        .card { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        h2 { margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; }
        input, select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; }
        button { background: #ffc107; color: #333; padding: 12px 25px; border: none; border-radius: 8px; cursor: pointer; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #667eea; text-decoration: none; }
        .message { background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="container">
    <a href="index.php" class="back-link">← Kembali</a>
    <div class="card">
        <h2>✏️ Edit Pembayaran</h2>
        
        <?php if ($message): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>ID Transaksi</label>
                <input type="text" name="id_transaksi" value="<?= htmlspecialchars($data['id_transaksi']) ?>" required>
            </div>
            <div class="form-group">
                <label>Total Tagihan (Rp)</label>
                <input type="number" name="total_tagihan" value="<?= $data['total_tagihan'] ?>" required>
            </div>
            <div class="form-group">
                <label>Status Lunas</label>
                <select name="status_lunas">
                    <option value="Belum Lunas" <?= $data['status_lunas'] == 'Belum Lunas' ? 'selected' : '' ?>>Belum Lunas</option>
                    <option value="Lunas" <?= $data['status_lunas'] == 'Lunas' ? 'selected' : '' ?>>Lunas</option>
                </select>
            </div>
            <div class="form-group">
                <label>Metode Pembayaran</label>
                <input type="text" name="metode_pembayaran" value="<?= htmlspecialchars($data['metode_pembayaran'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Waktu Pembayaran</label>
                <input type="datetime-local" name="waktu_pembayaran" value="<?= $data['waktu_pembayaran'] ? date('Y-m-d\TH:i', strtotime($data['waktu_pembayaran'])) : '' ?>">
            </div>
            <button type="submit">💾 Update Data</button>
        </form>
    </div>
</div>
</body>
</html>