<?php
/**
 * File: KargoPecahBelah.php
 * Class: KargoPecahBelah extends Kargo
 * Implementasi untuk kargo pecah belah/fragile
 */

require_once 'Kargo.php';

class KargoPecahBelah extends Kargo {
    // Atribut tambahan
    private $ketebalan_bubble_wrap; // dalam cm
    private $biaya_asuransi_wajib;
    private $biaya_packing_khusus = 25000; // Biaya packing tambahan
    
    public function __construct($id_resi, $pengirim, $kota_tujuan, $berat_barang, $tarif_dasar_per_kg, $ketebalan_bubble_wrap, $biaya_asuransi_wajib = null) {
        parent::__construct($id_resi, $pengirim, $kota_tujuan, $berat_barang, $tarif_dasar_per_kg);
        $this->ketebalan_bubble_wrap = $ketebalan_bubble_wrap;
        $this->biaya_asuransi_wajib = $biaya_asuransi_wajib ?? ($this->getBiayaDasar() * 0.10); // Asuransi 10%
        $this->jenis_kargo = 'PecahBelah';
    }
    
    // Getter & Setter
    public function getKetebalanBubbleWrap() { return $this->ketebalan_bubble_wrap; }
    public function getBiayaAsuransiWajib() { return $this->biaya_asuransi_wajib; }
    
    // Implementasi Polimorfisme - hitungTarifPengiriman
    public function hitungTarifPengiriman() {
        $biayaDasar = $this->getBiayaDasar();
        
        // Pecah belah: Biaya tambahan berdasarkan ketebalan bubble wrap
        $surchargePacking = 0;
        
        if($this->ketebalan_bubble_wrap < 1) {
            $surchargePacking = $biayaDasar * 0.30; // 30% untuk packing kurang
        } elseif($this->ketebalan_bubble_wrap < 3) {
            $surchargePacking = $biayaDasar * 0.15; // 15% untuk packing standar
        } else {
            $surchargePacking = $biayaDasar * 0.05; // 5% untuk packing baik
        }
        
        $total = $biayaDasar + $surchargePacking + $this->biaya_asuransi_wajib + $this->biaya_packing_khusus;
        
        return round($total, 2);
    }
    
    // Implementasi Polimorfisme - getInfoKargo
    public function getInfoKargo() {
        return [
            'id_resi' => $this->getIdResi(),
            'pengirim' => $this->getPengirim(),
            'kota_tujuan' => $this->getKotaTujuan(),
            'berat_barang' => $this->getBeratBarang(),
            'jenis_kargo' => 'Pecah Belah',
            'ketebalan_bubble_wrap' => $this->ketebalan_bubble_wrap,
            'biaya_asuransi_wajib' => $this->biaya_asuransi_wajib,
            'total_tarif' => $this->hitungTarifPengiriman()
        ];
    }
    
    // Method khusus untuk rekomendasi packing
    public function rekomendasiPacking() {
        if($this->ketebalan_bubble_wrap < 1) {
            return "PERINGATAN: Ketebalan bubble wrap kurang dari 1 cm! Risiko kerusakan tinggi. Disarankan menambah bubble wrap.";
        } elseif($this->ketebalan_bubble_wrap < 2) {
            return "Ketebalan bubble wrap minimal. Disarankan menambah bubble wrap untuk keamanan ekstra.";
        } else {
            return "Packing sudah baik, aman untuk pengiriman.";
        }
    }
}
?>