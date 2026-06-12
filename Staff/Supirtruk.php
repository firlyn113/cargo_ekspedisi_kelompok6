<?php
/**
 * Subclass: SupirTruk
 * Turunan dari StaffLogistik untuk mengelola data sopir truk
 * Menerapkan: Inheritance, Enkapsulasi, Polimorfisme (Override Abstract Methods)
 */

require_once 'StaffLogistik.php';

class SupirTruk extends StaffLogistik {
    
    // ===== ENKAPSULASI: Private Attributes spesifik SupirTruk =====
    private $nomorSIM_B;
    private $uangMakanJalan;
    private $ruteOperasional;
    private $jumlahJamLembur;
    
    // ===== CONSTRUCTOR =====
    public function __construct($conn) {
        parent::__construct($conn);
        $this->jumlahJamLembur = 0;
    }
    
    // ===== GETTER & SETTER untuk Atribut Spesifik =====
    
    public function setNomorSIM_B($nomorSIM_B) {
        $this->nomorSIM_B = $nomorSIM_B;
    }
    
    public function getNomorSIM_B() {
        return $this->nomorSIM_B;
    }
    
    public function setUangMakanJalan($uangMakanJalan) {
        $this->uangMakanJalan = $uangMakanJalan;
    }
    
    public function getUangMakanJalan() {
        return $this->uangMakanJalan;
    }
    
    public function setRuteOperasional($ruteOperasional) {
        $this->ruteOperasional = $ruteOperasional;
    }
    
    public function getRuteOperasional() {
        return $this->ruteOperasional;
    }
    
    public function setJumlahJamLembur($jumlahJam) {
        $this->jumlahJamLembur = $jumlahJam;
    }
    
    public function getJumlahJamLembur() {
        return $this->jumlahJamLembur;
    }
    
    // ===== IMPLEMENTASI ABSTRACT METHODS (POLIMORFISME) =====
    
    /**
     * POLIMORFISME 1: hitungTakeHomePay() - Khusus SupirTruk
     * 
     * Perhitungan: Gaji Pokok + Uang Makan Jalan + (Lembur * 25% Gaji Pokok per jam)
     * 
     * @return decimal Total gaji bawa pulang sopir truk
     */
    public function hitungTakeHomePay() {
        // Ambil gaji pokok
        $gajiPokok = $this->getGajiPokok();
        $uangMakanJalan = $this->getUangMakanJalan();
        
        // Hitung tunjangan lembur (25% dari gaji per jam)
        $tarifLemburPerJam = ($gajiPokok / 200) * 0.25; // Asumsi 200 jam kerja per bulan
        $uangLembur = $this->jumlahJamLembur * $tarifLemburPerJam;
        
        // Total Take Home Pay
        $totalTakeHome = $gajiPokok + $uangMakanJalan + $uangLembur;
        
        return $totalTakeHome;
    }
    
    /**
     * POLIMORFISME 2: evaluasiSOPKerja() - Khusus SupirTruk
     * 
     * Kriteria evaluasi untuk sopir truk:
     * - Kelayakan SIM B
     * - Riwayat kecelakaan (diasumsikan 0 untuk sempurna)
     * - Ketepatan waktu pengiriman
     * - Kelengkapan dokumen
     * 
     * @return array Hasil evaluasi dengan skor dan status
     */
    public function evaluasiSOPKerja() {
        $evaluasi = [
            'nama_staff' => $this->getNamaLengkap(),
            'jenis_staff' => 'SupirTruk',
            'skor_total' => 0,
            'detail' => []
        ];
        
        // Kriteria 1: Kelayakan SIM B
        $kriteria1 = [
            'nama_kriteria' => 'Kelayakan SIM B',
            'skor' => !empty($this->nomorSIM_B) ? 25 : 0,
            'status' => !empty($this->nomorSIM_B) ? 'VALID' : 'TIDAK VALID'
        ];
        $evaluasi['detail'][] = $kriteria1;
        
        // Kriteria 2: Riwayat Kecelakaan (0 = sempurna)
        $kriteria2 = [
            'nama_kriteria' => 'Riwayat Kecelakaan',
            'skor' => 25,
            'status' => 'BERSIH'
        ];
        $evaluasi['detail'][] = $kriteria2;
        
        // Kriteria 3: Ketepatan Waktu (diasumsikan 90%)
        $kriteria3 = [
            'nama_kriteria' => 'Ketepatan Waktu Pengiriman',
            'skor' => 25,
            'status' => 'MEMUASKAN'
        ];
        $evaluasi['detail'][] = $kriteria3;
        
        // Kriteria 4: Kelengkapan Dokumen
        $kriteria4 = [
            'nama_kriteria' => 'Kelengkapan Dokumen',
            'skor' => 25,
            'status' => 'LENGKAP'
        ];
        $evaluasi['detail'][] = $kriteria4;
        
        // Hitung total skor
        foreach ($evaluasi['detail'] as $detail) {
            $evaluasi['skor_total'] += $detail['skor'];
        }
        
        // Tentukan status keseluruhan
        if ($evaluasi['skor_total'] >= 90) {
            $evaluasi['status_keseluruhan'] = 'LULUS - SIAP OPERASIONAL';
        } elseif ($evaluasi['skor_total'] >= 75) {
            $evaluasi['status_keseluruhan'] = 'LULUS DENGAN CATATAN';
        } else {
            $evaluasi['status_keseluruhan'] = 'TIDAK LULUS - PERLU PELATIHAN';
        }
        
        return $evaluasi;
    }
    
    /**
     * Override: getJenisStaff
     */
    public function getJenisStaff() {
        return 'SupirTruk';
    }
    
    /**
     * Override: displayInfo - Tambah info spesifik SupirTruk
     */
    public function displayInfo() {
        $baseInfo = parent::displayInfo();
        $baseInfo['nomor_sim_b'] = $this->nomorSIM_B;
        $baseInfo['uang_makan_jalan'] = $this->uangMakanJalan;
        $baseInfo['rute_operasional'] = $this->ruteOperasional;
        return $baseInfo;
    }
    
    /**
     * Simpan data SupirTruk ke database
     */
    public function save() {
        $sql = "INSERT INTO staff (id_staff_code, nama_lengkap, gaji_pokok, jam_kerja, jenis_staff, nomor_sim_b, uang_makan_jalan, rute_tol) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->getConn()->prepare($sql);
        $jenisStaff = $this->getJenisStaff();
        $gajiPokok = $this->getGajiPokok();
        $jamKerja = $this->getJamKerja();
        
        $stmt->bind_param("ssdsssds", 
            $this->getIdStaffCode(),
            $this->getNamaLengkap(),
            $gajiPokok,
            $jamKerja,
            $jenisStaff,
            $this->nomorSIM_B,
            $this->uangMakanJalan,
            $this->ruteOperasional
        );
        
        return $stmt->execute();
    }
}
?>