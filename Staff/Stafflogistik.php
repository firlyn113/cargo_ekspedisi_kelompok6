<?php
/**
 * Abstract Class: StaffLogistik
 * Kelas abstrak utama untuk semua pegawai logistik.
 *
 * Pilar OOP yang diterapkan:
 *  - Abstraksi   : Kelas ini tidak bisa diinstansiasi langsung
 *  - Enkapsulasi : Atribut bersifat private, diakses via getter/setter
 *  - Inheritance : Subclass mewarisi atribut & method umum
 *  - Polimorfisme: Abstract method wajib di-override tiap subclass
 */
abstract class StaffLogistik {

    // =====================================================
    //  ENKAPSULASI — Atribut private (hanya 4 sesuai spec)
    // =====================================================
    private $idStaff;
    private $namaLengkap;
    private $gajiPokok;
    private $jamKerja;

    /** Koneksi database, protected agar subclass bisa mengaksesnya */
    protected $conn;

    // =====================================================
    //  CONSTRUCTOR
    // =====================================================
    public function __construct($conn) {
        $this->conn = $conn;
    }

    // =====================================================
    //  GETTER & SETTER  (Enkapsulasi)
    // =====================================================
    public function setIdStaff($idStaff)       { $this->idStaff     = $idStaff;     }
    public function getIdStaff()               { return $this->idStaff;              }

    public function setNamaLengkap($nama)      { $this->namaLengkap = $nama;         }
    public function getNamaLengkap()           { return $this->namaLengkap;          }

    public function setGajiPokok($gaji)        { $this->gajiPokok   = $gaji;         }
    public function getGajiPokok()             { return $this->gajiPokok;            }

    public function setJamKerja($jam)          { $this->jamKerja    = $jam;          }
    public function getJamKerja()              { return $this->jamKerja;             }

    public function getConn()                  { return $this->conn;                 }

    // =====================================================
    //  ABSTRACT METHODS  (Polimorfisme)
    //  Wajib diimplementasikan oleh setiap subclass
    // =====================================================

    /**
     * Menghitung total gaji bawa pulang.
     * Setiap subclass memiliki rumus perhitungan yang berbeda.
     *
     * @return float Total Take Home Pay
     */
    abstract public function hitungTakeHomePay();

    /**
     * Mengevaluasi kinerja berdasarkan SOP peran masing-masing.
     * Setiap subclass memiliki kriteria evaluasi yang berbeda.
     *
     * @return array Hasil evaluasi beserta skor dan detail
     */
    abstract public function evaluasiSOPKerja();

    /**
     * Mengembalikan nama jenis staff (dioverride di subclass).
     *
     * @return string Nama jenis staff
     */
    abstract public function getJenisStaff();

    // =====================================================
    //  METHOD UMUM  (Concrete — diwarisi semua subclass)
    // =====================================================

    /**
     * Menampilkan info dasar staff (atribut dari abstract class).
     * Subclass dapat meng-override dan menambahkan atribut spesifiknya.
     *
     * @return array Data dasar staff
     */
    public function displayInfo() {
        return [
            'id_staff'     => $this->idStaff,
            'nama_lengkap' => $this->namaLengkap,
            'gaji_pokok'   => $this->gajiPokok,
            'jam_kerja'    => $this->jamKerja,
            'jenis_staff'  => $this->getJenisStaff(),
        ];
    }
}
?>
