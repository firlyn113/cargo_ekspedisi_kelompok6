<?php
/**
 * File: KargoBahanKimia.php
 * Class: KargoBahanKimia extends Kargo
 * Implementasi untuk kargo bahan kimia berbahaya
 */

require_once 'Kargo.php';

class KargoBahanKimia extends Kargo {
    // Atribut tambahan
    private $tingkat_bahaya; // Class 1-9
    private $jenis_sertifikasi;
    private $biaya_penanganan_khusus = 50000; // Biaya tetap
    
    public function __construct($id_resi, $pengirim, $kota_tujuan, $berat_barang, $tarif_dasar_per_kg, $tingkat_bahaya, $jenis_sertifikasi) {
        parent::__construct($id_resi, $pengirim, $kota_tujuan, $berat_barang, $tarif_dasar_per_kg);
        $this->tingkat_bahaya = $tingkat_bahaya;
        $this->jenis_sertifikasi = $jenis_sertifikasi;
        $this->jenis_kargo = 'BahanKimia';
    }
    
    // Getter & Setter
    public function getTingkatBahaya() { return $this->tingkat_bahaya; }
    public function getJenisSertifikasi() { return $this->jenis_sertifikasi; }
    
    // Implementasi Polimorfisme - hitungTarifPengiriman
    public function hitungTarifPengiriman() {
        $biayaDasar = $this->getBiayaDasar();
        
        // Bahan kimia: Biaya tambahan berdasarkan tingkat bahaya
        $surchargeBahaya = 0;
        $tingkat = intval($this->tingkat_bahaya);
        
        if($tingkat <= 3) {
            $surchargeBahaya = $biayaDasar * 0.20; // 20% untuk bahaya tinggi
        } elseif($tingkat <= 6) {
            $surchargeBahaya = $biayaDasar * 0.15; // 15% untuk bahaya sedang
        } else {
            $surchargeBahaya = $biayaDasar * 0.10; // 10% untuk bahaya rendah
        }
        
        $total = $biayaDasar + $surchargeBahaya + $this->biaya_penanganan_khusus;
        
        return round($total, 2);
    }
    
    // Implementasi Polimorfisme - getInfoKargo
    public function getInfoKargo() {
        return [
            'id_resi' => $this->getIdResi(),
            'pengirim' => $this->getPengirim(),
            'kota_tujuan' => $this->getKotaTujuan(),
            'berat_barang' => $this->getBeratBarang(),
            'jenis_kargo' => 'Bahan Kimia',
            'tingkat_bahaya' => $this->tingkat_bahaya,
            'jenis_sertifikasi' => $this->jenis_sertifikasi,
            'total_tarif' => $this->hitungTarifPengiriman()
        ];
    }
    
    // Method khusus untuk validasi keamanan
    public function validasiKeamanan() {
        $tingkat = intval($this->tingkat_bahaya);
        if($tingkat < 1 || $tingkat > 9) {
            return false;
        }
        if(empty($this->jenis_sertifikasi)) {
            return false;
        }
        return true;
    }
}
?>