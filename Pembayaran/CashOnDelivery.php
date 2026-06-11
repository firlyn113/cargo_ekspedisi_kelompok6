<?php
require_once 'SistemPembayaran.php';

class CashOnDelivery extends SistemPembayaran {
    private $biayaPenangananKurir;
    private $batasMaksimalNominal;

    public function __construct($biayaPenangananKurir, $batasMaksimalNominal) {
        $this->biayaPenangananKurir = $biayaPenangananKurir;
        $this->batasMaksimalNominal = $batasMaksimalNominal;
    }

    public function prosesValidasiBayar() {
        if ($this->totalTagihan > $this->batasMaksimalNominal) {
            return "Validasi COD gagal: Nominal melebihi batas maksimal";
        }
        return "Validasi COD berhasil (pembayaran saat barang sampai)";
    }

    public function hitungBiayaAdmin() {
        return $this->biayaPenangananKurir;
    }
}
?>