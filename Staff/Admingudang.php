<?php
/**
 * Subclass: AdminGudang
 * Mewarisi StaffLogistik dan menambahkan atribut & logika khusus admin gudang.
 *
 * Atribut tambahan (sesuai spec):
 *  - shiftKerja   (Pagi / Siang / Malam)
 *  - zonaGudang
 *
 * Polimorfisme:
 *  - hitungTakeHomePay() : Gaji Pokok + Tunjangan Shift + Bonus Produktivitas
 *  - evaluasiSOPKerja()  : Keakuratan Data Entry, Kecepatan Pemrosesan,
 *                          Kehadiran & Ketaatan Shift, Penguasaan Sistem
 */

require_once 'StaffLogistik.php';

class AdminGudang extends StaffLogistik {

    // =====================================================
    //  ENKAPSULASI — Atribut private khusus AdminGudang
    // =====================================================
    private $shiftKerja;
    private $zonaGudang;

    // Atribut metrik kinerja (diset sebelum evaluasi/THP dipanggil)
    private $jumlahBarangDiproses    = 0;
    private $jumlahErrorAdministrasi = 0;

    // =====================================================
    //  CONSTRUCTOR
    // =====================================================
    public function __construct($conn) {
        parent::__construct($conn);
    }

    // =====================================================
    //  GETTER & SETTER
    // =====================================================
    public function setShiftKerja($shift)       { $this->shiftKerja              = $shift;  }
    public function getShiftKerja()             { return $this->shiftKerja;                 }

    public function setZonaGudang($zona)        { $this->zonaGudang              = $zona;   }
    public function getZonaGudang()             { return $this->zonaGudang;                 }

    public function setJumlahBarangDiproses($n)    { $this->jumlahBarangDiproses    = $n; }
    public function getJumlahBarangDiproses()      { return $this->jumlahBarangDiproses;   }

    public function setJumlahErrorAdministrasi($n) { $this->jumlahErrorAdministrasi = $n; }
    public function getJumlahErrorAdministrasi()   { return $this->jumlahErrorAdministrasi; }

    // =====================================================
    //  IMPLEMENTASI ABSTRACT METHODS (Polimorfisme)
    // =====================================================

    /**
     * hitungTakeHomePay() — Override untuk AdminGudang
     *
     * Rumus:
     *   Tunjangan Shift  = 10% gaji pokok (Malam) | 5% (Pagi/Siang)
     *   Bonus Produktivitas = (jumlahBarang × Rp500) − (jumlahError × Rp2.000)
     *                         minimum Rp0
     *   Take Home Pay = Gaji Pokok + Tunjangan Shift + Bonus Produktivitas
     *
     * @return float Total Take Home Pay
     */
    public function hitungTakeHomePay() {
        $gajiPokok = $this->getGajiPokok();

        // Tunjangan shift
        $tunjanganShift = ($this->shiftKerja === 'Malam')
            ? $gajiPokok * 0.10   // 10% shift malam
            : $gajiPokok * 0.05;  // 5%  shift pagi/siang

        // Bonus produktivitas (tidak boleh negatif)
        $bonusProduktivitas = max(
            0,
            ($this->jumlahBarangDiproses * 500)
            - ($this->jumlahErrorAdministrasi * 2000)
        );

        return $gajiPokok + $tunjanganShift + $bonusProduktivitas;
    }

    /**
     * evaluasiSOPKerja() — Override untuk AdminGudang
     *
     * Kriteria penilaian:
     *  1. Keakuratan Data Entry      (maks 30 poin)
     *  2. Kecepatan Pemrosesan       (maks 25 poin)  — target 50 barang/hari
     *  3. Kehadiran & Ketaatan Shift (25 poin)
     *  4. Penguasaan Sistem Informasi (20 poin)
     *
     * @return array Hasil evaluasi
     */
    public function evaluasiSOPKerja() {
        $evaluasi = [
            'nama_staff'  => $this->getNamaLengkap(),
            'jenis_staff' => 'AdminGudang',
            'skor_total'  => 0,
            'detail'      => [],
        ];

        // --- Kriteria 1: Keakuratan Data Entry ---
        $errorRate = ($this->jumlahBarangDiproses > 0)
            ? ($this->jumlahErrorAdministrasi / $this->jumlahBarangDiproses) * 100
            : 0;

        if ($errorRate < 2)       { $skor1 = 30; $status1 = 'SANGAT BAIK'; }
        elseif ($errorRate < 5)   { $skor1 = 20; $status1 = 'BAIK'; }
        elseif ($errorRate < 10)  { $skor1 = 10; $status1 = 'CUKUP'; }
        else                      { $skor1 = 0;  $status1 = 'PERLU PERBAIKAN'; }

        $evaluasi['detail'][] = [
            'nama_kriteria' => 'Keakuratan Data Entry',
            'skor'          => $skor1,
            'status'        => $status1,
            'detail'        => 'Error rate: ' . round($errorRate, 2) . '%',
        ];

        // --- Kriteria 2: Kecepatan Pemrosesan (target 50 barang/hari) ---
        $persen = ($this->jumlahBarangDiproses / 50) * 100;

        if ($persen >= 100)      { $skor2 = 25; $status2 = 'MENCAPAI TARGET'; }
        elseif ($persen >= 80)   { $skor2 = 20; $status2 = 'HAMPIR MENCAPAI'; }
        else                     { $skor2 = 15; $status2 = 'DI BAWAH TARGET'; }

        $evaluasi['detail'][] = [
            'nama_kriteria' => 'Kecepatan Pemrosesan',
            'skor'          => $skor2,
            'status'        => $status2,
            'detail'        => round($persen, 1) . '% dari target 50 barang/hari',
        ];

        // --- Kriteria 3: Kehadiran & Ketaatan Shift ---
        $evaluasi['detail'][] = [
            'nama_kriteria' => 'Kehadiran & Ketaatan Shift',
            'skor'          => 25,
            'status'        => 'TERPENUHI',
            'detail'        => 'Shift: ' . $this->shiftKerja,
        ];

        // --- Kriteria 4: Penguasaan Sistem Informasi ---
        $evaluasi['detail'][] = [
            'nama_kriteria' => 'Penguasaan Sistem Informasi',
            'skor'          => 20,
            'status'        => 'KOMPETEN',
            'detail'        => 'Zona gudang: ' . $this->zonaGudang,
        ];

        // Total skor
        foreach ($evaluasi['detail'] as $k) {
            $evaluasi['skor_total'] += $k['skor'];
        }

        // Status keseluruhan
        if ($evaluasi['skor_total'] >= 85)      { $evaluasi['status_keseluruhan'] = 'LULUS – LAYAK PROMOSI'; }
        elseif ($evaluasi['skor_total'] >= 75)  { $evaluasi['status_keseluruhan'] = 'LULUS – SESUAI STANDAR'; }
        elseif ($evaluasi['skor_total'] >= 60)  { $evaluasi['status_keseluruhan'] = 'LULUS – PERLU PERBAIKAN'; }
        else                                    { $evaluasi['status_keseluruhan'] = 'TIDAK LULUS – PERLU PELATIHAN ULANG'; }

        return $evaluasi;
    }

    /**
     * Override getJenisStaff
     */
    public function getJenisStaff() {
        return 'AdminGudang';
    }

    /**
     * Override displayInfo — tambahkan atribut spesifik AdminGudang
     */
    public function displayInfo() {
        $info = parent::displayInfo();
        $info['shift_kerja'] = $this->shiftKerja;
        $info['zona_gudang'] = $this->zonaGudang;
        return $info;
    }

    /**
     * Simpan ke database
     */
    public function save() {
        $sql = "INSERT INTO staff
                    (id_staff_code, nama_lengkap, gaji_pokok, jam_kerja,
                     jenis_staff, shift_kerja, zona_gudang)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt  = $this->getConn()->prepare($sql);
        $idCode = $this->getIdStaffCode();
        $nama  = $this->getNamaLengkap();
        $gaji  = $this->getGajiPokok();
        $jam   = $this->getJamKerja();
        $jenis = $this->getJenisStaff();

        $stmt->bind_param(
            'ssdisss',
            $idCode,
            $nama,
            $gaji,
            $jam,
            $jenis,
            $this->shiftKerja,
            $this->zonaGudang
        );

        return $stmt->execute();
    }
}
?>
