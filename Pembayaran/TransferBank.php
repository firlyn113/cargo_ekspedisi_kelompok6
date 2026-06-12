<?php
require_once 'SistemPembayaran.php';

class TransferBank extends SistemPembayaran {
    private $kodeVirtualAccount;
    private $namaBank;

    public function __construct($kodeVirtualAccount, $namaBank) {
        $this->kodeVirtualAccount = $kodeVirtualAccount;
        $this->namaBank = $namaBank;
    }

    public function prosesValidasiBayar() {
        return "Validasi Transfer Bank: Ping API Bank {$this->namaBank} untuk VA {$this->kodeVirtualAccount}";
    }

    public function hitungBiayaAdmin() {
        return 2500; // biaya admin transfer
    }
}
?>