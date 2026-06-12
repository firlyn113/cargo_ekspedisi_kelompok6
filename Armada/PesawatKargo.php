<?php
// armada/PesawatKargo.php
require_once 'Armada.php';

class PesawatKargo extends Armada {
    private $batas_ketinggian;
    private $izin_penerbangan_khusus;
    
    public function __construct($conn) {
        parent::__construct($conn);
        $this->setJenisArmada('PesawatKargo');
    }
    
    public function setBatasKetinggian($ketinggian) {
        $this->batas_ketinggian = $ketinggian;
    }
    
    public function getBatasKetinggian() {
        return $this->batas_ketinggian;
    }
    
    public function setIzinPenerbangan($izin) {
        $this->izin_penerbangan_khusus = $izin;
    }
    
    public function getIzinPenerbangan() {
        return $this->izin_penerbangan_khusus;
    }
    
    public function hitungBiayaOperasional() {
        $biaya_avtur = $this->getBiayaOperasionalDasar();
        $biaya_bandara = 250000;
        
        // Tambahan biaya berdasarkan izin khusus
        if ($this->izin_penerbangan_khusus == 'Cargo Malam') {
            $biaya_bandara += 100000;
        }
        
        return $biaya_avtur + $biaya_bandara;
    }
    
    public function cekKelayakanJalan() {
        $hasil_cek = [];
        
        if ($this->getStatusKelaikan() == 'Laik') {
            $hasil_cek[] = "✅ Izin navigasi udara valid";
            $hasil_cek[] = "✅ Pesawat siap terbang";
        } else {
            $hasil_cek[] = "❌ Pesawat tidak laik terbang";
            return $hasil_cek;
        }
        
        if ($this->batas_ketinggian > 0) {
            $hasil_cek[] = "✅ Batas ketinggian: " . $this->batas_ketinggian . " meter";
        }
        
        if (!empty($this->izin_penerbangan_khusus)) {
            $hasil_cek[] = "✅ Izin penerbangan: " . $this->izin_penerbangan_khusus;
        }
        
        $hasil_cek[] = "✅ Pesawat kargo dinyatakan LAIK terbang";
        return $hasil_cek;
    }
}
?>