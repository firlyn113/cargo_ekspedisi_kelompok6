<?php
/**
 * Subclass: KurirMotor
 * Mewarisi StaffLogistik dan menambahkan atribut & logika khusus kurir motor.
 *
 * Atribut tambahan (sesuai spec):
 *  - platNomorMotor
 *  - areaCakupan
 *
 * Polimorfisme:
 *  - hitungTakeHomePay() : Gaji Pokok + Insentif Per Paket + Bonus Accuracy
 *  - evaluasiSOPKerja()  : Akurasi Pengiriman, Ketepatan Waktu,
 *                          Penampilan & Presentasi, Kepuasan Pelanggan
 */

require_once 'StaffLogistik.php';

class KurirMotor extends StaffLogistik {

    // =====================================================
    //  ENKAPSULASI — Atribut private khusus KurirMotor
    // =====================================================
    private $platNomorMotor;
    private $areaCakupan;

    // Atribut metrik kinerja (diset sebelum evaluasi/THP dipanggil)
    private $jumlahPaketAntar  = 0;
    private $jumlahPaketTerima = 0;
    private $persentaseAccuracy = 100;

    // =====================================================
    //  CONSTRUCTOR
    // =====================================================
    public function __construct($conn) {
        parent::__construct($conn);
    }

    // =====================================================
    //  GETTER & SETTER
    // =====================================================
    public function setPlatNomorMotor($plat)    { $this->platNomorMotor   = $plat;  }
    public function getPlatNomorMotor()         { return $this->platNomorMotor;      }

    public function setAreaCakupan($area)       { $this->areaCakupan      = $area;  }
    public function getAreaCakupan()            { return $this->areaCakupan;         }

    public function setJumlahPaketAntar($n)     { $this->jumlahPaketAntar   = $n;  }
    public function getJumlahPaketAntar()       { return $this->jumlahPaketAntar;   }

    public function setJumlahPaketTerima($n)    { $this->jumlahPaketTerima  = $n;  }
    public function getJumlahPaketTerima()      { return $this->jumlahPaketTerima;  }

    public function setPersentaseAccuracy($pct) { $this->persentaseAccuracy = $pct; }
    public function getPersentaseAccuracy()     { return $this->persentaseAccuracy;  }

    // =====================================================
    //  IMPLEMENTASI ABSTRACT METHODS (Polimorfisme)
    // =====================================================

    /**
     * hitungTakeHomePay() — Override untuk KurirMotor
     *
     * Rumus:
     *   Insentif per paket = jumlahPaketAntar × Rp2.000
     *   Bonus accuracy     = 5% gaji pokok (accuracy ≥ 95%)
     *                      | 2% gaji pokok (accuracy ≥ 85%)
     *                      | Rp0           (di bawah 85%)
     *   Take Home Pay = Gaji Pokok + Insentif per Paket + Bonus Accuracy
     *
     * @return float Total Take Home Pay
     */
    public function hitungTakeHomePay() {
        $gajiPokok        = $this->getGajiPokok();
        $insentifPerPaket = $this->jumlahPaketAntar * 2000;

        if ($this->persentaseAccuracy >= 95) {
            $bonusAccuracy = $gajiPokok * 0.05;
        } elseif ($this->persentaseAccuracy >= 85) {
            $bonusAccuracy = $gajiPokok * 0.02;
        } else {
            $bonusAccuracy = 0;
        }

        return $gajiPokok + $insentifPerPaket + $bonusAccuracy;
    }

    /**
     * evaluasiSOPKerja() — Override untuk KurirMotor
     *
     * Kriteria penilaian:
     *  1. Akurasi Pengiriman          (maks 30 poin)
     *  2. Ketepatan Waktu Pengiriman  (25 poin)
     *  3. Penampilan & Presentasi Diri (20 poin)
     *  4. Kepuasan Pelanggan          (maks 25 poin)
     *
     * @return array Hasil evaluasi
     */
    public function evaluasiSOPKerja() {
        $evaluasi = [
            'nama_staff'  => $this->getNamaLengkap(),
            'jenis_staff' => 'KurirMotor',
            'skor_total'  => 0,
            'detail'      => [],
        ];

        // --- Kriteria 1: Akurasi Pengiriman ---
        $acc = $this->persentaseAccuracy;

        if ($acc >= 99)       { $skor1 = 30; $st1 = 'SEMPURNA'; }
        elseif ($acc >= 95)   { $skor1 = 25; $st1 = 'SANGAT BAIK'; }
        elseif ($acc >= 90)   { $skor1 = 20; $st1 = 'BAIK'; }
        elseif ($acc >= 85)   { $skor1 = 15; $st1 = 'CUKUP'; }
        else                  { $skor1 = 0;  $st1 = 'PERLU PERBAIKAN'; }

        $evaluasi['detail'][] = [
            'nama_kriteria' => 'Akurasi Pengiriman',
            'skor'          => $skor1,
            'status'        => $st1,
            'detail'        => 'Akurasi: ' . $acc . '%',
        ];

        // --- Kriteria 2: Ketepatan Waktu ---
        $evaluasi['detail'][] = [
            'nama_kriteria' => 'Ketepatan Waktu Pengiriman',
            'skor'          => 25,
            'status'        => 'SESUAI SLA',
            'detail'        => 'Semua pengiriman tepat waktu',
        ];

        // --- Kriteria 3: Penampilan & Presentasi Diri ---
        $evaluasi['detail'][] = [
            'nama_kriteria' => 'Penampilan & Presentasi Diri',
            'skor'          => 20,
            'status'        => 'PROFESIONAL',
            'detail'        => 'Motor: ' . $this->platNomorMotor,
        ];

        // --- Kriteria 4: Kepuasan Pelanggan (success rate paket) ---
        $successRate = ($this->jumlahPaketAntar > 0)
            ? ($this->jumlahPaketTerima / $this->jumlahPaketAntar) * 100
            : 0;

        if ($successRate >= 98)      { $skor4 = 25; $st4 = 'TERPUASKAN'; }
        elseif ($successRate >= 95)  { $skor4 = 20; $st4 = 'MEMUASKAN'; }
        else                         { $skor4 = 15; $st4 = 'CUKUP MEMUASKAN'; }

        $evaluasi['detail'][] = [
            'nama_kriteria' => 'Kepuasan Pelanggan',
            'skor'          => $skor4,
            'status'        => $st4,
            'detail'        => 'Success rate: ' . round($successRate, 1) . '%',
        ];

        // Total skor
        foreach ($evaluasi['detail'] as $k) {
            $evaluasi['skor_total'] += $k['skor'];
        }

        // Status keseluruhan
        if ($evaluasi['skor_total'] >= 95)      { $evaluasi['status_keseluruhan'] = 'LULUS – KARYAWAN BINTANG'; }
        elseif ($evaluasi['skor_total'] >= 85)  { $evaluasi['status_keseluruhan'] = 'LULUS – SANGAT MEMUASKAN'; }
        elseif ($evaluasi['skor_total'] >= 75)  { $evaluasi['status_keseluruhan'] = 'LULUS – MEMUASKAN'; }
        else                                    { $evaluasi['status_keseluruhan'] = 'TIDAK LULUS – BUTUH IMPROVEMENT'; }

        return $evaluasi;
    }

    /**
     * Override getJenisStaff
     */
    public function getJenisStaff() {
        return 'KurirMotor';
    }

    /**
     * Override displayInfo — tambahkan atribut spesifik KurirMotor
     */
    public function displayInfo() {
        $info = parent::displayInfo();
        $info['plat_nomor_motor'] = $this->platNomorMotor;
        $info['area_cakupan']     = $this->areaCakupan;
        return $info;
    }

    /**
     * Simpan ke database
     */
    public function save() {
        $sql = "INSERT INTO staff
                    (id_staff_code, nama_lengkap, gaji_pokok, jam_kerja,
                     jenis_staff, plat_nomor_motor, area_cakupan)
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
            $this->platNomorMotor,
            $this->areaCakupan
        );

        return $stmt->execute();
    }
}
?>
