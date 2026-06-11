<?php
/**
 * File: KargoReguler.php
 * Class: KargoReguler extends Kargo
 * Implementasi untuk kargo reguler biasa
 */

require_once 'Kargo.php';

class KargoReguler extends Kargo {
    // Atribut tambahan
    private $jenis_paket; // Koli/Dus
    private $estimasi_hari;
    
    public function __construct($id_resi, $pengirim, $kota_tujuan, $berat_barang, $tarif_dasar_per_kg, $jenis_paket, $estimasi_hari) {
        parent::__construct($id_resi, $pengirim, $kota_tujuan, $berat_barang, $tarif_dasar_per_kg);
        $this->jenis_paket = $jenis_paket;
        $this->estimasi_hari = $estimasi_hari;
        $this->jenis_kargo = 'Reguler';
    }
    
    // Getter & Setter
    public function getJenisPaket() { return $this->jenis_paket; }
    public function getEstimasiHari() { return $this->estimasi_hari; }
    
    public function setJenisPaket($jenis_paket) { $this->jenis_paket = $jenis_paket; }
    public function setEstimasiHari($estimasi_hari) { $this->estimasi_hari = $estimasi_hari; }
    
    // Implementasi Polimorfisme - hitungTarifPengiriman
    public function hitungTarifPengiriman() {
        $biayaDasar = $this->getBiayaDasar();
        
        // Reguler: Tidak ada biaya tambahan khusus
        // Diskon untuk berat di atas 100kg
        if($this->getBeratBarang() > 100) {
            $diskon = 0.05; // 5% diskon
            $total = $biayaDasar * (1 - $diskon);
        } else {
            $total = $biayaDasar;
        }
        
        return round($total, 2);
    }
    
    // Implementasi Polimorfisme - getInfoKargo
    public function getInfoKargo() {
        return [
            'id_resi' => $this->getIdResi(),
            'pengirim' => $this->getPengirim(),
            'kota_tujuan' => $this->getKotaTujuan(),
            'berat_barang' => $this->getBeratBarang(),
            'jenis_kargo' => 'Reguler',
            'jenis_paket' => $this->jenis_paket,
            'estimasi_hari' => $this->estimasi_hari,
            'total_tarif' => $this->hitungTarifPengiriman()
        ];
    }
    
    // Method khusus untuk mendapatkan data lengkap
    public function getDataLengkap() {
        return array_merge($this->getInfoKargo(), [
            'tipe_layanan' => 'Standar',
            'jaminan' => 'Tidak ada asuransi'
        ]);
    }
}
?>