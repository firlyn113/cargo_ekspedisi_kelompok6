<?php
require_once 'AbstractPelanggan.php';

class PelangganRetail extends AbstractPelanggan {
    // Additional attributes specific to Retail
    private $promo_voucher;
    private $batas_berat_max;
    
    public function __construct($id_pelanggan_code, $nama_lengkap, $promo_voucher = null, $batas_berat_max = 50) {
        parent::__construct($id_pelanggan_code, $nama_lengkap);
        $this->jenis_pelanggan = 'Retail';
        $this->promo_voucher = $promo_voucher;
        $this->batas_berat_max = $batas_berat_max;
    }
    
    // Polymorphism implementation
    public function hitungDiskonPengiriman($total_biaya) {
        $diskon = 0;
        
        // Diskon 5% jika punya voucher
        if ($this->promo_voucher) {
            $diskon = $total_biaya * 0.05;
        }
        
        // Diskon tambahan berdasarkan poin reward (1 poin = 1000 rupiah)
        $diskon_poin = min($this->poin_reward * 1000, $total_biaya * 0.1);
        $diskon += $diskon_poin;
        
        return min($diskon, $total_biaya * 0.15); // Maksimal diskon 15%
    }
    
    // Polymorphism implementation
    public function dapatkanBenefitTambahan() {
        $benefits = [];
        
        // Benefit retail: voucher dan poin
        if ($this->promo_voucher) {
            $benefits[] = "Voucher promo: " . $this->promo_voucher;
        }
        
        if ($this->poin_reward >= 10) {
            $benefits[] = "Dapat menukarkan " . floor($this->poin_reward / 10) . " voucher diskon 5%";
        }
        
        $benefits[] = "Akumulasi poin: " . $this->poin_reward . " poin";
        
        return $benefits;
    }
    
    // Getter & Setter untuk atribut tambahan
    public function getPromoVoucher() {
        return $this->promo_voucher;
    }
    
    public function setPromoVoucher($voucher) {
        $this->promo_voucher = $voucher;
    }
    
    public function getBatasBeratMax() {
        return $this->batas_berat_max;
    }
    
    // Override save method untuk menyimpan atribut tambahan
    public function saveToDatabase($koneksi) {
        parent::saveToDatabase($koneksi);
        
        $sql = "UPDATE pelanggan SET promo_voucher = '{$this->promo_voucher}', batas_berat_max = {$this->batas_berat_max} 
                WHERE id_pelanggan = {$this->id_pelanggan}";
        
        return $koneksi->query($sql);
    }
}
?>
