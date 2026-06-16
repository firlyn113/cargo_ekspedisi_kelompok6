<?php
// Armada/index.php - FINAL (TAMPILAN LOGIS)

// ============================================
// ABSTRACT CLASS ARMADA
// ============================================
abstract class Armada {
    private $id_armada_code;
    private $kapasitas_maksimal_kg;
    private $status_kelaikan;
    private $biaya_operasional_dasar;
    private $jenis_armada;
    
    public function __construct($id_armada_code, $kapasitas_maksimal_kg, $status_kelaikan, $biaya_operasional_dasar) {
        $this->id_armada_code = $id_armada_code;
        $this->kapasitas_maksimal_kg = $kapasitas_maksimal_kg;
        $this->status_kelaikan = $status_kelaikan;
        $this->biaya_operasional_dasar = $biaya_operasional_dasar;
    }
    
    public function getIdArmadaCode() {
        return $this->id_armada_code;
    }
    
    public function getKapasitasMaksimal() {
        return $this->kapasitas_maksimal_kg;
    }
    
    public function getStatusKelaikan() {
        return $this->status_kelaikan;
    }
    
    public function getBiayaOperasionalDasar() {
        return $this->biaya_operasional_dasar;
    }
    
    public function getJenisArmada() {
        return $this->jenis_armada;
    }
    
    public function setJenisArmada($jenis) {
        $this->jenis_armada = $jenis;
    }
    
    public abstract function hitungBiayaOperasional();
    public abstract function cekKelayakan();
    public abstract function getDetailSpesifik();
}

// ============================================
// REQUIRE SUBCLASS
// ============================================
require_once 'TrukDarat.php';
require_once 'KapalLaut.php';
require_once 'PesawatKargo.php';

// ============================================
// BUAT OBJEK DEMO
// ============================================

// TRUK DARAT
$truk1 = new TrukDarat('TRK-001', 5000, 'Laik', 750000, 6, 'Tol Dalam Kota, Tol Lingkar Luar');
$truk2 = new TrukDarat('TRK-002', 8000, 'Tidak Laik', 1000000, 8, '');

// KAPAL LAUT
$kapal1 = new KapalLaut('KPL-001', 15000, 'Laik', 2000000, 'Tanjung Priok', 'Refrigerated');
$kapal2 = new KapalLaut('KPL-002', 10000, 'Laik', 1500000, 'Surabaya', 'Standard');

// PESAWAT KARGO
$pesawat1 = new PesawatKargo('PSW-001', 20000, 'Laik', 5000000, 12000, 'Cargo Malam');
$pesawat2 = new PesawatKargo('PSW-002', 15000, 'Tidak Laik', 4000000, 10000, 'Internasional');

$armada_list = [$truk1, $truk2, $kapal1, $kapal2, $pesawat1, $pesawat2];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Armada - Cargo Ekspedisi</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
            padding: 30px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* ===== HEADER - WARNA #1e3c72 ===== */
        .header {
            background: #1e3c72;
            padding: 25px 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .header h1 {
            font-size: 28px;
            color: #ffffff;
            font-weight: 700;
        }

        .header p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 14px;
            margin-top: 4px;
        }

        /* ===== CARD GRID ===== */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
        }

        /* ===== CARD ===== */
        .card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            border-left: 5px solid #1e3c72;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .card-header {
            padding: 16px 22px;
            background: #f8f9fa;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header .jenis {
            font-size: 16px;
            font-weight: 700;
            color: #2c3e50;
        }

        .card-header .kode {
            background: #e9ecef;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: #555;
        }

        .card-body {
            padding: 20px 22px;
        }

        /* ===== INFO ROW ===== */
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 14px;
            border-bottom: 1px solid #f8f9fa;
        }

        .info-row .label {
            color: #888;
        }

        .info-row .value {
            font-weight: 600;
            color: #2c3e50;
        }

        .info-row .value.laik {
            color: #27ae60;
        }

        .info-row .value.tidak-laik {
            color: #e74c3c;
        }

        /* ===== DIVIDER ===== */
        .divider {
            border-top: 2px dashed #eee;
            margin: 14px 0;
        }

        /* ===== POLYMORPHISM SECTION ===== */
        .poly-title {
            font-size: 12px;
            font-weight: 700;
            color: #1e3c72;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }

        .poly-item {
            background: #f8f9fa;
            padding: 8px 14px;
            border-radius: 6px;
            margin-bottom: 6px;
            font-size: 13px;
            color: #333;
        }

        .poly-item .highlight {
            font-weight: 700;
            color: #2c3e50;
        }

        /* ===== FOOTER ===== */
        .footer-info {
            margin-top: 30px;
            text-align: center;
            color: #999;
            font-size: 13px;
            background: white;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .footer-info strong {
            color: #2c3e50;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            body { padding: 15px; }
            .grid { grid-template-columns: 1fr; }
            .header h1 { font-size: 22px; }
            .header { padding: 20px; }
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- HEADER - SUBTITLE SUDAH LOGIS -->
        <div class="header">
            <h1>🚛 ARMADA - CARGO EKSPEDISI</h1>
            <p>Kelola data armada - Truk Darat | Kapal Laut | Pesawat Kargo</p>
        </div>

        <!-- GRID CARDS -->
        <div class="grid">
            <?php foreach ($armada_list as $armada): ?>
                <?php
                $detail = $armada->getDetailSpesifik();
                $biaya_total = $armada->hitungBiayaOperasional();
                $kelayakan = $armada->cekKelayakan();
                $status_class = $armada->getStatusKelaikan() == 'Laik' ? 'laik' : 'tidak-laik';
                ?>
                <div class="card">
                    <!-- HEADER -->
                    <div class="card-header">
                        <span class="jenis"><?= $detail['label'] ?></span>
                        <span class="kode"><?= $armada->getIdArmadaCode() ?></span>
                    </div>

                    <!-- BODY -->
                    <div class="card-body">
                        <!-- Info Dasar -->
                        <div class="info-row">
                            <span class="label">Jenis Armada</span>
                            <span class="value"><?= $armada->getJenisArmada() ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Kapasitas Maksimal</span>
                            <span class="value"><?= number_format($armada->getKapasitasMaksimal(), 0, ',', '.') ?> kg</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Status Kelaikan</span>
                            <span class="value <?= $status_class ?>"><?= $armada->getStatusKelaikan() ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Biaya Operasional Dasar</span>
                            <span class="value">Rp <?= number_format($armada->getBiayaOperasionalDasar(), 0, ',', '.') ?></span>
                        </div>

                        <!-- Detail Spesifik -->
                        <div class="info-row">
                            <span class="label"><?= explode(':', $detail['detail1'])[0] ?></span>
                            <span class="value"><?= explode(':', $detail['detail1'])[1] ?? $detail['detail1'] ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label"><?= explode(':', $detail['detail2'])[0] ?></span>
                            <span class="value"><?= explode(':', $detail['detail2'])[1] ?? $detail['detail2'] ?></span>
                        </div>

                        <div class="divider"></div>

                        <!-- POLYMORPHISM 1: hitungBiayaOperasional() -->
                        <div class="poly-title">💰 Hitung Biaya Operasional</div>
                        <div class="poly-item">
                            <span>Biaya Operasional Total: <span class="highlight">Rp <?= number_format($biaya_total, 0, ',', '.') ?></span></span>
                        </div>

                        <div style="margin-top: 10px;"></div>

                        <!-- POLYMORPHISM 2: cekKelayakan() -->
                        <div class="poly-title">🔍 Hasil Cek Kelayakan</div>
                        <?php foreach ($kelayakan as $cek): ?>
                            <div class="poly-item">
                                <span><?= $cek ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- FOOTER -->
        <div class="footer-info">
            <p>
                <strong>🧪 Demo OOP Armada</strong> &middot;
                Abstract Class: <strong>Armada</strong> &middot;
                Subclass: <strong>TrukDarat</strong>, <strong>KapalLaut</strong>, <strong>PesawatKargo</strong> &middot;
                Polymorphism: <strong>hitungBiayaOperasional()</strong>, <strong>cekKelayakan()</strong>, <strong>getDetailSpesifik()</strong>
            </p>
        </div>
    </div>

</body>
</html>