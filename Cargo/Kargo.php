* File: Kargo.php
 * Abstract Class: Kargo
 * Fungsi: Class abstrak utama untuk modul kargo dengan implementasi OOP (Enkapsulasi, Abstraksi)
 */

abstract class Kargo {
    // Enkapsulasi: Atribut private
    private $id_resi;
    private $pengirim;
    private $kota_tujuan;
    private $berat_barang;
    private $tarif_dasar_per_kg;
    protected $jenis_kargo;
    
    // Constructor
    public function __construct($id_resi, $pengirim, $kota_tujuan, $berat_barang, $tarif_dasar_per_kg) {
        $this->id_resi = $id_resi;
        $this->pengirim = $pengirim;
        $this->kota_tujuan = $kota_tujuan;
        $this->berat_barang = $berat_barang;
        $this->tarif_dasar_per_kg = $tarif_dasar_per_kg;
    }
    
    // Getter methods (Enkapsulasi)
    public function getIdResi() {
        return $this->id_resi;
    }
    
    public function getPengirim() {
        return $this->pengirim;
    }
    
    public function getKotaTujuan() {
        return $this->kota_tujuan;
    }
    
    public function getBeratBarang() {
        return $this->berat_barang;
    }
    
    public function getTarifDasarPerKg() {
        return $this->tarif_dasar_per_kg;
    }
    
    protected function getBiayaDasar() {
        return $this->berat_barang * $this->tarif_dasar_per_kg;
    }
    
    // Setter methods
    public function setIdResi($id_resi) {
        $this->id_resi = $id_resi;
    }
    
    public function setPengirim($pengirim) {
        $this->pengirim = $pengirim;
    }
    
    // Abstract methods (Polimorfisme - akan diimplementasikan oleh subclass)
    public abstract function hitungTarifPengiriman();
    public abstract function getInfoKargo();
    
    // Method untuk validasi data
    public function validasiData() {
        if(empty($this->id_resi) || empty($this->pengirim) || empty($this->kota_tujuan)) {
            return false;
        }
        if($this->berat_barang <= 0 || $this->tarif_dasar_per_kg <= 0) {
            return false;
        }
        return true;
    }
    
    // Method untuk menyimpan ke database
    public function simpanKeDatabase($koneksi, $dataTambahan = []) {
        $sql = "INSERT INTO kargo (id_resi, pengirim, kota_tujuan, berat_barang, tarif_dasar_per_kg, jenis_kargo";
        
        // Tambahkan field khusus berdasarkan jenis kargo
        $fields = "";
        $values = "";
        $param_types = "sssddd";
        $params = [$this->id_resi, $this->pengirim, $this->kota_tujuan, $this->berat_barang, $this->tarif_dasar_per_kg, $this->jenis_kargo];
        
        foreach($dataTambahan as $field => $value) {
            $fields .= ", " . $field;
            $values .= ", ?";
            $param_types .= "s";
            $params[] = $value;
        }
        
        $sql .= $fields . ") VALUES (?, ?, ?, ?, ?, ?" . $values . ")";
        
        $stmt = $koneksi->prepare($sql);
        if($stmt) {
            $stmt->bind_param($param_types, ...$params);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }
        return false;
    }
}
?>