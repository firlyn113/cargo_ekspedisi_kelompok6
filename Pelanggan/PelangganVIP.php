<?php
require_once 'AbstractPelanggan.php';

class PelangganVIP extends AbstractPelanggan {
    // Additional attributes specific to VIP
    private $akses_layanan_prioritas;
    private $personal_assistant;
    
    public function __construct($id_pelanggan_code, $nama_lengkap, $akses_layanan_prioritas = true, $personal_assistant = null) {
        parent::__construct($id_pelanggan_code, $nama_lengkap);
        $this->jenis_pelanggan = 'VIP';
        $this->akses_layanan_prioritas = $akses_layanan_prioritas;
        $this->personal_assistant = $personal_assistant;
        
        // VIP mendapatkan bonus poin awal
        $this->poin_reward = 100;
    }
    
    // Polymorphism implementation - Diskon lebih besar untuk VIP
    public function hitungDiskonPengiriman($total_biaya) {
        $diskon = 0;
        
        // Diskon 15% untuk VIP
        $diskon = $total_biaya * 0.15;
        
        // Diskon tambahan berdasarkan poin (1 poin = 2000 rupiah untuk VIP)
        $diskon_poin = min($this->poin_reward * 2000, $total_biaya * 0.2);
        $diskon += $diskon_poin;
        
        return min($diskon, $total_biaya * 0.35); // Maksimal diskon 35% untuk VIP
    }
    
    // Polymorphism implementation - Benefit lebih banyak untuk VIP
    public function dapatkanBenefitTambahan() {
        $benefits = [];
        
        // Benefit VIP
        $benefits[] = "✅ Akses layanan prioritas (tanpa antri)";
        
        if ($this->personal_assistant) {
            $benefits[] = "👤 Personal Assistant: " . $this->personal_assistant;
        }
        
        $benefits[] = "🎁 Free packing kayu untuk semua pengiriman";
        $benefits[] = "📊 Laporan pengiriman bulanan analitik";
        $benefits[] = "🚚 Pickup gratis (minimal 2kg)";
        $benefits[] = "⭐ Poin reward x2 (2 poin per 100k)";
        
        return $benefits;
    }
    
    // Getter & Setter
    public function getAksesLayananPrioritas() {
        return $this->akses_layanan_prioritas;
    }
    
    public function setAksesLayananPrioritas($status) {
        $this->akses_layanan_prioritas = $status;
    }
    
    public function getPersonalAssistant() {
        return $this->personal_assistant;
    }
    
    public function setPersonalAssistant($assistant) {
        $this->personal_assistant = $assistant;
    }
    
    // Override tambah transaksi dengan perolehan poin double
    public function tambahTransaksi($nominal) {
        $this->total_transaksi_bulan_ini += $nominal;
        $this->poin_reward += floor($nominal / 100000) * 2; // 2 poin per 100k untuk VIP
    }
    
    // Override save method
    public function saveToDatabase($koneksi) {
        parent::saveToDatabase($koneksi);
        
        $sql = "UPDATE pelanggan SET akses_layanan_prioritas = " . ($this->akses_layanan_prioritas ? 1 : 0) . 
                ", personal_assistant = '{$this->personal_assistant}' WHERE id_pelanggan = {$this->id_pelanggan}";
        
        return $koneksi->query($sql);
    }
}
?>
