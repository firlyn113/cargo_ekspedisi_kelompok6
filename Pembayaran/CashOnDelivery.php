<?php
require_once 'SistemPembayaran.php';

class CashOnDelivery extends SistemPembayaran {
    // Encapsulation: private properties khusus COD
    private $biayaPenangananKurir;
    private $batasMaksimalNominal;

    public function __construct($id_transaksi, $totalTagihan, $biayaPenangananKurir, $batasMaksimalNominal) {
        parent::__construct($id_transaksi, $totalTagihan);
        $this->biayaPenangananKurir = $biayaPenangananKurir;
        $this->batasMaksimalNominal = $batasMaksimalNominal;
    }

    // Polymorphism: implementasi method abstract
    public function prosesValidasiBayar() {
        if ($this->totalTagihan > $this->batasMaksimalNominal) {
            return "❌ Validasi COD gagal: Nominal Rp " . number_format($this->totalTagihan, 0, ',', '.') . 
                   " melebihi batas maksimal Rp " . number_format($this->batasMaksimalNominal, 0, ',', '.');
        }
        return "✅ Validasi COD berhasil! Pembayaran akan dilakukan saat barang sampai.";
    }

    public function hitungBiayaAdmin() {
        return $this->biayaPenangananKurir; // biaya penanganan kurir
    }

    // Getter khusus COD
    public function getBiayaPenangananKurir() {
        return $this->biayaPenangananKurir;
    }
    public function getBatasMaksimalNominal() {
        return $this->batasMaksimalNominal;
    }
}
?>