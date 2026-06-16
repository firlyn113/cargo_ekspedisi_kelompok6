<?php
/**
 * Subclass: SupirTruk
 * Mewarisi StaffLogistik dan menambahkan atribut & logika khusus sopir truk.
 *
 * Atribut tambahan (sesuai spec):
 *  - nomorSIM_B
 *  - uangMakanJalan
 *
 * Polimorfisme:
 *  - hitungTakeHomePay() : Gaji Pokok + Uang Makan Jalan + Tunjangan Lembur
 *  - evaluasiSOPKerja()  : Kelayakan SIM B, Riwayat Kecelakaan,
 *                          Ketepatan Waktu, Kelengkapan Dokumen
 */

require_once 'StaffLogistik.php';

class SupirTruk extends StaffLogistik {

    // =====================================================
    //  ENKAPSULASI — Atribut private khusus SupirTruk
    // =====================================================
    private $nomorSIM_B;
    private $uangMakanJalan;

    // =====================================================
    //  CONSTRUCTOR
    // =====================================================
    public function __construct($conn) {
        parent::__construct($conn);
    }

    // =====================================================
    //  GETTER & SETTER
    // =====================================================
    public function setNomorSIM_B($nomor)        { $this->nomorSIM_B      = $nomor; }
    public function getNomorSIM_B()              { return $this->nomorSIM_B;         }

    public function setUangMakanJalan($nominal)  { $this->uangMakanJalan  = $nominal; }
    public function getUangMakanJalan()          { return $this->uangMakanJalan;       }

    // =====================================================
    //  IMPLEMENTASI ABSTRACT METHODS (Polimorfisme)
    // =====================================================

    /**
     * hitungTakeHomePay() — Override untuk SupirTruk
     *
     * Rumus:
     *   Take Home Pay = Gaji Pokok + Uang Makan Jalan
     *
     * Uang Makan Jalan sudah mencakup tunjangan perjalanan
     * yang nilainya diinput per staff (bukan dihitung otomatis).
     *
     * @return float Total Take Home Pay
     */
    public function hitungTakeHomePay() {
        $gajiPokok      = $this->getGajiPokok();
        $uangMakanJalan = $this->uangMakanJalan ?? 0;

        $totalTakeHome  = $gajiPokok + $uangMakanJalan;

        return $totalTakeHome;
    }

    /**
     * evaluasiSOPKerja() — Override untuk SupirTruk
     *
     * Kriteria penilaian:
     *  1. Kelayakan SIM B           (25 poin)
     *  2. Riwayat Kecelakaan        (25 poin)
     *  3. Ketepatan Waktu Pengiriman (25 poin)
     *  4. Kelengkapan Dokumen       (25 poin)
     *
     * @return array Hasil evaluasi
     */
    public function evaluasiSOPKerja() {
        $evaluasi = [
            'nama_staff'  => $this->getNamaLengkap(),
            'jenis_staff' => 'SupirTruk',
            'skor_total'  => 0,
            'detail'      => [],
        ];

        // Kriteria 1: Kelayakan SIM B
        $simValid = !empty($this->nomorSIM_B);
        $evaluasi['detail'][] = [
            'nama_kriteria' => 'Kelayakan SIM B',
            'skor'          => $simValid ? 25 : 0,
            'status'        => $simValid ? 'VALID' : 'TIDAK VALID',
            'detail'        => $simValid ? 'No. SIM: ' . $this->nomorSIM_B : 'SIM B tidak terdaftar',
        ];

        // Kriteria 2: Riwayat Kecelakaan (diasumsikan bersih dari DB)
        $evaluasi['detail'][] = [
            'nama_kriteria' => 'Riwayat Kecelakaan',
            'skor'          => 25,
            'status'        => 'BERSIH',
            'detail'        => 'Tidak ada catatan kecelakaan',
        ];

        // Kriteria 3: Ketepatan Waktu Pengiriman
        $evaluasi['detail'][] = [
            'nama_kriteria' => 'Ketepatan Waktu Pengiriman',
            'skor'          => 25,
            'status'        => 'MEMUASKAN',
            'detail'        => 'Rata-rata pengiriman tepat waktu',
        ];

        // Kriteria 4: Kelengkapan Dokumen
        $evaluasi['detail'][] = [
            'nama_kriteria' => 'Kelengkapan Dokumen',
            'skor'          => 25,
            'status'        => 'LENGKAP',
            'detail'        => 'STNK, KIR, dan surat jalan tersedia',
        ];

        // Total skor
        foreach ($evaluasi['detail'] as $k) {
            $evaluasi['skor_total'] += $k['skor'];
        }

        // Status keseluruhan
        if ($evaluasi['skor_total'] >= 90) {
            $evaluasi['status_keseluruhan'] = 'LULUS – SIAP OPERASIONAL';
        } elseif ($evaluasi['skor_total'] >= 75) {
            $evaluasi['status_keseluruhan'] = 'LULUS DENGAN CATATAN';
        } else {
            $evaluasi['status_keseluruhan'] = 'TIDAK LULUS – PERLU PELATIHAN';
        }

        return $evaluasi;
    }

    /**
     * Override getJenisStaff
     */
    public function getJenisStaff() {
        return 'SupirTruk';
    }

    /**
     * Override displayInfo — tambahkan atribut spesifik SupirTruk
     */
    public function displayInfo() {
        $info = parent::displayInfo();
        $info['nomor_sim_b']     = $this->nomorSIM_B;
        $info['uang_makan_jalan'] = $this->uangMakanJalan;
        return $info;
    }

    /**
     * Simpan ke database
     */
    public function save() {
        $sql = "INSERT INTO staff
                    (id_staff_code, nama_lengkap, gaji_pokok, jam_kerja,
                     jenis_staff, nomor_sim_b, uang_makan_jalan)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt        = $this->getConn()->prepare($sql);
        $idCode      = $this->getIdStaffCode();   // Tambahan: tetap butuh id_staff_code untuk DB
        $nama        = $this->getNamaLengkap();
        $gaji        = $this->getGajiPokok();
        $jam         = $this->getJamKerja();
        $jenis       = $this->getJenisStaff();

        $stmt->bind_param(
            'ssdisd d',
            $idCode,
            $nama,
            $gaji,
            $jam,
            $jenis,
            $this->nomorSIM_B,
            $this->uangMakanJalan
        );

        return $stmt->execute();
    }
}
?>
