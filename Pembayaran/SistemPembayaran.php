<?php
abstract class SistemPembayaran {
    // Encapsulation: protected properties
    protected $id_transaksi;
    protected $totalTagihan;
    protected $statusLunas;
    protected $waktuPembayaran;

    // Constructor
    public function __construct($id_transaksi, $totalTagihan) {
        $this->id_transaksi = $id_transaksi;
        $this->totalTagihan = $totalTagihan;
        $this->statusLunas = 'Belum Lunas';
        $this->waktuPembayaran = date('Y-m-d H:i:s');
    }

    // Getter & Setter (Encapsulation)
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

    public function getWaktuPembayaran() {
        return $this->waktuPembayaran;
    }

    // Polymorphism: 2 abstract methods
    abstract public function prosesValidasiBayar();
    abstract public function hitungBiayaAdmin();

    // Method tambahan untuk menampilkan informasi
    public function getInfoPembayaran() {
        return [
            'id_transaksi' => $this->id_transaksi,
            'total_tagihan' => $this->totalTagihan,
            'status' => $this->statusLunas,
            'waktu' => $this->waktuPembayaran,
            'biaya_admin' => $this->hitungBiayaAdmin(),
            'validasi' => $this->prosesValidasiBayar(),
            'total_dibayar' => $this->totalTagihan + $this->hitungBiayaAdmin()
        ];
    }
}
?>