<?php
// armada/KapalLaut.php
require_once 'Armada.php';

class KapalLaut extends Armada {
    private $nama_dermaga;
    private $jenis_kontainer;
    
    public function __construct($conn) {
        parent::__construct($conn);
        $this->setJenisArmada('KapalLaut');
    }
    
    public function setNamaDermaga($dermaga) {
        $this->nama_dermaga = $dermaga;
    }
    
    public function getNamaDermaga() {
        return $this->nama_dermaga;
    }
    
    public function setJenisKontainer($kontainer) {
        $this->jenis_kontainer = $kontainer;
    }
    
    public function getJenisKontainer() {
        return $this->jenis_kontainer;
    }
    
    public function hitungBiayaOperasional() {
        $biaya_bbm = $this->getBiayaOperasionalDasar();
        $biaya_sandar = 150000; // Biaya sandar di dermaga
        
        // Tambahan biaya berdasarkan jenis kontainer
        $biaya_kontainer = 0;
        if ($this->jenis_kontainer == 'Refrigerated') {
            $biaya_kontainer = 100000;
        } elseif ($this->jenis_kontainer == 'Open Top') {
            $biaya_kontainer = 50000;
        }
        
        return $biaya_bbm + $biaya_sandar + $biaya_kontainer;
    }
    
    public function cekKelayakanJalan() {
        $hasil_cek = [];
        
        if ($this->getStatusKelaikan() == 'Laik') {
            $hasil_cek[] = "✅ Manifes laut lengkap";
            $hasil_cek[] = "✅ Sistem navigasi berfungsi";
        } else {
            $hasil_cek[] = "❌ Kapal tidak laik laut";
            return $hasil_cek;
        }
        
        if (!empty($this->nama_dermaga)) {
            $hasil_cek[] = "✅ Dermaga tujuan: " . $this->nama_dermaga;
        }
        
        if (!empty($this->jenis_kontainer)) {
            $hasil_cek[] = "✅ Jenis kontainer: " . $this->jenis_kontainer;
        }
        
        $hasil_cek[] = "✅ Kapal laut dinyatakan LAIK berlayar";
        return $hasil_cek;
    }
}
?>