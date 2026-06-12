<?php
// armada/TrukDarat.php
require_once 'Armada.php';

class TrukDarat extends Armada {
    // Atribut tambahan
    private $jumlah_roda;
    private $rute_tol;
    
    public function __construct($conn) {
        parent::__construct($conn);
        $this->setJenisArmada('TrukDarat');
    }
    
    // Setter & Getter
    public function setJumlahRoda($roda) {
        $this->jumlah_roda = $roda;
    }
    
    public function getJumlahRoda() {
        return $this->jumlah_roda;
    }
    
    public function setRuteTol($rute) {
        $this->rute_tol = $rute;
    }
    
    public function getRuteTol() {
        return $this->rute_tol;
    }
    
    // IMPLEMENTASI POLYMORPHISM - Override method abstrak
    public function hitungBiayaOperasional() {
        $biaya_bbm = $this->getBiayaOperasionalDasar();
        $biaya_tol = 50000; // Estimasi biaya tol
        
        if (!empty($this->rute_tol)) {
            $jumlah_tol = substr_count($this->rute_tol, ',') + 1;
            $biaya_tol = $jumlah_tol * 75000;
        }
        
        return $biaya_bbm + $biaya_tol;
    }
    
    // IMPLEMENTASI POLYMORPHISM - Override method abstrak
    public function cekKelayakanJalan() {
        $hasil_cek = [];
        
        // Cek mesin darat
        if ($this->getStatusKelaikan() == 'Laik') {
            $hasil_cek[] = "✅ Mesin darat dalam kondisi baik";
            $hasil_cek[] = "✅ Sistem rem berfungsi normal";
        } else {
            $hasil_cek[] = "❌ Mesin darat perlu perbaikan";
            return $hasil_cek;
        }
        
        // Cek kapasitas
        if ($this->getKapasitasMaksimal() > 0) {
            $hasil_cek[] = "✅ Kapasitas muatan: " . $this->getKapasitasMaksimal() . " kg";
        }
        
        // Cek rute tol
        if (!empty($this->rute_tol)) {
            $hasil_cek[] = "✅ Rute tol tersedia: " . $this->rute_tol;
        } else {
            $hasil_cek[] = "⚠️ Tidak ada rute tol yang ditentukan";
        }
        
        $hasil_cek[] = "✅ Truk darat dinyatakan LAIK beroperasi";
        return $hasil_cek;
    }
}
?>