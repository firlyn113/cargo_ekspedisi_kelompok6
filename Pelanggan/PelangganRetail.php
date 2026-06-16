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
            $benefits[] = "🎫 Voucher promo: " . $this->promo_voucher;
        }
        
        if ($this->poin_reward >= 10) {
            $benefits[] = "🔄 Dapat menukarkan " . floor($this->poin_reward / 10) . " voucher diskon 5%";
        }
        
        $benefits[] = "⭐ Akumulasi poin: " . $this->poin_reward . " poin";
        $benefits[] = "📦 Batas berat maksimal: " . $this->batas_berat_max . " kg";
        
        return $benefits;
    }
    
    // Getter & Setter untuk atribut tambahan (Encapsulation)
    public function getPromoVoucher() {
        return $this->promo_voucher;
    }
    
    public function setPromoVoucher($voucher) {
        $this->promo_voucher = $voucher;
    }
    
    public function getBatasBeratMax() {
        return $this->batas_berat_max;
    }
    
    public function setBatasBeratMax($berat) {
        $this->batas_berat_max = $berat;
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
            'promo_voucher' => $this->promo_voucher,
            'batas_berat_max' => $this->batas_berat_max
        ];
    }
    
    // Method khusus untuk retail - cek voucher
    public function cekVoucherValid() {
        if ($this->promo_voucher) {
            return "✅ Voucher '{$this->promo_voucher}' aktif dan valid!";
        }
        return "❌ Tidak ada voucher aktif. Gunakan kode 'WELCOME10' untuk diskon 10%!";
    }
    
    // Method khusus untuk retail - cek batas berat
    public function cekBatasBerat($berat_barang) {
        if ($berat_barang <= $this->batas_berat_max) {
            return "✅ Berat barang {$berat_barang} kg masih dalam batas maksimal ({$this->batas_berat_max} kg)";
        }
        return "⚠️ Berat barang {$berat_barang} kg MELEBIHI batas maksimal ({$this->batas_berat_max} kg)";
    }
    
    // Method untuk menghitung poin yang bisa ditukar
    public function hitungPoinBisaDitukar() {
        return floor($this->poin_reward / 10);
    }
    
    // Method untuk menukar poin menjadi voucher
    public function tukarPoinKeVoucher($jumlah_voucher) {
        $poin_dibutuhkan = $jumlah_voucher * 10;
        
        if ($this->poin_reward >= $poin_dibutuhkan) {
            $this->poin_reward -= $poin_dibutuhkan;
            $voucher_baru = "VOUCHER_" . date('Ymd') . "_" . rand(1000, 9999);
            $this->promo_voucher = $voucher_baru;
            
            return [
                'status' => 'success',
                'message' => "✅ Berhasil menukar {$jumlah_voucher} voucher!",
                'voucher_baru' => $voucher_baru,
                'sisa_poin' => $this->poin_reward
            ];
        }
        
        return [
            'status' => 'failed',
            'message' => "❌ Poin tidak mencukupi! Butuh {$poin_dibutuhkan} poin, tersisa {$this->poin_reward} poin",
            'poin_dibutuhkan' => $poin_dibutuhkan,
            'poin_tersedia' => $this->poin_reward
        ];
    }
}
?>