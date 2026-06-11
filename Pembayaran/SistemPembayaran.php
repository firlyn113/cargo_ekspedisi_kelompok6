<?php
abstract class SistemPembayaran {
    protected $id_transaksi;
    protected $totalTagihan;
    protected $statusLunas;
    protected $waktuPembayaran;

    // Enkapsulasi
    public function setIdTransaksi($id_transaksi) {
        $this->id_transaksi = $id_transaksi;
    }
    public function getIdTransaksi() {
        return $this->id_transaksi;
    }

    public function setTotalTagihan($totalTagihan) {
        $this->totalTagihan = $totalTagihan;
    }
    public function getTotalTagihan() {
        return $this->totalTagihan;
    }

    public function setStatusLunas($statusLunas) {
        $this->statusLunas = $statusLunas;
    }
    public function getStatusLunas() {
        return $this->statusLunas;
    }

    public function setWaktuPembayaran($waktuPembayaran) {
        $this->waktuPembayaran = $waktuPembayaran;
    }
    public function getWaktuPembayaran() {
        return $this->waktuPembayaran;
    }

    // Polimorfisme (2 abstract methods)
    abstract public function prosesValidasiBayar();
    abstract public function hitungBiayaAdmin();
}
?>