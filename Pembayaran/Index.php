<?php
require_once 'SistemPembayaran.php';
require_once 'CashOnDelivery.php';
require_once 'TransferBank.php';
require_once 'EWallet.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pembayaran - Cargo Ekspedisi</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            padding: 30px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 25px 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }
        .header p {
            opacity: 0.85;
            font-size: 14px;
        }
        
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
        }
        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border-top: 4px solid #2a5298;
        }
        .card h2 {
            font-size: 18px;
            color: #1e3c72;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        .card .info {
            margin: 8px 0;
            font-size: 14px;
            color: #333;
        }
        .card .info strong {
            color: #1e3c72;
            display: inline-block;
            width: 140px;
        }
        .card .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            margin-top: 5px;
        }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-warning { background: #fff3cd; color: #856404; }
        
        .total-box {
            background: #1e3c72;
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            margin-top: 15px;
            text-align: center;
            font-size: 18px;
        }
        .total-box span {
            font-weight: bold;
            font-size: 22px;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Header -->
    <div class="header">
        <h1> Sistem Pembayaran - Cargo Ekspedisi</h1>
        <p>Kelola transaksi pembayaran dengan berbagai metode</p>
    </div>

    <!-- Cards -->
    <div class="cards">
        
        <!-- 1. Cash On Delivery -->
        <?php
        $cod = new CashOnDelivery('TRX-COD-001', 450000, 15000, 500000);
        $info = $cod->getInfoPembayaran();
        ?>
        <div class="card">
            <h2>Cash On Delivery</h2>
            <div class="info"><strong>ID Transaksi:</strong> <?= $info['id_transaksi'] ?></div>
            <div class="info"><strong>Total Tagihan:</strong> Rp <?= number_format($info['total_tagihan'], 0, ',', '.') ?></div>
            <div class="info"><strong>Biaya Admin:</strong> Rp <?= number_format($info['biaya_admin'], 0, ',', '.') ?></div>
            <div class="info"><strong>Total Dibayar:</strong> Rp <?= number_format($info['total_dibayar'], 0, ',', '.') ?></div>
            <div class="info"><strong>Status:</strong> 
                <span class="badge badge-warning"><?= $info['status'] ?></span>
            </div>
            <div class="info"><strong>Validasi:</strong> <?= $info['validasi'] ?></div>
            <div class="info" style="font-size:12px; color:#888; margin-top:5px;">
                <strong>Batas Maks:</strong> Rp <?= number_format($cod->getBatasMaksimalNominal(), 0, ',', '.') ?>
                | <strong>Biaya Kurir:</strong> Rp <?= number_format($cod->getBiayaPenangananKurir(), 0, ',', '.') ?>
            </div>
        </div>

        <!-- 2. Transfer Bank -->
        <?php
        $transfer = new TransferBank('TRX-BANK-001', 750000, '881231234567', 'BCA');
        $info = $transfer->getInfoPembayaran();
        ?>
        <div class="card">
            <h2> Transfer Bank</h2>
            <div class="info"><strong>ID Transaksi:</strong> <?= $info['id_transaksi'] ?></div>
            <div class="info"><strong>Total Tagihan:</strong> Rp <?= number_format($info['total_tagihan'], 0, ',', '.') ?></div>
            <div class="info"><strong>Biaya Admin:</strong> Rp <?= number_format($info['biaya_admin'], 0, ',', '.') ?></div>
            <div class="info"><strong>Total Dibayar:</strong> Rp <?= number_format($info['total_dibayar'], 0, ',', '.') ?></div>
            <div class="info"><strong>Status:</strong> 
                <span class="badge badge-warning"><?= $info['status'] ?></span>
            </div>
            <div class="info"><strong>Validasi:</strong> <?= $info['validasi'] ?></div>
            <div class="info" style="font-size:12px; color:#888; margin-top:5px;">
                <strong>Bank:</strong> <?= $transfer->getNamaBank() ?>
                | <strong>VA:</strong> <?= $transfer->getKodeVirtualAccount() ?>
            </div>
        </div>

        <!-- 3. E-Wallet -->
        <?php
        $ewallet = new EWallet('TRX-EWALLET-001', 200000, '08123456789', 3500);
        $info = $ewallet->getInfoPembayaran();
        ?>
        <div class="card">
            <h2> E-Wallet</h2>
            <div class="info"><strong>ID Transaksi:</strong> <?= $info['id_transaksi'] ?></div>
            <div class="info"><strong>Total Tagihan:</strong> Rp <?= number_format($info['total_tagihan'], 0, ',', '.') ?></div>
            <div class="info"><strong>Biaya Admin:</strong> Rp <?= number_format($info['biaya_admin'], 0, ',', '.') ?></div>
            <div class="info"><strong>Total Dibayar:</strong> Rp <?= number_format($info['total_dibayar'], 0, ',', '.') ?></div>
            <div class="info"><strong>Status:</strong> 
                <span class="badge badge-warning"><?= $info['status'] ?></span>
            </div>
            <div class="info"><strong>Validasi:</strong> <?= $info['validasi'] ?></div>
            <div class="info" style="font-size:12px; color:#888; margin-top:5px;">
                <strong>Nomor HP:</strong> <?= $ewallet->getNomorHP() ?>
                | <strong>Biaya Layanan:</strong> Rp <?= number_format($ewallet->getBiayaLayananAplikasi(), 0, ',', '.') ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>