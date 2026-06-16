<?php
/**
 * index.php - Modul STAFF (Read-Only)
 * Menampilkan data staff berdasarkan konsep OOP:
 * Abstract Class StaffLogistik, Subclass, Polimorfisme
 */

require_once '../config/koneksi.php';
require_once 'StaffManager.php';

$database = new Database();
$conn = $database->getConnection();
$staffManager = new StaffManager($conn);

$mode = isset($_GET['mode']) ? $_GET['mode'] : 'list';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modul STAFF – Ekspedisi Logistik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --brand:       #1B4FBF;
            --brand-light: #EBF1FD;
            --brand-dark:  #0D2E7A;
            --accent:      #0F6E56;
            --accent-bg:   #E1F5EE;
            --muted:       #6c757d;
            --surface:     #F7F9FC;
            --border:      #DEE2E6;
        }

        body {
            background-color: var(--surface);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            font-size: 0.9rem;
            color: #212529;
        }

        /* ── NAVBAR ── */
        .top-bar {
            background: var(--brand-dark);
            color: #fff;
            padding: 0.7rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.01em;
        }
        .top-bar span.sub {
            font-weight: 400;
            opacity: 0.65;
            font-size: 0.85rem;
        }

        /* ── LAYOUT ── */
        .page-wrap {
            max-width: 1060px;
            margin: 1.8rem auto;
            padding: 0 1rem;
        }

        /* ── OOP BANNER ── */
        .oop-banner {
            background: var(--brand-light);
            border: 1px solid #c5d8fa;
            border-radius: 8px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.8rem;
        }
        .oop-banner .icon {
            font-size: 1.5rem;
            color: var(--brand);
            flex-shrink: 0;
            margin-top: 2px;
        }
        .oop-banner h6 {
            margin: 0 0 0.2rem;
            font-weight: 600;
            color: var(--brand-dark);
            font-size: 0.9rem;
        }
        .oop-banner p {
            margin: 0;
            color: #3a5a9e;
            font-size: 0.82rem;
            line-height: 1.5;
        }
        .pill {
            display: inline-block;
            background: var(--brand);
            color: #fff;
            border-radius: 20px;
            padding: 1px 8px;
            font-size: 0.72rem;
            font-weight: 600;
            margin-right: 3px;
        }

        /* ── STAT CARDS ── */
        .stat-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        .stat-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1rem 1.1rem;
            text-align: center;
        }
        .stat-card .num {
            font-size: 1.9rem;
            font-weight: 700;
            line-height: 1.1;
            color: var(--brand);
        }
        .stat-card .lbl {
            font-size: 0.75rem;
            color: var(--muted);
            margin-top: 0.25rem;
        }
        .stat-card .badge-type {
            display: inline-block;
            margin-top: 0.3rem;
            font-size: 0.68rem;
            padding: 2px 7px;
            border-radius: 4px;
        }

        /* ── SEARCH BAR ── */
        .search-bar {
            display: flex;
            gap: 0.6rem;
            margin-bottom: 1.2rem;
            flex-wrap: wrap;
        }
        .search-bar input,
        .search-bar select {
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 0.45rem 0.8rem;
            font-size: 0.85rem;
            background: #fff;
            outline: none;
            transition: border-color 0.15s;
        }
        .search-bar input { flex: 1; min-width: 180px; }
        .search-bar select { min-width: 175px; }
        .search-bar input:focus,
        .search-bar select:focus { border-color: var(--brand); }

        /* ── TABLE ── */
        .card-box {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
        }
        .card-box-header {
            padding: 0.85rem 1.1rem;
            border-bottom: 1px solid var(--border);
            font-weight: 600;
            font-size: 0.88rem;
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        table.staff-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.84rem;
        }
        .staff-table thead th {
            background: var(--surface);
            padding: 0.6rem 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 1px solid var(--border);
        }
        .staff-table tbody tr {
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.1s;
            cursor: pointer;
        }
        .staff-table tbody tr:hover { background: #f5f8ff; }
        .staff-table tbody tr:last-child { border-bottom: none; }
        .staff-table td { padding: 0.65rem 1rem; vertical-align: middle; }
        .id-code { font-family: monospace; font-size: 0.8rem; color: var(--muted); }
        .nama { font-weight: 500; }

        /* ── BADGE JENIS ── */
        .jenis-badge {
            display: inline-block;
            border-radius: 4px;
            padding: 2px 9px;
            font-size: 0.72rem;
            font-weight: 600;
        }
        .badge-supir   { background: #FFF3CD; color: #856404; }
        .badge-admin   { background: var(--brand-light); color: var(--brand-dark); }
        .badge-kurir   { background: var(--accent-bg); color: var(--accent); }

        /* ── VIEW DETAIL (slide panel) ── */
        .panel-wrap {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .detail-table td { padding: 0.35rem 0; }
        .detail-table td:first-child {
            color: var(--muted);
            width: 40%;
            font-size: 0.8rem;
        }

        /* ── TABS (detail page) ── */
        .tab-nav {
            display: flex;
            gap: 0;
            border-bottom: 1px solid var(--border);
            margin-bottom: 1rem;
        }
        .tab-btn {
            background: none;
            border: none;
            border-bottom: 2px solid transparent;
            padding: 0.55rem 1rem;
            font-size: 0.82rem;
            cursor: pointer;
            color: var(--muted);
            font-weight: 500;
            transition: color 0.15s, border-color 0.15s;
        }
        .tab-btn.active {
            color: var(--brand);
            border-bottom-color: var(--brand);
        }
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }

        /* ── THP TABLE ── */
        .thp-table { width: 100%; font-size: 0.83rem; border-collapse: collapse; }
        .thp-table tr { border-bottom: 1px solid #f0f0f0; }
        .thp-table td { padding: 0.4rem 0; }
        .thp-table td:last-child { text-align: right; font-weight: 500; }
        .thp-total { font-size: 1rem; font-weight: 700; color: var(--accent); }

        /* ── EVALUASI ── */
        .eval-row { display: flex; justify-content: space-between; align-items: center; padding: 0.4rem 0; border-bottom: 1px solid #f0f0f0; font-size: 0.82rem; }
        .eval-row:last-child { border-bottom: none; }
        .eval-skor { font-weight: 600; color: var(--brand); }
        .eval-lulus   { background: var(--accent-bg); color: var(--accent); font-weight: 600; border-radius: 4px; padding: 2px 9px; font-size: 0.75rem; }
        .eval-cond    { background: #FFF3CD; color: #856404; font-weight: 600; border-radius: 4px; padding: 2px 9px; font-size: 0.75rem; }
        .eval-gagal   { background: #fce8e8; color: #a32d2d; font-weight: 600; border-radius: 4px; padding: 2px 9px; font-size: 0.75rem; }

        /* ── BACK LINK ── */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            color: var(--brand);
            text-decoration: none;
            font-size: 0.83rem;
            margin-bottom: 1rem;
        }
        .back-link:hover { text-decoration: underline; }

        /* ── EMPTY ── */
        .empty-row td {
            text-align: center;
            color: var(--muted);
            padding: 2.5rem;
            font-size: 0.85rem;
        }

        @media (max-width: 640px) {
            .stat-row { grid-template-columns: repeat(2, 1fr); }
            .panel-wrap { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- TOP BAR -->
<div class="top-bar">
    <i class="bi bi-people-fill"></i>
    Modul STAFF
    <span class="sub">— Sistem Ekspedisi Logistik</span>
    <a href="../dashboard.php" class="btn btn-primary">
    Kembali ke Dashboard
</a>
</div>

<div class="page-wrap">

<?php

/* ============================================================
   MODE: DETAIL / VIEW
   ============================================================ */
if ($mode == 'view' && isset($_GET['id'])):
    $staffData = $staffManager->getStaffById($_GET['id']);
    if (!$staffData['success']):
?>
    <div class="alert alert-danger">Staff tidak ditemukan.</div>
    <a href="?" class="back-link"><i class="bi bi-arrow-left"></i> Kembali ke daftar</a>
<?php else:
    $staff = $staffData['data'];

    /* ---- Hitung THP ---- */
    $thpResult = $staffManager->hitungTakeHomePay($staff['id_staff']);
    $thpData   = $thpResult['success'] ? $thpResult['data'] : [];

    /* ---- Evaluasi SOP ---- */
    $evalData = [];
    if ($staff['jenis_staff'] == 'AdminGudang') {
        $evalData = ['jumlah_barang' => 50, 'jumlah_error' => 1];
    } elseif ($staff['jenis_staff'] == 'KurirMotor') {
        $evalData = ['jumlah_paket_antar' => 20, 'jumlah_paket_terima' => 20, 'accuracy_persen' => 98];
    }
    $evalResult = $staffManager->evaluasiSOPKerja($staff['id_staff'], $evalData);
    $eval       = ($evalResult['success'] ?? false) ? $evalResult['data'] : [];

    /* Badge warna per jenis */
    $badgeClass = ['SupirTruk' => 'badge-supir', 'AdminGudang' => 'badge-admin', 'KurirMotor' => 'badge-kurir'];
    $jenisLabel = ['SupirTruk' => 'Sopir Truk', 'AdminGudang' => 'Admin Gudang', 'KurirMotor' => 'Kurir Motor'];
    $bc = $badgeClass[$staff['jenis_staff']] ?? 'badge-admin';
    $jl = $jenisLabel[$staff['jenis_staff']] ?? $staff['jenis_staff'];
?>

    <a href="?" class="back-link"><i class="bi bi-arrow-left"></i> Kembali ke daftar</a>

    <!-- OOP BANNER – menunjukkan konsep yang dipakai -->
    <div class="oop-banner">
        <div class="icon"><i class="bi bi-diagram-3-fill"></i></div>
        <div>
            <h6>Konsep OOP yang diterapkan pada objek ini</h6>
            <p>
                <span class="pill">Abstract Class</span> <code>StaffLogistik</code> &rarr;
                <span class="pill">Subclass</span> <code><?php echo $staff['jenis_staff']; ?></code> &nbsp;|&nbsp;
                <span class="pill">Enkapsulasi</span> id_staff, namaLengkap, gajiPokok, jamKerja &nbsp;|&nbsp;
                <span class="pill">Polimorfisme</span> hitungTakeHomePay() &amp; evaluasiSOPKerja()
            </p>
        </div>
    </div>

    <div class="panel-wrap">

        <!-- ── PANEL KIRI: Info Staff ── -->
        <div class="card-box">
            <div class="card-box-header">
                <i class="bi bi-person-badge"></i> Informasi Staff
                <span class="jenis-badge <?php echo $bc; ?>" style="margin-left:auto;"><?php echo $jl; ?></span>
            </div>
            <div style="padding: 1rem 1.1rem;">
                <!-- Inisial Avatar -->
                <?php
                    $namaParts = explode(' ', $staff['nama_lengkap']);
                    $inisial   = strtoupper(substr($namaParts[0],0,1) . (isset($namaParts[1]) ? substr($namaParts[1],0,1) : ''));
                ?>
                <div style="display:flex;align-items:center;gap:0.9rem;margin-bottom:1rem;padding-bottom:0.9rem;border-bottom:1px solid var(--border);">
                    <div style="width:46px;height:46px;border-radius:50%;background:var(--brand-light);color:var(--brand-dark);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1rem;flex-shrink:0;">
                        <?php echo $inisial; ?>
                    </div>
                    <div>
                        <div style="font-weight:600;font-size:1rem;"><?php echo htmlspecialchars($staff['nama_lengkap']); ?></div>
                        <div class="id-code"><?php echo htmlspecialchars($staff['id_staff_code']); ?></div>
                    </div>
                </div>

                <!-- Atribut Enkapsulasi (dari Abstract Class) -->
                <p style="font-size:0.7rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.4rem;">
                    Atribut dari <code>StaffLogistik</code>
                </p>
                <table class="detail-table" style="width:100%;margin-bottom:1rem;">
                    <tr><td>Gaji Pokok</td><td><strong>Rp <?php echo number_format($staff['gaji_pokok'],0,',','.'); ?></strong></td></tr>
                    <tr><td>Jam Kerja</td><td><?php echo $staff['jam_kerja']; ?> jam / bulan</td></tr>
                    <tr><td>Jenis Staff</td><td><span class="jenis-badge <?php echo $bc; ?>"><?php echo $jl; ?></span></td></tr>
                </table>

                <!-- Atribut Tambahan per Subclass -->
                <p style="font-size:0.7rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.4rem;">
                    Atribut tambahan <code><?php echo $staff['jenis_staff']; ?></code>
                </p>
                <table class="detail-table" style="width:100%;">
                    <?php if ($staff['jenis_staff'] == 'SupirTruk'): ?>
                        <tr><td>Nomor SIM B</td><td><?php echo htmlspecialchars($staff['nomor_sim_b'] ?? '-'); ?></td></tr>
                        <tr><td>Uang Makan Jalan</td><td>Rp <?php echo number_format($staff['uang_makan_jalan'] ?? 0,0,',','.'); ?></td></tr>
                        <tr><td>Rute Operasional</td><td><?php echo htmlspecialchars($staff['rute_tol'] ?? '-'); ?></td></tr>
                    <?php elseif ($staff['jenis_staff'] == 'AdminGudang'): ?>
                        <tr><td>Shift Kerja</td><td><?php echo htmlspecialchars($staff['shift_kerja'] ?? '-'); ?></td></tr>
                        <tr><td>Zona Gudang</td><td><?php echo htmlspecialchars($staff['zona_gudang'] ?? '-'); ?></td></tr>
                    <?php elseif ($staff['jenis_staff'] == 'KurirMotor'): ?>
                        <tr><td>Plat Motor</td><td><?php echo htmlspecialchars($staff['plat_nomor_motor'] ?? '-'); ?></td></tr>
                        <tr><td>Area Cakupan</td><td><?php echo htmlspecialchars($staff['area_cakupan'] ?? '-'); ?></td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <!-- ── PANEL KANAN: Polimorfisme ── -->
        <div class="card-box">
            <div class="card-box-header">
                <i class="bi bi-cpu"></i> Method Polimorfisme
            </div>
            <div style="padding: 1rem 1.1rem;">

                <!-- TAB NAV -->
                <div class="tab-nav">
                    <button class="tab-btn active" onclick="switchTab(this,'tab-thp')">
                        <i class="bi bi-cash-stack"></i> hitungTakeHomePay()
                    </button>
                    <button class="tab-btn" onclick="switchTab(this,'tab-eval')">
                        <i class="bi bi-clipboard2-check"></i> evaluasiSOPKerja()
                    </button>
                </div>

                <!-- TAB: TAKE HOME PAY -->
                <div id="tab-thp" class="tab-pane active">
                    <?php if (!empty($thpData)): ?>
                        <table class="thp-table">
                            <tr>
                                <td style="color:var(--muted);">Gaji Pokok</td>
                                <td>Rp <?php echo number_format($thpData['gaji_pokok'],0,',','.'); ?></td>
                            </tr>
                            <?php if ($staff['jenis_staff'] == 'SupirTruk'): ?>
                                <tr>
                                    <td style="color:var(--muted);">+ Uang Makan Jalan</td>
                                    <td>Rp <?php echo number_format($staff['uang_makan_jalan'] ?? 0,0,',','.'); ?></td>
                                </tr>
                                <tr>
                                    <td style="color:var(--muted);">+ Tunjangan Lembur</td>
                                    <td>Rp 0</td>
                                </tr>
                            <?php elseif ($staff['jenis_staff'] == 'AdminGudang'):
                                $tunjShift = $thpData['gaji_pokok'] * ($staff['shift_kerja'] == 'Malam' ? 0.10 : 0.05);
                            ?>
                                <tr>
                                    <td style="color:var(--muted);">+ Tunjangan Shift (<?php echo $staff['shift_kerja']; ?>)</td>
                                    <td>Rp <?php echo number_format($tunjShift,0,',','.'); ?></td>
                                </tr>
                                <tr>
                                    <td style="color:var(--muted);">+ Bonus Produktivitas</td>
                                    <td>Rp 0</td>
                                </tr>
                            <?php elseif ($staff['jenis_staff'] == 'KurirMotor'): ?>
                                <tr>
                                    <td style="color:var(--muted);">+ Insentif Per Paket</td>
                                    <td>Rp 0</td>
                                </tr>
                                <tr>
                                    <td style="color:var(--muted);">+ Bonus Accuracy</td>
                                    <td>Rp 0</td>
                                </tr>
                            <?php endif; ?>
                            <tr style="border-top:1px solid var(--border);">
                                <td style="padding-top:.6rem;font-weight:600;">Total Take Home Pay</td>
                                <td style="padding-top:.6rem;" class="thp-total">Rp <?php echo number_format($thpData['take_home_pay'],0,',','.'); ?></td>
                            </tr>
                        </table>
                        <p style="font-size:0.72rem;color:var(--muted);margin-top:.8rem;margin-bottom:0;">
                            Override method <code>hitungTakeHomePay()</code> dari class <code><?php echo $staff['jenis_staff']; ?></code>
                        </p>
                    <?php else: ?>
                        <p style="color:var(--muted);font-size:.83rem;">Data THP tidak tersedia.</p>
                    <?php endif; ?>
                </div>

                <!-- TAB: EVALUASI SOP -->
                <div id="tab-eval" class="tab-pane">
                    <?php if (!empty($eval)): ?>
                        <?php
                            $skor = $eval['skor_total'];
                            if ($skor >= 85) { $kelasEval = 'eval-lulus'; $labelEval = 'LULUS'; }
                            elseif ($skor >= 75) { $kelasEval = 'eval-cond'; $labelEval = 'LULUS KONDISIONAL'; }
                            else { $kelasEval = 'eval-gagal'; $labelEval = 'TIDAK LULUS'; }
                        ?>
                        <div style="display:flex;align-items:center;gap:.7rem;margin-bottom:.9rem;">
                            <span class="<?php echo $kelasEval; ?>"><?php echo $labelEval; ?></span>
                            <span style="font-size:1.1rem;font-weight:700;color:var(--brand);"><?php echo $skor; ?> / 100</span>
                        </div>
                        <div style="margin-bottom:.5rem;">
                            <?php foreach ($eval['detail'] as $det): ?>
                                <div class="eval-row">
                                    <div>
                                        <div style="font-weight:500;"><?php echo $det['nama_kriteria']; ?></div>
                                        <?php if (!empty($det['detail'])): ?>
                                            <div style="font-size:.75rem;color:var(--muted);"><?php echo $det['detail']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <span class="eval-skor"><?php echo $det['skor']; ?> &nbsp;<small style="font-weight:400;color:var(--muted);"><?php echo $det['status']; ?></small></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <p style="font-size:0.72rem;color:var(--muted);margin-top:.6rem;margin-bottom:0;">
                            Override method <code>evaluasiSOPKerja()</code> dari class <code><?php echo $staff['jenis_staff']; ?></code>
                        </p>
                    <?php else: ?>
                        <p style="color:var(--muted);font-size:.83rem;">Data evaluasi tidak tersedia.</p>
                    <?php endif; ?>
                </div>

            </div><!-- /card body -->
        </div><!-- /card kanan -->
    </div><!-- /panel-wrap -->

<?php endif; // end staffData success ?>

<?php

/* ============================================================
   MODE: LIST
   ============================================================ */
else: // mode == list

    $stats      = $staffManager->getStaffStatistics();
    $statsData  = ($stats['success'] ?? false) ? $stats['data'] : [];
    $totalStaff = ($stats['success'] ?? false) ? $stats['total'] : 0;
    $staffResult = $staffManager->getAllStaff();
    $staffList   = ($staffResult['success'] && count($staffResult['data']) > 0) ? $staffResult['data'] : [];
?>

    <!-- OOP BANNER -->
    <div class="oop-banner">
        <div class="icon"><i class="bi bi-diagram-3-fill"></i></div>
        <div>
            <h6>Arsitektur OOP – Modul STAFF</h6>
            <p>
                <span class="pill">Abstract Class</span> <code>StaffLogistik</code> dengan atribut enkapsulasi:
                <code>id_staff</code>, <code>namaLengkap</code>, <code>gajiPokok</code>, <code>jamKerja</code>.
                Tiga subclass mewarisi dan meng-override method polimorfisme
                <code>hitungTakeHomePay()</code> &amp; <code>evaluasiSOPKerja()</code>.
            </p>
        </div>
    </div>

    <!-- STAT CARDS -->
    <div class="stat-row">
        <div class="stat-card">
            <div class="num" style="color:#856404;"><?php echo $statsData['SupirTruk'] ?? 0; ?></div>
            <div class="lbl">Sopir Truk</div>
            <span class="badge-type badge-supir">SupirTruk</span>
        </div>
        <div class="stat-card">
            <div class="num" style="color:var(--brand);"><?php echo $statsData['AdminGudang'] ?? 0; ?></div>
            <div class="lbl">Admin Gudang</div>
            <span class="badge-type badge-admin">AdminGudang</span>
        </div>
        <div class="stat-card">
            <div class="num" style="color:var(--accent);"><?php echo $statsData['KurirMotor'] ?? 0; ?></div>
            <div class="lbl">Kurir Motor</div>
            <span class="badge-type badge-kurir">KurirMotor</span>
        </div>
        <div class="stat-card">
            <div class="num"><?php echo $totalStaff; ?></div>
            <div class="lbl">Total Staff</div>
        </div>
    </div>

    <!-- SEARCH -->
    <div class="search-bar">
        <input type="text" id="searchInput" placeholder="&#xF52A; Cari nama atau ID staff…">
        <select id="filterJenis">
            <option value="">Semua Subclass</option>
            <option value="SupirTruk">SupirTruk</option>
            <option value="AdminGudang">AdminGudang</option>
            <option value="KurirMotor">KurirMotor</option>
        </select>
    </div>

    <!-- TABLE -->
    <div class="card-box">
        <div class="card-box-header">
            <i class="bi bi-list-ul"></i>
            Daftar Instance – Subclass dari <code>StaffLogistik</code>
            <span id="count-label" style="margin-left:auto;font-weight:400;font-size:0.78rem;color:var(--muted);">
                <?php echo count($staffList); ?> staff
            </span>
        </div>
        <table class="staff-table">
            <thead>
                <tr>
                    <th>ID Code</th>
                    <th>Nama Lengkap</th>
                    <th>Subclass / Jenis</th>
                    <th>Gaji Pokok</th>
                    <th>Jam Kerja</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="staffTableBody">
                <?php if (count($staffList) > 0):
                    $badgeClass = ['SupirTruk' => 'badge-supir', 'AdminGudang' => 'badge-admin', 'KurirMotor' => 'badge-kurir'];
                    foreach ($staffList as $s):
                        $bc = $badgeClass[$s['jenis_staff']] ?? 'badge-admin';
                ?>
                    <tr data-nama="<?php echo strtolower(htmlspecialchars($s['nama_lengkap'])); ?>"
                        data-id="<?php echo strtolower(htmlspecialchars($s['id_staff_code'])); ?>"
                        data-jenis="<?php echo $s['jenis_staff']; ?>"
                        onclick="window.location='?mode=view&id=<?php echo $s['id_staff']; ?>'">
                        <td class="id-code"><?php echo htmlspecialchars($s['id_staff_code']); ?></td>
                        <td class="nama"><?php echo htmlspecialchars($s['nama_lengkap']); ?></td>
                        <td><span class="jenis-badge <?php echo $bc; ?>"><?php echo $s['jenis_staff']; ?></span></td>
                        <td>Rp <?php echo number_format($s['gaji_pokok'],0,',','.'); ?></td>
                        <td><?php echo $s['jam_kerja']; ?> jam</td>
                        <td style="color:var(--brand);font-size:.8rem;white-space:nowrap;">
                            Lihat detail <i class="bi bi-chevron-right" style="font-size:.7rem;"></i>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr class="empty-row">
                        <td colspan="6"><i class="bi bi-inbox"></i> Belum ada data staff.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php endif; // end mode list ?>
</div><!-- /page-wrap -->

<script>
/* ---- Filter tabel ---- */
(function() {
    const search = document.getElementById('searchInput');
    const filter = document.getElementById('filterJenis');
    const label  = document.getElementById('count-label');
    if (!search) return;

    function run() {
        const q  = search.value.toLowerCase().trim();
        const jf = filter.value;
        const rows = document.querySelectorAll('#staffTableBody tr[data-nama]');
        let visible = 0;
        rows.forEach(r => {
            const nm = r.dataset.nama || '';
            const id = r.dataset.id  || '';
            const jn = r.dataset.jenis || '';
            const ok = (!q || nm.includes(q) || id.includes(q)) && (!jf || jn === jf);
            r.style.display = ok ? '' : 'none';
            if (ok) visible++;
        });
        if (label) label.textContent = visible + ' staff';
    }

    search.addEventListener('input', run);
    filter.addEventListener('change', run);
})();

/* ---- Tab switcher ---- */
function switchTab(btn, paneId) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    const pane = document.getElementById(paneId);
    if (pane) pane.classList.add('active');
}
</script>
</body>
</html>