<?php
/**
 * StaffManager
 * Kelas untuk mengelola operasi CRUD staff dan business logic terkait
 */

require_once 'StaffLogistik.php';
require_once 'SupirTruk.php';
require_once 'AdminGudang.php';
require_once 'KurirMotor.php';

class StaffManager {
    
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * CREATE: Tambah staff baru ke database
     */
    public function addStaff($idStaffCode, $namaLengkap, $gajiPokok, $jamKerja, $jenisStaff, $dataEkstra = []) {
        try {
            // Cek apakah id_staff_code sudah ada
            $checkSql = "SELECT id_staff FROM staff WHERE id_staff_code = ?";
            $checkStmt = $this->conn->prepare($checkSql);
            $checkStmt->bind_param("s", $idStaffCode);
            $checkStmt->execute();
            
            if ($checkStmt->get_result()->num_rows > 0) {
                return ['success' => false, 'message' => 'ID Staff Code sudah ada'];
            }
            
            // Buat object berdasarkan jenis staff
            $staff = null;
            
            if ($jenisStaff == 'SupirTruk') {
                $staff = new SupirTruk($this->conn);
                $staff->setNomorSIM_B($dataEkstra['nomor_sim_b'] ?? '');
                $staff->setUangMakanJalan($dataEkstra['uang_makan_jalan'] ?? 0);
                $staff->setRuteOperasional($dataEkstra['rute_operasional'] ?? '');
                
            } elseif ($jenisStaff == 'AdminGudang') {
                $staff = new AdminGudang($this->conn);
                $staff->setShiftKerja($dataEkstra['shift_kerja'] ?? 'Pagi');
                $staff->setZonaGudang($dataEkstra['zona_gudang'] ?? '');
                
            } elseif ($jenisStaff == 'KurirMotor') {
                $staff = new KurirMotor($this->conn);
                $staff->setPlatNomorMotor($dataEkstra['plat_nomor_motor'] ?? '');
                $staff->setAreaCakupan($dataEkstra['area_cakupan'] ?? '');
            }
            
            if ($staff === null) {
                return ['success' => false, 'message' => 'Jenis staff tidak valid'];
            }
            
            // Set atribut umum
            $staff->setIdStaffCode($idStaffCode);
            $staff->setNamaLengkap($namaLengkap);
            $staff->setGajiPokok($gajiPokok);
            $staff->setJamKerja($jamKerja);
            
            // Simpan ke database
            if ($staff->save()) {
                return ['success' => true, 'message' => 'Staff berhasil ditambahkan'];
            } else {
                return ['success' => false, 'message' => 'Gagal menyimpan data: ' . $this->conn->error];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * READ: Ambil semua staff atau staff tertentu
     */
    public function getAllStaff($filter = null) {
        try {
            $sql = "SELECT * FROM staff";
            $conditions = [];
            
            if ($filter) {
                if (isset($filter['jenis_staff']) && !empty($filter['jenis_staff'])) {
                    $conditions[] = "jenis_staff = '" . $this->conn->real_escape_string($filter['jenis_staff']) . "'";
                }
                if (isset($filter['search']) && !empty($filter['search'])) {
                    $search = $this->conn->real_escape_string($filter['search']);
                    $conditions[] = "(nama_lengkap LIKE '%$search%' OR id_staff_code LIKE '%$search%')";
                }
            }
            
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }
            
            $sql .= " ORDER BY id_staff DESC";
            
            $result = $this->conn->query($sql);
            
            if ($result->num_rows > 0) {
                $staffList = [];
                while ($row = $result->fetch_assoc()) {
                    $staffList[] = $row;
                }
                return ['success' => true, 'data' => $staffList, 'total' => count($staffList)];
            } else {
                return ['success' => false, 'message' => 'Tidak ada data staff', 'data' => []];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage(), 'data' => []];
        }
    }
    
    /**
     * READ: Ambil staff berdasarkan ID
     */
    public function getStaffById($idStaff) {
        try {
            $sql = "SELECT * FROM staff WHERE id_staff = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $idStaff);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                return ['success' => true, 'data' => $result->fetch_assoc()];
            } else {
                return ['success' => false, 'message' => 'Staff tidak ditemukan'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * UPDATE: Perbarui data staff
     */
    public function updateStaff($idStaff, $namaLengkap = null, $gajiPokok = null, $jamKerja = null, $dataEkstra = []) {
        try {
            // Ambil data staff yang ada
            $getResult = $this->getStaffById($idStaff);
            if (!$getResult['success']) {
                return $getResult;
            }
            
            $staffData = $getResult['data'];
            $jenisStaff = $staffData['jenis_staff'];
            
            // Buat update statement berdasarkan jenis staff
            $updateFields = [];
            $types = "";
            $values = [];
            
            if ($namaLengkap !== null) {
                $updateFields[] = "nama_lengkap = ?";
                $types .= "s";
                $values[] = $namaLengkap;
            }
            
            if ($gajiPokok !== null) {
                $updateFields[] = "gaji_pokok = ?";
                $types .= "d";
                $values[] = $gajiPokok;
            }
            
            if ($jamKerja !== null) {
                $updateFields[] = "jam_kerja = ?";
                $types .= "i";
                $values[] = $jamKerja;
            }
            
            // Update field spesifik berdasarkan jenis
            if ($jenisStaff == 'SupirTruk') {
                if (isset($dataEkstra['nomor_sim_b'])) {
                    $updateFields[] = "nomor_sim_b = ?";
                    $types .= "s";
                    $values[] = $dataEkstra['nomor_sim_b'];
                }
                if (isset($dataEkstra['uang_makan_jalan'])) {
                    $updateFields[] = "uang_makan_jalan = ?";
                    $types .= "d";
                    $values[] = $dataEkstra['uang_makan_jalan'];
                }
                if (isset($dataEkstra['rute_operasional'])) {
                    $updateFields[] = "rute_tol = ?";
                    $types .= "s";
                    $values[] = $dataEkstra['rute_operasional'];
                }
            } elseif ($jenisStaff == 'AdminGudang') {
                if (isset($dataEkstra['shift_kerja'])) {
                    $updateFields[] = "shift_kerja = ?";
                    $types .= "s";
                    $values[] = $dataEkstra['shift_kerja'];
                }
                if (isset($dataEkstra['zona_gudang'])) {
                    $updateFields[] = "zona_gudang = ?";
                    $types .= "s";
                    $values[] = $dataEkstra['zona_gudang'];
                }
            } elseif ($jenisStaff == 'KurirMotor') {
                if (isset($dataEkstra['plat_nomor_motor'])) {
                    $updateFields[] = "plat_nomor_motor = ?";
                    $types .= "s";
                    $values[] = $dataEkstra['plat_nomor_motor'];
                }
                if (isset($dataEkstra['area_cakupan'])) {
                    $updateFields[] = "area_cakupan = ?";
                    $types .= "s";
                    $values[] = $dataEkstra['area_cakupan'];
                }
            }
            
            if (empty($updateFields)) {
                return ['success' => false, 'message' => 'Tidak ada data yang diupdate'];
            }
            
            $sql = "UPDATE staff SET " . implode(", ", $updateFields) . " WHERE id_staff = ?";
            $types .= "i";
            $values[] = $idStaff;
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param($types, ...$values);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Data staff berhasil diperbarui'];
            } else {
                return ['success' => false, 'message' => 'Gagal mengupdate data: ' . $this->conn->error];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * DELETE: Hapus staff dari database
     */
    public function deleteStaff($idStaff) {
        try {
            $sql = "DELETE FROM staff WHERE id_staff = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $idStaff);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Staff berhasil dihapus'];
            } else {
                return ['success' => false, 'message' => 'Gagal menghapus staff: ' . $this->conn->error];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * POLIMORFISME: Hitung Take Home Pay untuk staff tertentu
     */
    public function hitungTakeHomePay($idStaff) {
        try {
            $staffData = $this->getStaffById($idStaff);
            if (!$staffData['success']) {
                return $staffData;
            }
            
            $data = $staffData['data'];
            $jenisStaff = $data['jenis_staff'];
            
            $staff = null;
            
            if ($jenisStaff == 'SupirTruk') {
                $staff = new SupirTruk($this->conn);
                $staff->setNomorSIM_B($data['nomor_sim_b']);
                $staff->setUangMakanJalan($data['uang_makan_jalan']);
                $staff->setJumlahJamLembur(0); // Bisa diset dari parameter lain
                
            } elseif ($jenisStaff == 'AdminGudang') {
                $staff = new AdminGudang($this->conn);
                $staff->setShiftKerja($data['shift_kerja']);
                $staff->setZonaGudang($data['zona_gudang']);
                
            } elseif ($jenisStaff == 'KurirMotor') {
                $staff = new KurirMotor($this->conn);
                $staff->setPlatNomorMotor($data['plat_nomor_motor']);
                $staff->setAreaCakupan($data['area_cakupan']);
            }
            
            if ($staff === null) {
                return ['success' => false, 'message' => 'Jenis staff tidak valid'];
            }
            
            $staff->setGajiPokok($data['gaji_pokok']);
            $staff->setJamKerja($data['jam_kerja']);
            
            $takeHome = $staff->hitungTakeHomePay();
            
            return [
                'success' => true,
                'data' => [
                    'nama_staff' => $data['nama_lengkap'],
                    'jenis_staff' => $jenisStaff,
                    'gaji_pokok' => $data['gaji_pokok'],
                    'take_home_pay' => $takeHome
                ]
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * POLIMORFISME: Evaluasi SOP Kerja untuk staff tertentu
     */
    public function evaluasiSOPKerja($idStaff, $metrikData = []) {
        try {
            $staffData = $this->getStaffById($idStaff);
            if (!$staffData['success']) {
                return $staffData;
            }
            
            $data = $staffData['data'];
            $jenisStaff = $data['jenis_staff'];
            
            $staff = null;
            
            if ($jenisStaff == 'SupirTruk') {
                $staff = new SupirTruk($this->conn);
                
            } elseif ($jenisStaff == 'AdminGudang') {
                $staff = new AdminGudang($this->conn);
                $staff->setJumlahBarangDiproses($metrikData['jumlah_barang'] ?? 0);
                $staff->setJumlahErrorAdministrasi($metrikData['jumlah_error'] ?? 0);
                $staff->setShiftKerja($data['shift_kerja']);
                
            } elseif ($jenisStaff == 'KurirMotor') {
                $staff = new KurirMotor($this->conn);
                $staff->setJumlahPaketAntar($metrikData['jumlah_paket_antar'] ?? 0);
                $staff->setJumlahPaketTerima($metrikData['jumlah_paket_terima'] ?? 0);
                $staff->setPersentaseAccuracy($metrikData['accuracy_persen'] ?? 100);
            }
            
            if ($staff === null) {
                return ['success' => false, 'message' => 'Jenis staff tidak valid'];
            }
            
            $staff->setNamaLengkap($data['nama_lengkap']);
            $evaluasi = $staff->evaluasiSOPKerja();
            
            return ['success' => true, 'data' => $evaluasi];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Hitung statistik staff per jenis
     */
    public function getStaffStatistics() {
        try {
            $sql = "SELECT jenis_staff, COUNT(*) as jumlah FROM staff GROUP BY jenis_staff";
            $result = $this->conn->query($sql);
            
            $stats = [
                'SupirTruk' => 0,
                'AdminGudang' => 0,
                'KurirMotor' => 0
            ];
            
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $stats[$row['jenis_staff']] = $row['jumlah'];
                }
            }
            
            return [
                'success' => true,
                'data' => $stats,
                'total' => array_sum($stats)
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
?>