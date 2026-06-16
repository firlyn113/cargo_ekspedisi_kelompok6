<?php
// Armada/PesawatKargo.php
class PesawatKargo extends Armada {
    private $batas_ketinggian;
    private $izin_penerbangan_khusus;
    
    public function __construct($id_armada_code, $kapasitas_maksimal_kg, $status_kelaikan, $biaya_operasional_dasar, $batas_ketinggian, $izin_penerbangan_khusus) {
        parent::__construct($id_armada_code, $kapasitas_maksimal_kg, $status_kelaikan, $biaya_operasional_dasar);
        $this->batas_ketinggian = $batas_ketinggian;
        $this->izin_penerbangan_khusus = $izin_penerbangan_khusus;
        $this->setJenisArmada('Pesawat Kargo');
    }
    
    public function getBatasKetinggian() {
        return $this->batas_ketinggian;
    }
    
    public function getIzinPenerbangan() {
        return $this->izin_penerbangan_khusus;
    }
    
    public function hitungBiayaOperasional() {
        $biaya = $this->getBiayaOperasionalDasar();
        $biaya += 250000;
        if ($this->izin_penerbangan_khusus == 'Cargo Malam') {
            $biaya += 150000;
        } elseif ($this->izin_penerbangan_khusus == 'Internasional') {
            $biaya += 500000;
        }
        return $biaya;
    }
    
    public function cekKelayakan() {
        $hasil = [];
        if ($this->getStatusKelaikan() == 'Laik') {
            $hasil[] = "✅ Izin navigasi udara valid";
            $hasil[] = "✅ Mesin pesawat siap";
            $hasil[] = "✅ Sistem hidrolik normal";
            $hasil[] = "✅ Batas ketinggian: " . $this->batas_ketinggian . " m";
            if (!empty($this->izin_penerbangan_khusus)) {
                $hasil[] = "✅ Izin khusus: " . $this->izin_penerbangan_khusus;
            }
            $hasil[] = "🎉 STATUS: LAIK TERBANG";
        } else {
            $hasil[] = "❌ Pesawat tidak laik terbang";
            $hasil[] = "❌ STATUS: TIDAK LAIK OPERASI";
        }
        return $hasil;
    }
    
    public function getDetailSpesifik() {
        return [
            'label' => '✈️ Pesawat Kargo',
            'detail1' => 'Batas Ketinggian: ' . $this->batas_ketinggian . ' m',
            'detail2' => 'Izin Penerbangan: ' . ($this->izin_penerbangan_khusus ?: 'Reguler')
        ];
    }
}
?>