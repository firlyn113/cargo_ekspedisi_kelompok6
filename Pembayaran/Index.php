<?php
require_once '../Config/koneksi.php';
require_once 'CashOnDelivery.php';
require_once 'TransferBank.php';
require_once 'EWallet.php';

$database = new Database();
$conn = $database->getConnection();

// Contoh 1: CashOnDelivery
$cod = new CashOnDelivery(15000, 500000);
$cod->setIdTransaksi("TRX001");
$cod->setTotalTagihan(450000);
echo "<h3>Cash On Delivery</h3>";
echo "Validasi: " . $cod->prosesValidasiBayar() . "<br>";
echo "Biaya Admin (Penanganan Kurir): Rp " . number_format($cod->hitungBiayaAdmin(), 0, ',', '.') . "<br><hr>";

// Contoh 2: TransferBank
$transfer = new TransferBank("881231234567", "BCA");
$transfer->setIdTransaksi("TRX002");
$transfer->setTotalTagihan(750000);
echo "<h3>Transfer Bank</h3>";
echo "Validasi: " . $transfer->prosesValidasiBayar() . "<br>";
echo "Biaya Admin: Rp " . number_format($transfer->hitungBiayaAdmin(), 0, ',', '.') . "<br><hr>";

// Contoh 3: EWallet
$ewallet = new EWallet("08123456789", 3500);
$ewallet->setIdTransaksi("TRX003");
$ewallet->setTotalTagihan(200000);
echo "<h3>E-Wallet</h3>";
echo "Validasi: " . $ewallet->prosesValidasiBayar() . "<br>";
echo "Biaya Layanan Aplikasi: Rp " . number_format($ewallet->hitungBiayaAdmin(), 0, ',', '.') . "<br><hr>";
?>