<?php
/**
 * Subclass: KurirMotor
 * Turunan dari StaffLogistik untuk mengelola data kurir motor
 * Menerapkan: Inheritance, Enkapsulasi, Polimorfisme (Override Abstract Methods)
 */

require_once 'StaffLogistik.php';

class KurirMotor extends StaffLogistik {
    
    // ===== ENKAPSULASI: Private Attributes spesifik KurirMotor =====
    private $platNomorMotor;
    private $areaCakupan;  // Wilayah operasional
    private $jumlahPaketAntar;
    private $jumlahPaketTerima;
    private $persentaseAccuracy;
    
    // ===== CONSTRUCTOR =====
    public function __construct($conn) {
        parent::__construct($conn);
        $this->jumlahPaketAntar = 0;
        $this->jumlahPaketTerima = 0;
        $this->persentaseAccuracy = 100;
    }
    
    // ===== GETTER & SETTER untuk Atribut Spesifik =====
    
    public function setPlatNomorMotor($platNomorMotor) {
        $this->platNomorMotor = $platNomorMotor;
    }
    
    public function getPlatNomorMotor() {
        return $this->platNomorMotor;
    }
    
    public function setAreaCakupan($areaCakupan) {
        $this->areaCakupan = $areaCakupan;
    }
    
    public function getAreaCakupan() {
        return $this->areaCakupan;
    }
    
    public function setJumlahPaketAntar($jumlah) {
        $this->jumlahPaketAntar = $jumlah;
    }
    
    public function getJumlahPaketAntar() {
        return $this->jumlahPaketAntar;
    }
    
    public function setJumlahPaketTerima($jumlah) {
        $this->jumlahPaketTerima = $jumlah;
    }
    
    public function getJumlahPaketTerima() {
        return $this->jumlahPaketTerima;
    }
    
    public function setPersentaseAccuracy($persen) {
        $this->persentaseAccuracy = $persen;
    }
    
    public function getPersentaseAccuracy() {
        return $this->persentaseAccuracy;
    }
    
    // ===== IMPLEMENTASI ABSTRACT METHODS (POLIMORFISME) =====
    
    /**
     * POLIMORFISME 1: hitungTakeHomePay() - Khusus KurirMotor
     * 
     * Perhitungan: Gaji Pokok + Insentif Per Paket + Bonus Accuracy
     * - Gaji base (fixed)
     * - Insentif: Rp 2,000 per paket antar sukses
     * - Bonus Accuracy: Jika accuracy >= 95% maka bonus 5% gaji pokok
     * 
     * @return decimal Total gaji bawa pulang kurir motor
     */
    public function hitungTakeHomePay() {
        $gajiPokok = $this->getGajiPokok();
        
        // Insentif per paket antar (Rp 2,000)
        $insentifPerPaket = $this->jumlahPaketAntar * 2000;
        
        // Bonus accuracy (jika >= 95%)
        $bonusAccuracy = 0;
        if ($this->persentaseAccuracy >= 95) {
            $bonusAccuracy = $gajiPokok * 0.05; // 5% bonus
        } elseif ($this->persentaseAccuracy >= 85) {
            $bonusAccuracy = $gajiPokok * 0.02; // 2% bonus
        }
        
        // Total Take Home Pay
        $totalTakeHome = $gajiPokok + $insentifPerPaket + $bonusAccuracy;
        
        return $totalTakeHome;
    }
    
    /**
     * POLIMORFISME 2: evaluasiSOPKerja() - Khusus KurirMotor
     * 
     * Kriteria evaluasi untuk kurir motor:
     * - Akurasi Pengiriman (tepat lokasi, tepat orang)
     * - Ketepatan Waktu Pengiriman
     * - Penampilan & Presentasi Diri
     * - Kepuasan Pelanggan & Feedback Positif
     * 
     * @return array Hasil evaluasi dengan skor dan status
     */
    public function evaluasiSOPKerja() {
        $evaluasi = [
            'nama_staff' => $this->getNamaLengkap(),
            'jenis_staff' => 'KurirMotor',
            'skor_total' => 0,
            'detail' => []
        ];
        
        // Kriteria 1: Akurasi Pengiriman
        $akurasi = $this->persentaseAccuracy;
        
        $skor1 = 0;
        if ($akurasi >= 99) {
            $skor1 = 30;
            $status1 = 'SEMPURNA';
        } elseif ($akurasi >= 95) {
            $skor1 = 25;
            $status1 = 'SANGAT BAIK';
        } elseif ($akurasi >= 90) {
            $skor1 = 20;
            $status1 = 'BAIK';
        } elseif ($akurasi >= 85) {
            $skor1 = 15;
            $status1 = 'CUKUP';
        } else {
            $skor1 = 0;
            $status1 = 'PERLU PERBAIKAN';
        }
        
        $kriteria1 = [
            'nama_kriteria' => 'Akurasi Pengiriman',
            'skor' => $skor1,
            'status' => $status1,
            'detail' => "Akurasi: {$akurasi}%"
        ];
        $evaluasi['detail'][] = $kriteria1;
        
        // Kriteria 2: Ketepatan Waktu Pengiriman
        // Target: Semua paket selesai dalam SLA (asumsi tercapai)
        $kriteria2 = [
            'nama_kriteria' => 'Ketepatan Waktu Pengiriman',
            'skor' => 25,
            'status' => 'SESUAI SLA',
            'detail' => 'Semua pengiriman tepat waktu'
        ];
        $evaluasi['detail'][] = $kriteria2;
        
        // Kriteria 3: Penampilan & Presentasi Diri
        $kriteria3 = [
            'nama_kriteria' => 'Penampilan & Presentasi Diri',
            'skor' => 20,
            'status' => 'PROFESIONAL',
            'detail' => "Motor: {$this->platNomorMotor}"
        ];
        $evaluasi['detail'][] = $kriteria3;
        
        // Kriteria 4: Kepuasan Pelanggan
        // Rating berdasarkan jumlah paket terima vs antar
        $successRate = $this->jumlahPaketAntar > 0 
            ? ($this->jumlahPaketTerima / $this->jumlahPaketAntar) * 100 
            : 0;
        
        $skor4 = 0;
        if ($successRate >= 98) {
            $skor4 = 25;
            $status4 = 'TERPUASKAN';
        } elseif ($successRate >= 95) {
            $skor4 = 20;
            $status4 = 'MEMUASKAN';
        } else {
            $skor4 = 15;
            $status4 = 'CUKUP MEMUASKAN';
        }
        
        $kriteria4 = [
            'nama_kriteria' => 'Kepuasan Pelanggan',
            'skor' => $skor4,
            'status' => $status4,
            'detail' => "Success Rate: {$successRate}%"
        ];
        $evaluasi['detail'][] = $kriteria4;
        
        // Hitung total skor
        foreach ($evaluasi['detail'] as $detail) {
            $evaluasi['skor_total'] += $detail['skor'];
        }
        
        // Tentukan status keseluruhan
        if ($evaluasi['skor_total'] >= 95) {
            $evaluasi['status_keseluruhan'] = 'LULUS - KARYAWAN BINTANG';
        } elseif ($evaluasi['skor_total'] >= 85) {
            $evaluasi['status_keseluruhan'] = 'LULUS - SANGAT MEMUASKAN';
        } elseif ($evaluasi['skor_total'] >= 75) {
            $evaluasi['status_keseluruhan'] = 'LULUS - MEMUASKAN';
        } else {
            $evaluasi['status_keseluruhan'] = 'TIDAK LULUS - BUTUH IMPROVEMENT';
        }
        
        return $evaluasi;
    }
    
    /**
     * Override: getJenisStaff
     */
    public function getJenisStaff() {
        return 'KurirMotor';
    }
    
    /**
     * Override: displayInfo - Tambah info spesifik KurirMotor
     */
    public function displayInfo() {
        $baseInfo = parent::displayInfo();
        $baseInfo['plat_nomor_motor'] = $this->platNomorMotor;
        $baseInfo['area_cakupan'] = $this->areaCakupan;
        $baseInfo['jumlah_paket_antar'] = $this->jumlahPaketAntar;
        $baseInfo['jumlah_paket_terima'] = $this->jumlahPaketTerima;
        return $baseInfo;
    }
    
    /**
     * Simpan data KurirMotor ke database
     */
    public function save() {
        $sql = "INSERT INTO staff (id_staff_code, nama_lengkap, gaji_pokok, jam_kerja, jenis_staff, plat_nomor_motor, area_cakupan) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->getConn()->prepare($sql);
        $jenisStaff = $this->getJenisStaff();
        $gajiPokok = $this->getGajiPokok();
        $jamKerja = $this->getJamKerja();
        
        $stmt->bind_param("ssdsss", 
            $this->getIdStaffCode(),
            $this->getNamaLengkap(),
            $gajiPokok,
            $jamKerja,
            $jenisStaff,
            $this->platNomorMotor,
            $this->areaCakupan
        );
        
        return $stmt->execute();
    }
}
?>