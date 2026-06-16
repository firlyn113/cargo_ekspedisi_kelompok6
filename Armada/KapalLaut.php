<?php
// Armada/KapalLaut.php
class KapalLaut extends Armada {
    private $nama_dermaga;
    private $jenis_kontainer;
    
    public function __construct($id_armada_code, $kapasitas_maksimal_kg, $status_kelaikan, $biaya_operasional_dasar, $nama_dermaga, $jenis_kontainer) {
        parent::__construct($id_armada_code, $kapasitas_maksimal_kg, $status_kelaikan, $biaya_operasional_dasar);
        $this->nama_dermaga = $nama_dermaga;
        $this->jenis_kontainer = $jenis_kontainer;
        $this->setJenisArmada('Kapal Laut');
    }
    
    public function getNamaDermaga() {
        return $this->nama_dermaga;
    }
    
    public function getJenisKontainer() {
        return $this->jenis_kontainer;
    }
    
    public function hitungBiayaOperasional() {
        $biaya = $this->getBiayaOperasionalDasar();
        $biaya += 150000;
        if ($this->jenis_kontainer == 'Refrigerated') {
            $biaya += 200000;
        } elseif ($this->jenis_kontainer == 'Open Top') {
            $biaya += 100000;
        }
        return $biaya;
    }
    
    public function cekKelayakan() {
        $hasil = [];
        if ($this->getStatusKelaikan() == 'Laik') {
            $hasil[] = "✅ Manifes laut lengkap";
            $hasil[] = "✅ Sistem navigasi berfungsi";
            $hasil[] = "✅ Mesin kapal prima";
            $hasil[] = "✅ Dermaga tujuan: " . $this->nama_dermaga;
            $hasil[] = "✅ Kontainer: " . $this->jenis_kontainer;
            $hasil[] = "🎉 STATUS: LAIK BERLAYAR";
        } else {
            $hasil[] = "❌ Kapal tidak laik laut";
            $hasil[] = "❌ STATUS: TIDAK LAIK OPERASI";
        }
        return $hasil;
    }
    
    public function getDetailSpesifik() {
        return [
            'label' => '⛴️ Kapal Laut',
            'detail1' => 'Dermaga: ' . $this->nama_dermaga,
            'detail2' => 'Kontainer: ' . $this->jenis_kontainer
        ];
    }
}
?>