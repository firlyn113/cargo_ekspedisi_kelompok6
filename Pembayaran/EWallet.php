<?php
require_once 'SistemPembayaran.php';

class EWallet extends SistemPembayaran {
    private $nomorHP;
    private $biayaLayananAplikasi;

    public function __construct($nomorHP, $biayaLayananAplikasi) {
        $this->nomorHP = $nomorHP;
        $this->biayaLayananAplikasi = $biayaLayananAplikasi;
    }

    public function prosesValidasiBayar() {
        return "Validasi E-Wallet: OTP dikirim ke {$this->nomorHP}";
    }

    public function hitungBiayaAdmin() {
        return $this->biayaLayananAplikasi;
    }
}
?>