<?php
/**
 * Subclass: AdminGudang
 * Turunan dari StaffLogistik untuk mengelola data admin gudang
 * Menerapkan: Inheritance, Enkapsulasi, Polimorfisme (Override Abstract Methods)
 */

require_once 'StaffLogistik.php';

class AdminGudang extends StaffLogistik {
    
    // ===== ENKAPSULASI: Private Attributes spesifik AdminGudang =====
    private $shiftKerja;  // Pagi/Siang/Malam
    private $zonaGudang;   // Zona gudang yang ditangani
    private $jumlahBarangDiproses;
    private $jumlahErrorAdministrasi;
    
    // ===== CONSTRUCTOR =====
    public function __construct($conn) {
        parent::__construct($conn);
        $this->jumlahBarangDiproses = 0;
        $this->jumlahErrorAdministrasi = 0;
    }
    
    // ===== GETTER & SETTER untuk Atribut Spesifik =====
    
    public function setShiftKerja($shiftKerja) {
        $this->shiftKerja = $shiftKerja;
    }
    
    public function getShiftKerja() {
        return $this->shiftKerja;
    }
    
    public function setZonaGudang($zonaGudang) {
        $this->zonaGudang = $zonaGudang;
    }
    
    public function getZonaGudang() {
        return $this->zonaGudang;
    }
    
    public function setJumlahBarangDiproses($jumlah) {
        $this->jumlahBarangDiproses = $jumlah;
    }
    
    public function getJumlahBarangDiproses() {
        return $this->jumlahBarangDiproses;
    }
    
    public function setJumlahErrorAdministrasi($jumlah) {
        $this->jumlahErrorAdministrasi = $jumlah;
    }
    
    public function getJumlahErrorAdministrasi() {
        return $this->jumlahErrorAdministrasi;
    }
    
    // ===== IMPLEMENTASI ABSTRACT METHODS (POLIMORFISME) =====
    
    /**
     * POLIMORFISME 1: hitungTakeHomePay() - Khusus AdminGudang
     * 
     * Perhitungan: Gaji Pokok + Tunjangan Shift + Bonus Produktivitas Barang
     * - Shift Pagi/Siang: 5% dari gaji pokok
     * - Shift Malam: 10% dari gaji pokok (tunjangan malam)
     * - Bonus: Rp 500 per barang yang diproses (dengan penalti error)
     * 
     * @return decimal Total gaji bawa pulang admin gudang
     */
    public function hitungTakeHomePay() {
        $gajiPokok = $this->getGajiPokok();
        
        // Tunjangan shift
        $tunjanganShift = 0;
        if ($this->shiftKerja == 'Malam') {
            $tunjanganShift = $gajiPokok * 0.10; // 10% untuk shift malam
        } else {
            $tunjanganShift = $gajiPokok * 0.05; // 5% untuk shift pagi/siang
        }
        
        // Bonus produktivitas (Rp 500 per barang, dikurangi penalti error)
        $bonusProduktivitas = ($this->jumlahBarangDiproses * 500) - ($this->jumlahErrorAdministrasi * 2000);
        
        // Pastikan bonus tidak negatif
        if ($bonusProduktivitas < 0) {
            $bonusProduktivitas = 0;
        }
        
        // Total Take Home Pay
        $totalTakeHome = $gajiPokok + $tunjanganShift + $bonusProduktivitas;
        
        return $totalTakeHome;
    }
    
    /**
     * POLIMORFISME 2: evaluasiSOPKerja() - Khusus AdminGudang
     * 
     * Kriteria evaluasi untuk admin gudang:
     * - Keakuratan Data Entry (error rate)
     * - Kecepatan Pemrosesan Barang
     * - Kehadiran & Ketaatan Shift
     * - Penguasaan Sistem Informasi
     * 
     * @return array Hasil evaluasi dengan skor dan status
     */
    public function evaluasiSOPKerja() {
        $evaluasi = [
            'nama_staff' => $this->getNamaLengkap(),
            'jenis_staff' => 'AdminGudang',
            'skor_total' => 0,
            'detail' => []
        ];
        
        // Kriteria 1: Keakuratan Data Entry
        // Error rate < 2% = 30 poin
        $errorRate = $this->jumlahBarangDiproses > 0 
            ? ($this->jumlahErrorAdministrasi / $this->jumlahBarangDiproses) * 100 
            : 0;
        
        $skor1 = 0;
        if ($errorRate < 2) {
            $skor1 = 30;
            $status1 = 'SANGAT BAIK';
        } elseif ($errorRate < 5) {
            $skor1 = 20;
            $status1 = 'BAIK';
        } elseif ($errorRate < 10) {
            $skor1 = 10;
            $status1 = 'CUKUP';
        } else {
            $skor1 = 0;
            $status1 = 'PERLU PERBAIKAN';
        }
        
        $kriteria1 = [
            'nama_kriteria' => 'Keakuratan Data Entry',
            'skor' => $skor1,
            'status' => $status1,
            'detail' => "Error Rate: {$errorRate}%"
        ];
        $evaluasi['detail'][] = $kriteria1;
        
        // Kriteria 2: Kecepatan Pemrosesan
        // Asumsi target 50 barang/hari
        $kecepatanPersen = $this->jumlahBarangDiproses > 0 ? ($this->jumlahBarangDiproses / 50) * 100 : 0;
        
        $skor2 = 0;
        if ($kecepatanPersen >= 100) {
            $skor2 = 25;
            $status2 = 'MENCAPAI TARGET';
        } elseif ($kecepatanPersen >= 80) {
            $skor2 = 20;
            $status2 = 'HAMPIR MENCAPAI';
        } else {
            $skor2 = 15;
            $status2 = 'DIBAWAH TARGET';
        }
        
        $kriteria2 = [
            'nama_kriteria' => 'Kecepatan Pemrosesan',
            'skor' => $skor2,
            'status' => $status2,
            'detail' => "{$kecepatanPersen}% dari target"
        ];
        $evaluasi['detail'][] = $kriteria2;
        
        // Kriteria 3: Kehadiran & Ketaatan Shift
        $kriteria3 = [
            'nama_kriteria' => 'Kehadiran & Ketaatan Shift',
            'skor' => 25,
            'status' => 'TERPENUHI',
            'detail' => "Shift: {$this->shiftKerja}"
        ];
        $evaluasi['detail'][] = $kriteria3;
        
        // Kriteria 4: Penguasaan Sistem Informasi
        $kriteria4 = [
            'nama_kriteria' => 'Penguasaan Sistem Informasi',
            'skor' => 20,
            'status' => 'KOMPETEN',
            'detail' => 'Zona: ' . $this->zonaGudang
        ];
        $evaluasi['detail'][] = $kriteria4;
        
        // Hitung total skor
        foreach ($evaluasi['detail'] as $detail) {
            $evaluasi['skor_total'] += $detail['skor'];
        }
        
        // Tentukan status keseluruhan
        if ($evaluasi['skor_total'] >= 85) {
            $evaluasi['status_keseluruhan'] = 'LULUS - LAYAK PROMOSI';
        } elseif ($evaluasi['skor_total'] >= 75) {
            $evaluasi['status_keseluruhan'] = 'LULUS - SESUAI STANDAR';
        } elseif ($evaluasi['skor_total'] >= 60) {
            $evaluasi['status_keseluruhan'] = 'LULUS - PERLU PERBAIKAN';
        } else {
            $evaluasi['status_keseluruhan'] = 'TIDAK LULUS - PERLU PELATIHAN ULANG';
        }
        
        return $evaluasi;
    }
    
    /**
     * Override: getJenisStaff
     */
    public function getJenisStaff() {
        return 'AdminGudang';
    }
    
    /**
     * Override: displayInfo - Tambah info spesifik AdminGudang
     */
    public function displayInfo() {
        $baseInfo = parent::displayInfo();
        $baseInfo['shift_kerja'] = $this->shiftKerja;
        $baseInfo['zona_gudang'] = $this->zonaGudang;
        $baseInfo['jumlah_barang_diproses'] = $this->jumlahBarangDiproses;
        return $baseInfo;
    }
    
    /**
     * Simpan data AdminGudang ke database
     */
    public function save() {
    $sql = "INSERT INTO staff (id_staff_code, nama_lengkap, gaji_pokok, jam_kerja, jenis_staff, shift_kerja, zona_gudang) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $this->getConn()->prepare($sql);
    $jenisStaff = $this->getJenisStaff();
    $gajiPokok = $this->getGajiPokok();
    $jamKerja = $this->getJamKerja();
    
    // ✅ Langsung tulis tipe data tanpa fungsi
    $stmt->bind_param("ssdssss", 
        $this->getIdStaffCode(),
        $this->getNamaLengkap(),
        $gajiPokok,
        $jamKerja,
        $jenisStaff,
        $this->shiftKerja,
        $this->zonaGudang
    );
    
    return $stmt->execute();
}
}
?>