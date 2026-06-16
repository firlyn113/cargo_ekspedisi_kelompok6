<?php
// Staff/Index.php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "ekspedisi_logistik";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// ============================================
// ABSTRACT CLASS STAFFLOGISTIK
// ============================================
abstract class StaffLogistik {
    protected $conn;
    private $id_staff;
    private $id_staff_code;
    private $nama_lengkap;
    private $gaji_pokok;
    private $jam_kerja;
    private $jenis_staff;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function setIdStaff($id) { $this->id_staff = $id; }
    public function setIdStaffCode($code) { $this->id_staff_code = $code; }
    public function setNamaLengkap($nama) { $this->nama_lengkap = $nama; }
    public function setGajiPokok($gaji) { $this->gaji_pokok = $gaji; }
    public function setJamKerja($jam) { $this->jam_kerja = $jam; }
    public function setJenisStaff($jenis) { $this->jenis_staff = $jenis; }
    
    public function getIdStaff() { return $this->id_staff; }
    public function getIdStaffCode() { return $this->id_staff_code; }
    public function getNamaLengkap() { return $this->nama_lengkap; }
    public function getGajiPokok() { return $this->gaji_pokok; }
    public function getJamKerja() { return $this->jam_kerja; }
    public function getJenisStaff() { return $this->jenis_staff; }
    
    public abstract function hitungTakeHomePay();
    public abstract function evaluasiSOPKerja();
    
    public function simpan() {
        $query = "INSERT INTO staff (id_staff_code, nama_lengkap, gaji_pokok, jam_kerja, jenis_staff) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssdis", 
            $this->id_staff_code, 
            $this->nama_lengkap, 
            $this->gaji_pokok, 
            $this->jam_kerja, 
            $this->jenis_staff
        );
        
        if ($stmt->execute()) {
            $id = $this->conn->insert_id;
            $stmt->close();
            return $id;
        }
        $stmt->close();
        return false;
    }
    
    public static function getAll($conn) {
        $result = $conn->query("SELECT * FROM staff ORDER BY id_staff DESC");
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
    
    public static function delete($conn, $id) {
        $stmt = $conn->prepare("DELETE FROM staff WHERE id_staff = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    
    public static function countByType($conn, $jenis) {
        $result = $conn->query("SELECT COUNT(*) as total FROM staff WHERE jenis_staff = '$jenis'");
        return $result->fetch_assoc()['total'];
    }
}

// ============================================
// CLASS SUPIRTRUK
// ============================================
class SupirTruk extends StaffLogistik {
    private $nomor_sim_b;
    private $uang_makan_jalan;
    
    public function __construct($conn) {
        parent::__construct($conn);
        $this->setJenisStaff('SupirTruk');
    }
    
    public function setNomorSIM($sim) { $this->nomor_sim_b = $sim; }
    public function getNomorSIM() { return $this->nomor_sim_b; }
    public function setUangMakanJalan($uang) { $this->uang_makan_jalan = $uang; }
    public function getUangMakanJalan() { return $this->uang_makan_jalan; }
    
    public function hitungTakeHomePay() {
        return $this->getGajiPokok() + ($this->uang_makan_jalan ?? 0);
    }
    
    public function evaluasiSOPKerja() {
        return "✅ SIM B valid\n✅ Catatan mengemudi baik";
    }
}

// ============================================
// CLASS ADMINGUDANG
// ============================================
class AdminGudang extends StaffLogistik {
    private $shift_kerja;
    private $zona_gudang;
    
    public function __construct($conn) {
        parent::__construct($conn);
        $this->setJenisStaff('AdminGudang');
    }
    
    public function setShiftKerja($shift) { $this->shift_kerja = $shift; }
    public function getShiftKerja() { return $this->shift_kerja; }
    public function setZonaGudang($zona) { $this->zona_gudang = $zona; }
    public function getZonaGudang() { return $this->zona_gudang; }
    
    public function hitungTakeHomePay() {
        $bonus_shift = ($this->shift_kerja == 'Malam') ? 200000 : 0;
        return $this->getGajiPokok() + $bonus_shift;
    }
    
    public function evaluasiSOPKerja() {
        return "✅ Inventaris rapi\n✅ SOP gudang terpenuhi";
    }
}

// ============================================
// CLASS KURIRMOTOR
// ============================================
class KurirMotor extends StaffLogistik {
    private $plat_nomor_motor;
    private $area_cakupan;
    
    public function __construct($conn) {
        parent::__construct($conn);
        $this->setJenisStaff('KurirMotor');
    }
    
    public function setPlatNomor($plat) { $this->plat_nomor_motor = $plat; }
    public function getPlatNomor() { return $this->plat_nomor_motor; }
    public function setAreaCakupan($area) { $this->area_cakupan = $area; }
    public function getAreaCakupan() { return $this->area_cakupan; }
    
    public function hitungTakeHomePay() {
        $bonus_paket = 50000; // bonus per paket
        return $this->getGajiPokok() + $bonus_paket;
    }
    
    public function evaluasiSOPKerja() {
        return "✅ Plat motor terdaftar\n✅ Area cakupan jelas";
    }
}

// ============================================
// PROSES CRUD
// ============================================
$message = '';
$error = '';
$active_menu = 'staff';

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if (StaffLogistik::delete($conn, $id)) {
        $message = "✅ Staff berhasil dihapus!";
    } else {
        $error = "❌ Gagal menghapus staff!";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jenis = $_POST['jenis_staff'];
    $staff = null;
    
    switch ($jenis) {
        case 'SupirTruk':
            $staff = new SupirTruk($conn);
            $staff->setIdStaffCode($_POST['id_staff_code']);
            $staff->setNamaLengkap($_POST['nama_lengkap']);
            $staff->setGajiPokok($_POST['gaji_pokok']);
            $staff->setJamKerja($_POST['jam_kerja']);
            $staff->setNomorSIM($_POST['nomor_sim']);
            $staff->setUangMakanJalan($_POST['uang_makan'] ?? 0);
            break;
            
        case 'AdminGudang':
            $staff = new AdminGudang($conn);
            $staff->setIdStaffCode($_POST['id_staff_code']);
            $staff->setNamaLengkap($_POST['nama_lengkap']);
            $staff->setGajiPokok($_POST['gaji_pokok']);
            $staff->setJamKerja($_POST['jam_kerja']);
            $staff->setShiftKerja($_POST['shift_kerja']);
            $staff->setZonaGudang($_POST['zona_gudang']);
            break;
            
        case 'KurirMotor':
            $staff = new KurirMotor($conn);
            $staff->setIdStaffCode($_POST['id_staff_code']);
            $staff->setNamaLengkap($_POST['nama_lengkap']);
            $staff->setGajiPokok($_POST['gaji_pokok']);
            $staff->setJamKerja($_POST['jam_kerja']);
            $staff->setPlatNomor($_POST['plat_nomor']);
            $staff->setAreaCakupan($_POST['area_cakupan']);
            break;
    }
    
    if ($staff && $staff->simpan()) {
        $message = "✅ Staff " . $jenis . " berhasil ditambahkan!";
        header("Location: Index.php?msg=" . urlencode($message));
        exit();
    } else {
        $error = "❌ Gagal menambahkan staff!";
    }
}

if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
}

$staff_list = StaffLogistik::getAll($conn);
$total_admin = StaffLogistik::countByType($conn, 'AdminGudang');
$total_kurir = StaffLogistik::countByType($conn, 'KurirMotor');
$total_supir = StaffLogistik::countByType($conn, 'SupirTruk');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Staff - Ekspedisi Logistik</title>
    <style>
        :root {
            --primary-color: #1a3c2a;
            --secondary-color: #2ecc71;
            --danger-color: #e74c3c;
            --sidebar-width: 250px;
            --bg-gradient-start: #1a3c2a;
            --bg-gradient-end: #2ecc71;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
            display: flex;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: var(--sidebar-width);
            background: #1a1a2e;
            color: white;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 2px 0 15px rgba(0,0,0,0.3);
        }

        .sidebar-brand {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-brand .logo-icon { font-size: 40px; display: block; margin-bottom: 8px; }
        .sidebar-brand h2 { font-size: 20px; color: white; }
        .sidebar-brand small { color: #aaa; font-size: 11px; }

        .sidebar-menu { padding: 15px 0; }
        .sidebar-menu .menu-label { padding: 10px 20px; color: #666; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; font-weight: bold; }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #ccc;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
            gap: 12px;
        }
        .sidebar-menu a:hover { background: rgba(255,255,255,0.05); color: white; border-left-color: var(--secondary-color); }
        .sidebar-menu a.active { background: rgba(46, 204, 113, 0.15); color: white; border-left-color: var(--secondary-color); }
        .sidebar-menu a .icon { font-size: 18px; width: 28px; text-align: center; }
        .sidebar-menu a .menu-text { font-size: 14px; }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            padding: 25px;
            min-height: 100vh;
        }

        /* ===== HEADER ===== */
        .header {
            background: white;
            padding: 20px 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left h1 { color: var(--primary-color); font-size: 24px; }
        .header-left p { color: #888; font-size: 14px; margin-top: 3px; }

        .hamburger {
            display: none;
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: var(--primary-color);
            padding: 5px 10px;
        }

        /* ===== SEARCH BAR ===== */
        .search-bar {
            background: white;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }

        .search-bar input {
            flex: 1;
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            min-width: 200px;
        }

        .search-bar input:focus {
            outline: none;
            border-color: var(--secondary-color);
        }

        .search-bar .filter-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            background: #f0f2f5;
            color: #555;
        }

        .search-bar .filter-badge.active {
            background: var(--secondary-color);
            color: white;
        }

        /* ===== STATS ROW ===== */
        .stats-row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .stat-item {
            background: white;
            padding: 12px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .stat-item .stat-icon { font-size: 24px; }
        .stat-item .stat-info { display: flex; flex-direction: column; }
        .stat-item .stat-info .number { font-size: 20px; font-weight: bold; color: var(--primary-color); }
        .stat-item .stat-info .label { font-size: 12px; color: #888; }

        /* ===== TABLE ===== */
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .table-header {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .table-header h2 {
            font-size: 18px;
            color: var(--primary-color);
        }

        .btn-add {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-add:hover {
            background: var(--primary-color);
            transform: scale(1.02);
        }

        .table-wrapper {
            overflow-x: auto;
            padding: 0 20px 20px 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tr:hover { background: #f8f9fa; }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-supir { background: #3498db; color: white; }
        .badge-admin { background: #2ecc71; color: white; }
        .badge-kurir { background: #e67e22; color: white; }

        .btn-hapus {
            background: var(--danger-color);
            color: white;
            padding: 5px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
            transition: all 0.3s;
            display: inline-block;
        }

        .btn-hapus:hover {
            background: #c0392b;
            transform: scale(1.05);
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #999;
        }

        .empty-state .empty-icon { font-size: 48px; margin-bottom: 15px; }

        /* ===== MODAL ===== */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .modal.open { display: flex; }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }

        .modal-content .close-btn {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 28px;
            cursor: pointer;
            color: #999;
            background: none;
            border: none;
        }

        .modal-content h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
            border-bottom: 3px solid var(--secondary-color);
            padding-bottom: 10px;
        }

        .modal-content .form-group {
            margin-bottom: 15px;
        }

        .modal-content label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .modal-content input, .modal-content select {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }

        .modal-content input:focus, .modal-content select:focus {
            outline: none;
            border-color: var(--secondary-color);
        }

        .modal-content button[type="submit"] {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
        }

        .modal-content button[type="submit"]:hover {
            background: var(--primary-color);
        }

        .modal-content .field-group {
            display: none;
        }

        /* ===== ALERT ===== */
        .alert {
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .alert-success { background: