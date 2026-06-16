<?php
require_once 'SistemPembayaran.php';

class TransferBank extends SistemPembayaran {
    // Encapsulation: private properties khusus Transfer Bank
    private $kodeVirtualAccount;
    private $namaBank;

    public function __construct($id_transaksi, $totalTagihan, $kodeVirtualAccount, $namaBank) {
        parent::__construct($id_transaksi, $totalTagihan);
        $this->kodeVirtualAccount = $kodeVirtualAccount;
        $this->namaBank = $namaBank;
    }

    // Polymorphism: implementasi method abstract
    public function prosesValidasiBayar() {
        return "✅ Validasi Transfer Bank: Ping API Bank {$this->namaBank} untuk VA {$this->kodeVirtualAccount} berhasil!";
    }

    public function hitungBiayaAdmin() {
        return 2500; // biaya admin transfer bank flat
    }

    // Getter khusus Transfer Bank
    public function getKodeVirtualAccount() {
        return $this->kodeVirtualAccount;
    }
    public function getNamaBank() {
        return $this->namaBank;
    }
}
?>