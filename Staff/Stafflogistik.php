<?php
/**
 * Abstract Class: StaffLogistik
 * Kelas abstrak utama untuk mengelola data pegawai logistik
 * Menerapkan Pilar OOP: Abstraksi, Enkapsulasi, Inheritance
 */

abstract class StaffLogistik {
    
    // ===== ENKAPSULASI: Private Attributes =====
    private $idStaff;
    private $idStaffCode;
    private $namaLengkap;
    private $gajiPokok;
    private $jamKerja;
    
    // Protected untuk akses ke subclass
    protected $conn;
    
    /**
     * Constructor
     */
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // ===== GETTER & SETTER (Enkapsulasi) =====
    
    public function setIdStaff($idStaff) {
        $this->idStaff = $idStaff;
    }
    
    public function getIdStaff() {
        return $this->idStaff;
    }
    
    public function setIdStaffCode($idStaffCode) {
        $this->idStaffCode = $idStaffCode;
    }
    
    public function getIdStaffCode() {
        return $this->idStaffCode;
    }
    
    public function setNamaLengkap($namaLengkap) {
        $this->namaLengkap = $namaLengkap;
    }
    
    public function getNamaLengkap() {
        return $this->namaLengkap;
    }
    
    public function setGajiPokok($gajiPokok) {
        $this->gajiPokok = $gajiPokok;
    }
    
    public function getGajiPokok() {
        return $this->gajiPokok;
    }
    
    public function setJamKerja($jamKerja) {
        $this->jamKerja = $jamKerja;
    }
    
    public function getJamKerja() {
        return $this->jamKerja;
    }
    
    public function getConn() {
        return $this->conn;
    }
    
    // ===== ABSTRACT METHODS (Polimorfisme - harus diimplementasikan di subclass) =====
    
    /**
     * Metode Abstrak 1: hitungTakeHomePay
     * Setiap jenis staff menghitung gaji bawa pulang dengan cara berbeda
     * 
     * @return decimal Jumlah gaji bawa pulang
     */
    abstract public function hitungTakeHomePay();
    
    /**
     * Metode Abstrak 2: evaluasiSOPKerja
     * Setiap jenis staff memiliki kriteria evaluasi yang berbeda
     * 
     * @return array Hasil evaluasi SOP kerja
     */
    abstract public function evaluasiSOPKerja();
    
    /**
     * Metode Umum: Simpan Data Staff ke Database
     */
    public function save() {
        $sql = "INSERT INTO staff (id_staff_code, nama_lengkap, gaji_pokok, jam_kerja, jenis_staff) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $jenis_staff = $this->getJenisStaff();
        
        $stmt->bind_param("sssds", 
            $this->idStaffCode, 
            $this->namaLengkap, 
            $this->gajiPokok, 
            $this->jamKerja,
            $jenis_staff
        );
        
        return $stmt->execute();
    }
    
    /**
     * Metode untuk mendapatkan jenis staff (dioverride di subclass)
     */
    abstract public function getJenisStaff();
    
    /**
     * Metode untuk menampilkan info staff
     */
    public function displayInfo() {
        return [
            'id_staff' => $this->idStaff,
            'id_staff_code' => $this->idStaffCode,
            'nama_lengkap' => $this->namaLengkap,
            'gaji_pokok' => $this->gajiPokok,
            'jam_kerja' => $this->jamKerja,
            'jenis_staff' => $this->getJenisStaff()
        ];
    }
}
?>