<?php
// Armada/TrukDarat.php
class TrukDarat extends Armada {
    private $jumlah_roda;
    private $rute_tol;
    
    public function __construct($id_armada_code, $kapasitas_maksimal_kg, $status_kelaikan, $biaya_operasional_dasar, $jumlah_roda, $rute_tol) {
        parent::__construct($id_armada_code, $kapasitas_maksimal_kg, $status_kelaikan, $biaya_operasional_dasar);
        $this->jumlah_roda = $jumlah_roda;
        $this->rute_tol = $rute_tol;
        $this->setJenisArmada('Truk Darat');
    }
    
    public function getJumlahRoda() {
        return $this->jumlah_roda;
    }
    
    public function getRuteTol() {
        return $this->rute_tol;
    }
    
    public function hitungBiayaOperasional() {
        $biaya = $this->getBiayaOperasionalDasar();
        if (!empty($this->rute_tol)) {
            $jumlah_tol = substr_count($this->rute_tol, ',') + 1;
            $biaya += $jumlah_tol * 75000;
        } else {
            $biaya += 50000;
        }
        if ($this->jumlah_roda >= 8) {
            $biaya += 100000;
        }
        return $biaya;
    }
    
    public function cekKelayakan() {
        $hasil = [];
        if ($this->getStatusKelaikan() == 'Laik') {
            $hasil[] = "✅ Mesin dalam kondisi baik";
            $hasil[] = "✅ Rem berfungsi normal";
            $hasil[] = "✅ Ban dalam kondisi baik";
            $hasil[] = "✅ Konfigurasi roda sesuai (" . $this->jumlah_roda . " roda)";
            if (!empty($this->rute_tol)) {
                $hasil[] = "✅ Rute tol: " . $this->rute_tol;
            }
            $hasil[] = "🎉 STATUS: LAIK BEROPERASI";
        } else {
            $hasil[] = "❌ Mesin bermasalah";
            $hasil[] = "❌ STATUS: TIDAK LAIK OPERASI";
        }
        return $hasil;
    }
    
    public function getDetailSpesifik() {
        return [
            'label' => '🚛 Truk Darat',
            'detail1' => 'Jumlah Roda: ' . $this->jumlah_roda,
            'detail2' => 'Rute Tol: ' . ($this->rute_tol ?: 'Tidak ada rute tol')
        ];
    }
}
?>