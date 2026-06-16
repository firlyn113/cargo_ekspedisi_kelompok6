<?php
require_once 'AbstractPelanggan.php';
require_once 'PelangganRetail.php';
require_once 'PelangganVIP.php';
require_once 'MitraKorporat.php';

class PelangganService {
    private $koneksi;
    
    public function __construct($koneksi) {
        $this->koneksi = $koneksi;
    }
    
    // CREATE - Menyimpan pelanggan
    public function createPelanggan($data) {
        try {
            $jenis = $data['jenis_pelanggan'];
            $pelanggan = null;
            
            switch ($jenis) {
                case 'Retail':
                    $pelanggan = new PelangganRetail(
                        $data['id_pelanggan_code'], 
                        $data['nama_lengkap'], 
                        $data['promo_voucher'] ?? null, 
                        $data['batas_berat_max'] ?? 50
                    );
                    break;
                case 'VIP':
                    $pelanggan = new PelangganVIP(
                        $data['id_pelanggan_code'], 
                        $data['nama_lengkap'], 
                        $data['akses_layanan_prioritas'] ?? true, 
                        $data['personal_assistant'] ?? null
                    );
                    break;
                case 'MitraKorporat':
                    $pelanggan = new MitraKorporat(
                        $data['id_pelanggan_code'], 
                        $data['nama_lengkap'], 
                        $data['npwp_perusahaan'], 
                        $data['batas_tempo_pembayaran'] ?? null
                    );
                    break;
                default:
                    throw new Exception("Jenis pelanggan tidak valid");
            }
            
            return $this->savePelangganToDatabase($pelanggan);
        } catch (Exception $e) {
            throw new Exception("Error creating pelanggan: " . $e->getMessage());
        }
    }
    
    // Helper untuk menyimpan pelanggan ke database
    private function savePelangganToDatabase($pelanggan) {
        $sql = "INSERT INTO pelanggan (
                    id_pelanggan_code, 
                    nama_lengkap, 
                    total_transaksi_bulan_ini, 
                    poin_reward, 
                    jenis_pelanggan, 
                    created_at,
                    promo_voucher,
                    batas_berat_max,
                    akses_layanan_prioritas,
                    personal_assistant,
                    npwp_perusahaan,
                    batas_tempo_pembayaran
                ) VALUES (
                    '{$pelanggan->getIdPelangganCode()}',
                    '{$pelanggan->getNamaLengkap()}',
                    {$pelanggan->getTotalTransaksiBulanIni()},
                    {$pelanggan->getPoinReward()},
                    '{$pelanggan->getJenisPelanggan()}',
                    '{$pelanggan->getCreatedAt()}',
                    " . $this->getAdditionalFields($pelanggan) . "
                )";
        
        if ($this->koneksi->query($sql)) {
            $id = $this->koneksi->insert_id;
            $pelanggan->setIdPelanggan($id);
            return $id;
        }
        return false;
    }
    
    // Helper untuk mendapatkan field tambahan
    private function getAdditionalFields($pelanggan) {
        $jenis = $pelanggan->getJenisPelanggan();
        
        switch ($jenis) {
            case 'Retail':
                $promo = $pelanggan->getPromoVoucher() ? "'{$pelanggan->getPromoVoucher()}'" : 'NULL';
                return "{$promo}, {$pelanggan->getBatasBeratMax()}, NULL, NULL, NULL, NULL";
            case 'VIP':
                return "NULL, NULL, " . ($pelanggan->getAksesLayananPrioritas() ? '1' : '0') . ", '{$pelanggan->getPersonalAssistant()}', NULL, NULL";
            case 'MitraKorporat':
                return "NULL, NULL, NULL, NULL, '{$pelanggan->getNpwpPerusahaan()}', '{$pelanggan->getBatasTempoPembayaran()}'";
            default:
                return "NULL, NULL, NULL, NULL, NULL, NULL";
        }
    }
    
    // READ - Mendapatkan semua pelanggan
    public function getAllPelanggan() {
        $sql = "SELECT * FROM pelanggan ORDER BY created_at DESC";
        $result = $this->koneksi->query($sql);
        $pelangganList = [];
        
        if ($result && $result->num_rows > 0) {
            while ($data = $result->fetch_assoc()) {
                $pelangganList[] = $this->createPelangganFromData($data);
            }
        }
        
        return $pelangganList;
    }
    
    // READ - Mendapatkan pelanggan by ID
    public function getPelangganById($id) {
        $sql = "SELECT * FROM pelanggan WHERE id_pelanggan = $id";
        $result = $this->koneksi->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $data = $result->fetch_assoc();
            return $this->createPelangganFromData($data);
        }
        
        return null;
    }
    
    // READ - Mendapatkan data mentah pelanggan by ID
    public function getPelangganDataById($id) {
        $sql = "SELECT * FROM pelanggan WHERE id_pelanggan = $id";
        $result = $this->koneksi->query($sql);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    // UPDATE - Mengupdate pelanggan
    public function updatePelanggan($id, $data) {
        $pelanggan = $this->getPelangganById($id);
        if (!$pelanggan) {
            return false;
        }
        
        $sql = "UPDATE pelanggan SET 
                nama_lengkap = '{$data['nama_lengkap']}',
                total_transaksi_bulan_ini = {$data['total_transaksi_bulan_ini']},
                poin_reward = {$data['poin_reward']}";
        
        if ($pelanggan->getJenisPelanggan() == 'Retail') {
            $sql .= ", promo_voucher = " . (isset($data['promo_voucher']) ? "'{$data['promo_voucher']}'" : 'NULL');
            $sql .= ", batas_berat_max = {$data['batas_berat_max']}";
        } elseif ($pelanggan->getJenisPelanggan() == 'VIP') {
            $sql .= ", akses_layanan_prioritas = " . ($data['akses_layanan_prioritas'] ? 1 : 0);
            $sql .= ", personal_assistant = '{$data['personal_assistant']}'";
        } elseif ($pelanggan->getJenisPelanggan() == 'MitraKorporat') {
            $sql .= ", npwp_perusahaan = '{$data['npwp_perusahaan']}'";
            $sql .= ", batas_tempo_pembayaran = '{$data['batas_tempo_pembayaran']}'";
        }
        
        $sql .= " WHERE id_pelanggan = $id";
        
        return $this->koneksi->query($sql);
    }
    
    // DELETE - Menghapus pelanggan
    public function deletePelanggan($id) {
        $sql = "DELETE FROM pelanggan WHERE id_pelanggan = $id";
        return $this->koneksi->query($sql);
    }
    
    // CALCULATE - Menghitung diskon
    public function calculateDiscount($id_pelanggan, $total_biaya) {
        $data = $this->getPelangganDataById($id_pelanggan);
        
        if (!$data) {
            throw new Exception("Pelanggan tidak ditemukan");
        }
        
        $pelanggan = $this->createPelangganFromData($data);
        $diskon = $pelanggan->hitungDiskonPengiriman($total_biaya);
        $total_akhir = $total_biaya - $diskon;
        $benefits = $pelanggan->dapatkanBenefitTambahan();
        
        return [
            'pelanggan' => $pelanggan,
            'diskon' => $diskon,
            'total_akhir' => $total_akhir,
            'benefits' => $benefits
        ];
    }
    
    // Helper untuk membuat objek pelanggan dari data
    private function createPelangganFromData($data) {
        switch ($data['jenis_pelanggan']) {
            case 'Retail':
                $pelanggan = new PelangganRetail(
                    $data['id_pelanggan_code'], 
                    $data['nama_lengkap'], 
                    $data['promo_voucher'] ?? null, 
                    $data['batas_berat_max'] ?? 50
                );
                break;
            case 'VIP':
                $pelanggan = new PelangganVIP(
                    $data['id_pelanggan_code'], 
                    $data['nama_lengkap'], 
                    $data['akses_layanan_prioritas'] ?? true, 
                    $data['personal_assistant'] ?? null
                );
                break;
            case 'MitraKorporat':
                $pelanggan = new MitraKorporat(
                    $data['id_pelanggan_code'], 
                    $data['nama_lengkap'], 
                    $data['npwp_perusahaan'], 
                    $data['batas_tempo_pembayaran'] ?? null
                );
                break;
            default:
                throw new Exception("Jenis pelanggan tidak valid");
        }
        
        $pelanggan->setIdPelanggan($data['id_pelanggan']);
        $pelanggan->setTotalTransaksiBulanIni($data['total_transaksi_bulan_ini']);
        $pelanggan->setPoinReward($data['poin_reward']);
        
        return $pelanggan;
    }
}
?>