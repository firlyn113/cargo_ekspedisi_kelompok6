<?php
require_once 'SistemPembayaran.php';

class EWallet extends SistemPembayaran {
    // Encapsulation: private properties khusus E-Wallet
    private $nomorHP;
    private $biayaLayananAplikasi;

    public function __construct($id_transaksi, $totalTagihan, $nomorHP, $biayaLayananAplikasi) {
        parent::__construct($id_transaksi, $totalTagihan);
        $this->nomorHP = $nomorHP;
        $this->biayaLayananAplikasi = $biayaLayananAplikasi;
    }

    // Polymorphism: implementasi method abstract
    public function prosesValidasiBayar() {
        return "✅ Validasi E-Wallet: OTP dikirim ke {$this->nomorHP}. Silakan verifikasi!";
    }

    public function hitungBiayaAdmin() {
        return $this->biayaLayananAplikasi;
    }

    // Getter khusus E-Wallet
    public function getNomorHP() {
        return $this->nomorHP;
    }
    public function getBiayaLayananAplikasi() {
        return $this->biayaLayananAplikasi;
    }
}
?>