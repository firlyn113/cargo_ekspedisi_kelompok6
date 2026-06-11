<?php
/**
 * File: proses_kargo.php
 * Fungsi: Memproses operasi CRUD untuk modul kargo
 */

session_start();
require_once '../config/koneksi.php';
require_once 'KargoReguler.php';
require_once 'KargoBahanKimia.php';
require_once 'KargoPecahBelah.php';

class ProsesKargo {
    private $koneksi;
    
    public function __construct($koneksi) {
        $this->koneksi = $koneksi;
    }
    
    // Tambah data kargo
    public function tambahKargo($data) {
        $id_resi = $data['id_resi'];
        $pengirim = $data['pengirim'];
        $kota_tujuan = $data['kota_tujuan'];
        $berat_barang = $data['berat_barang'];
        $tarif_dasar = $data['tarif_dasar'];
        $jenis_kargo = $data['jenis_kargo'];
        
        try {
            switch($jenis_kargo) {
                case 'Reguler':
                    $kargo = new KargoReguler(
                        $id_resi, $pengirim, $kota_tujuan, $berat_barang, $tarif_dasar,
                        $data['jenis_paket'], $data['estimasi_hari']
                    );
                    $dataTambahan = [
                        'jenis_paket' => $data['jenis_paket'],
                        'estimasi_hari' => $data['estimasi_hari']
                    ];
                    break;
                    
                case 'BahanKimia':
                    $kargo = new KargoBahanKimia(
                        $id_resi, $pengirim, $kota_tujuan, $berat_barang, $tarif_dasar,
                        $data['tingkat_bahaya'], $data['jenis_sertifikasi']
                    );
                    $dataTambahan = [
                        'tingkat_bahaya' => $data['tingkat_bahaya'],
                        'jenis_sertifikasi' => $data['jenis_sertifikasi']
                    ];
                    break;
                    
                case 'PecahBelah':
                    $kargo = new KargoPecahBelah(
                        $id_resi, $pengirim, $kota_tujuan, $berat_barang, $tarif_dasar,
                        $data['ketebalan_bubble_wrap'], $data['biaya_asuransi_wajib'] ?? null
                    );
                    $dataTambahan = [
                        'ketebalan_bubble_wrap' => $data['ketebalan_bubble_wrap'],
                        'biaya_asuransi_wajib' => $kargo->getBiayaAsuransiWajib()
                    ];
                    break;
                    
                default:
                    return ['status' => false, 'pesan' => 'Jenis kargo tidak valid'];
            }
            
            // Validasi data
            if(!$kargo->validasiData()) {
                return ['status' => false, 'pesan' => 'Data tidak lengkap atau tidak valid'];
            }
            
            // Simpan ke database
            if($kargo->simpanKeDatabase($this->koneksi, $dataTambahan)) {
                return ['status' => true, 'pesan' => 'Data kargo berhasil disimpan', 'tarif' => $kargo->hitungTarifPengiriman()];
            } else {
                return ['status' => false, 'pesan' => 'Gagal menyimpan data'];
            }
            
        } catch(Exception $e) {
            return ['status' => false, 'pesan' => 'Error: ' . $e->getMessage()];
        }
    }
    
    // Ambil semua data kargo
    public function ambilSemuaKargo() {
        $sql = "SELECT * FROM kargo ORDER BY created_at DESC";
        $result = $this->koneksi->query($sql);
        
        $data = [];
        if($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }
    
    // Ambil detail kargo berdasarkan ID
    public function ambilDetailKargo($id_kargo) {
        $sql = "SELECT * FROM kargo WHERE id_kargo = ?";
        $stmt = $this->koneksi->prepare($sql);
        $stmt->bind_param("i", $id_kargo);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }
    
    // Hapus kargo
    public function hapusKargo($id_kargo) {
        $sql = "DELETE FROM kargo WHERE id_kargo = ?";
        $stmt = $this->koneksi->prepare($sql);
        $stmt->bind_param("i", $id_kargo);
        
        if($stmt->execute()) {
            return ['status' => true, 'pesan' => 'Data kargo berhasil dihapus'];
        }
        return ['status' => false, 'pesan' => 'Gagal menghapus data'];
    }
    
    // Update kargo
    public function updateKargo($id_kargo, $data) {
        $sql = "UPDATE kargo SET pengirim = ?, kota_tujuan = ?, berat_barang = ?, tarif_dasar_per_kg = ? WHERE id_kargo = ?";
        $stmt = $this->koneksi->prepare($sql);
        $stmt->bind_param("ssddi", $data['pengirim'], $data['kota_tujuan'], $data['berat_barang'], $data['tarif_dasar'], $id_kargo);
        
        if($stmt->execute()) {
            return ['status' => true, 'pesan' => 'Data kargo berhasil diupdate'];
        }
        return ['status' => false, 'pesan' => 'Gagal mengupdate data'];
    }
}

// Proses berdasarkan request method
$proses = new ProsesKargo($koneksi);
$response = [];

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'tambah':
                $response = $proses->tambahKargo($_POST);
                break;
            case 'hapus':
                $response = $proses->hapusKargo($_POST['id_kargo']);
                break;
            case 'update':
                $response = $proses->updateKargo($_POST['id_kargo'], $_POST);
                break;
        }
    }
    
    $_SESSION['kargo_message'] = $response;
    header('Location: index.php');
    exit();
}
?>