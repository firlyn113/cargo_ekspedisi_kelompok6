<?php
abstract class AbstractPelanggan {
    // Encapsulation: protected attributes
    protected $id_pelanggan;
    protected $id_pelanggan_code;
    protected $nama_lengkap;
    protected $total_transaksi_bulan_ini;
    protected $poin_reward;
    protected $jenis_pelanggan;
    protected $created_at;
    
    // Constructor
    public function __construct($id_pelanggan_code, $nama_lengkap) {
        $this->id_pelanggan_code = $id_pelanggan_code;
        $this->nama_lengkap = $nama_lengkap;
        $this->total_transaksi_bulan_ini = 0;
        $this->poin_reward = 0;
        $this->created_at = date('Y-m-d H:i:s');
    }
    
    // Abstract methods for polymorphism
    abstract public function hitungDiskonPengiriman($total_biaya);
    abstract public function dapatkanBenefitTambahan();
    
    // Getter methods (Encapsulation)
    public function getIdPelanggan() {
        return $this->id_pelanggan;
    }
    
    public function getIdPelangganCode() {
        return $this->id_pelanggan_code;
    }
    
    public function getNamaLengkap() {
        return $this->nama_lengkap;
    }
    
    public function getTotalTransaksiBulanIni() {
        return $this->total_transaksi_bulan_ini;
    }
    
    public function getPoinReward() {
        return $this->poin_reward;
    }
    
    public function getJenisPelanggan() {
        return $this->jenis_pelanggan;
    }
    
    // Setter methods
    public function setNamaLengkap($nama_lengkap) {
        $this->nama_lengkap = $nama_lengkap;
    }
    
    public function setTotalTransaksiBulanIni($total) {
        $this->total_transaksi_bulan_ini = $total;
    }
    
    public function setPoinReward($poin) {
        $this->poin_reward = $poin;
    }
    
    // Business logic method
    public function tambahTransaksi($nominal) {
        $this->total_transaksi_bulan_ini += $nominal;
        $this->poin_reward += floor($nominal / 100000); // 1 poin per 100k
    }
    
    // Method untuk menampilkan info pelanggan (opsional)
    public function getInfoPelanggan() {
        return [
            'id' => $this->id_pelanggan,
            'code' => $this->id_pelanggan_code,
            'nama' => $this->nama_lengkap,
            'jenis' => $this->jenis_pelanggan,
            'total_transaksi' => $this->total_transaksi_bulan_ini,
            'poin' => $this->poin_reward,
            'created_at' => $this->created_at
        ];
    }
}
?>