<?php
/**
 * StaffManager
 * Kelas bantu untuk mengambil data staff dari database dan
 * mendelegasikan kalkulasi ke masing-masing subclass (Polimorfisme).
 *
 * Karena modul ini hanya bersifat READ-ONLY (tidak ada CRUD dari UI),
 * method addStaff / updateStaff / deleteStaff tetap ada di sini
 * sebagai business-logic layer, namun tidak diekspos ke halaman index.
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

    // =========================================================
    //  FACTORY — membuat objek subclass yang tepat dari data DB
    // =========================================================

    /**
     * Buat instance subclass berdasarkan jenis_staff dari database.
     * Digunakan oleh hitungTakeHomePay() dan evaluasiSOPKerja().
     *
     * @param  array         $data  Satu baris dari tabel staff
     * @return StaffLogistik|null
     */
    private function buatObjekStaff(array $data): ?StaffLogistik {
        switch ($data['jenis_staff']) {

            case 'SupirTruk':
                $obj = new SupirTruk($this->conn);
                $obj->setNomorSIM_B($data['nomor_sim_b']     ?? '');
                $obj->setUangMakanJalan($data['uang_makan_jalan'] ?? 0);
                break;

            case 'AdminGudang':
                $obj = new AdminGudang($this->conn);
                $obj->setShiftKerja($data['shift_kerja'] ?? 'Pagi');
                $obj->setZonaGudang($data['zona_gudang'] ?? '');
                break;

            case 'KurirMotor':
                $obj = new KurirMotor($this->conn);
                $obj->setPlatNomorMotor($data['plat_nomor_motor'] ?? '');
                $obj->setAreaCakupan($data['area_cakupan']       ?? '');
                break;

            default:
                return null;
        }

        // Atribut umum dari abstract class StaffLogistik
        $obj->setIdStaff($data['id_staff']);
        $obj->setNamaLengkap($data['nama_lengkap']);
        $obj->setGajiPokok($data['gaji_pokok']);
        $obj->setJamKerja($data['jam_kerja']);

        return $obj;
    }

    // =========================================================
    //  READ — Ambil data staff dari database
    // =========================================================

    /**
     * Ambil semua staff (dengan filter opsional).
     *
     * @param  array|null $filter  ['jenis_staff' => ..., 'search' => ...]
     * @return array
     */
    public function getAllStaff(?array $filter = null): array {
        try {
            $sql        = "SELECT * FROM staff";
            $conditions = [];

            if ($filter) {
                if (!empty($filter['jenis_staff'])) {
                    $jenis        = $this->conn->real_escape_string($filter['jenis_staff']);
                    $conditions[] = "jenis_staff = '$jenis'";
                }
                if (!empty($filter['search'])) {
                    $s            = $this->conn->real_escape_string($filter['search']);
                    $conditions[] = "(nama_lengkap LIKE '%$s%' OR id_staff_code LIKE '%$s%')";
                }
            }

            if ($conditions) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }
            $sql .= " ORDER BY id_staff DESC";

            $result = $this->conn->query($sql);
            $list   = [];
            while ($row = $result->fetch_assoc()) {
                $list[] = $row;
            }

            return ['success' => true, 'data' => $list, 'total' => count($list)];

        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage(), 'data' => []];
        }
    }

    /**
     * Ambil satu staff berdasarkan id_staff.
     *
     * @param  int   $idStaff
     * @return array
     */
    public function getStaffById(int $idStaff): array {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM staff WHERE id_staff = ?");
            $stmt->bind_param("i", $idStaff);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                return ['success' => true, 'data' => $result->fetch_assoc()];
            }
            return ['success' => false, 'message' => 'Staff tidak ditemukan'];

        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Statistik jumlah staff per jenis.
     *
     * @return array
     */
    public function getStaffStatistics(): array {
        try {
            $result = $this->conn->query(
                "SELECT jenis_staff, COUNT(*) AS jumlah FROM staff GROUP BY jenis_staff"
            );

            $stats = ['SupirTruk' => 0, 'AdminGudang' => 0, 'KurirMotor' => 0];
            while ($row = $result->fetch_assoc()) {
                $stats[$row['jenis_staff']] = (int) $row['jumlah'];
            }

            return ['success' => true, 'data' => $stats, 'total' => array_sum($stats)];

        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // =========================================================
    //  POLIMORFISME — Delegasi kalkulasi ke subclass
    // =========================================================

    /**
     * Hitung Take Home Pay staff tertentu.
     * Memanggil hitungTakeHomePay() milik subclass (polimorfisme).
     *
     * @param  int   $idStaff
     * @return array
     */
    public function hitungTakeHomePay(int $idStaff): array {
        try {
            $staffData = $this->getStaffById($idStaff);
            if (!$staffData['success']) return $staffData;

            $obj = $this->buatObjekStaff($staffData['data']);
            if (!$obj) return ['success' => false, 'message' => 'Jenis staff tidak valid'];

            // Polimorfisme: metode yang dipanggil ditentukan di runtime
            $thp = $obj->hitungTakeHomePay();

            return [
                'success' => true,
                'data'    => [
                    'nama_staff'   => $obj->getNamaLengkap(),
                    'jenis_staff'  => $obj->getJenisStaff(),
                    'gaji_pokok'   => $obj->getGajiPokok(),
                    'take_home_pay' => $thp,
                ],
            ];

        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Evaluasi SOP Kerja staff tertentu.
     * Memanggil evaluasiSOPKerja() milik subclass (polimorfisme).
     *
     * @param  int   $idStaff
     * @param  array $metrikData  Data kinerja tambahan (untuk AdminGudang & KurirMotor)
     * @return array
     */
    public function evaluasiSOPKerja(int $idStaff, array $metrikData = []): array {
        try {
            $staffData = $this->getStaffById($idStaff);
            if (!$staffData['success']) return $staffData;

            $data = $staffData['data'];
            $obj  = $this->buatObjekStaff($data);
            if (!$obj) return ['success' => false, 'message' => 'Jenis staff tidak valid'];

            // Injeksi metrik kinerja ke subclass yang membutuhkan
            if ($obj instanceof AdminGudang) {
                $obj->setJumlahBarangDiproses($metrikData['jumlah_barang'] ?? 0);
                $obj->setJumlahErrorAdministrasi($metrikData['jumlah_error'] ?? 0);
            } elseif ($obj instanceof KurirMotor) {
                $obj->setJumlahPaketAntar($metrikData['jumlah_paket_antar']   ?? 0);
                $obj->setJumlahPaketTerima($metrikData['jumlah_paket_terima'] ?? 0);
                $obj->setPersentaseAccuracy($metrikData['accuracy_persen']    ?? 100);
            }

            // Polimorfisme: metode yang dipanggil ditentukan di runtime
            $evaluasi = $obj->evaluasiSOPKerja();

            return ['success' => true, 'data' => $evaluasi];

        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
?>
