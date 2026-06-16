<?php
require_once 'AbstractPelanggan.php';

class MitraKorporat extends AbstractPelanggan {
    // Additional attributes specific to Mitra Korporat
    private $npwp_perusahaan;
    private $batas_tempo_pembayaran;
    
    public function __construct($id_pelanggan_code, $nama_lengkap, $npwp_perusahaan, $batas_tempo_pembayaran = null) {
        parent::__construct($id_pelanggan_code, $nama_lengkap);
        $this->jenis_pelanggan = 'MitraKorporat';
        $this->npwp_perusahaan = $npwp_perusahaan;
        $this->batas_tempo_pembayaran = $batas_tempo_pembayaran ?: date('Y-m-d', strtotime('+30 days'));
        
        // Mitra korporat mendapatkan poin awal besar
        $this->poin_reward = 500;
    }
    
    // Polymorphism implementation - Diskon volume untuk korporat
    public function hitungDiskonPengiriman($total_biaya) {
        $diskon = 0;
        
        // Diskon 20% untuk korporat
        $diskon = $total_biaya * 0.20;
        
        // Diskon tambahan jika total transaksi bulan ini > 10 juta
        if ($this->total_transaksi_bulan_ini > 10000000) {
            $diskon += $total_biaya * 0.05; // Tambahan 5%
        }
        
        // Diskon poin (1 poin = 1500 rupiah)
        $diskon_poin = min($this->poin_reward * 1500, $total_biaya * 0.15);
        $diskon += $diskon_poin;
        
        return min($diskon, $total_biaya * 0.40); // Maksimal diskon 40% untuk korporat
    }
    
    // Polymorphism implementation - Benefit untuk korporat
    public function dapatkanBenefitTambahan() {
        $benefits = [];
        
        // Benefit Mitra Korporat
        $benefits[] = "🏢 NPWP Perusahaan: " . $this->npwp_perusahaan;
        $benefits[] = "📅 Batas tempo pembayaran: " . date('d-m-Y', strtotime($this->batas_tempo_pembayaran));
        $benefits[] = "📈 Diskon volume (5% untuk transaksi > 10jt/bulan)";
        $benefits[] = "📊 Laporan keuangan lengkap per periode";
        $benefits[] = "🛡️ Asuransi barang gratis untuk 5 pengiriman pertama";
        $benefits[] = "🎯 Account Manager khusus perusahaan";
        
        return $benefits;
    }
    
    // Getter & Setter (Encapsulation)
    public function getNpwpPerusahaan() {
        return $this->npwp_perusahaan;
    }
    
    public function setNpwpPerusahaan($npwp) {
        $this->npwp_perusahaan = $npwp;
    }
    
    public function getBatasTempoPembayaran() {
        return $this->batas_tempo_pembayaran;
    }
    
    public function setBatasTempoPembayaran($tanggal) {
        $this->batas_tempo_pembayaran = $tanggal;
    }
    
    // Method untuk mendapatkan data lengkap sebagai array (untuk keperluan CRUD)
    public function toArray() {
        return [
            'id_pelanggan' => $this->id_pelanggan,
            'id_pelanggan_code' => $this->id_pelanggan_code,
            'nama_lengkap' => $this->nama_lengkap,
            'jenis_pelanggan' => $this->jenis_pelanggan,
            'total_transaksi_bulan_ini' => $this->total_transaksi_bulan_ini,
            'poin_reward' => $this->poin_reward,
            'created_at' => $this->created_at,
            'npwp_perusahaan' => $this->npwp_perusahaan,
            'batas_tempo_pembayaran' => $this->batas_tempo_pembayaran
        ];
    }
    
    // Method khusus untuk korporat - cek tagihan jatuh tempo
    public function cekTagihanJatuhTempo() {
        $hari_tersisa = (strtotime($this->batas_tempo_pembayaran) - time()) / (60 * 60 * 24);
        
        if ($hari_tersisa <= 0) {
            return "⚠️ Tagihan sudah jatuh tempo! Segera lakukan pembayaran.";
        } elseif ($hari_tersisa <= 7) {
            return "⚠️ Peringatan: Tagihan akan jatuh tempo dalam " . ceil($hari_tersisa) . " hari.";
        }
        
        return "✅ Tagihan masih dalam periode tempo. Sisa " . ceil($hari_tersisa) . " hari.";
    }
    
    // Method untuk menampilkan info lengkap korporat
    public function getInfoKorporat() {
        return [
            'npwp' => $this->npwp_perusahaan,
            'tempo_pembayaran' => $this->batas_tempo_pembayaran,
            'status_tagihan' => $this->cekTagihanJatuhTempo(),
            'total_transaksi' => $this->total_transaksi_bulan_ini,
            'poin' => $this->poin_reward
        ];
    }
}
?>