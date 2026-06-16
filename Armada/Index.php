<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Armada - Cargo Ekspedisi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */
        .sidebar {
            width: 250px;
            background: #1a2332;
            color: #fff;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar .logo {
            padding: 0 20px 30px 20px;
            border-bottom: 1px solid #2a3a4a;
            margin-bottom: 20px;
        }

        .sidebar .logo h1 {
            font-size: 22px;
            font-weight: 700;
        }

        .sidebar .logo small {
            font-size: 12px;
            color: #8899aa;
            display: block;
            margin-top: 4px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0 15px;
        }

        .sidebar ul li {
            margin-bottom: 4px;
        }

        .sidebar ul li a {
            display: block;
            padding: 12px 16px;
            color: #b0c4d8;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background: #2a3a4a;
            color: #fff;
        }

        .sidebar ul li a.active {
            background: #2d7aff;
        }

        /* MAIN CONTENT */
        .main-content {
            margin-left: 250px;
            padding: 30px 40px;
            flex: 1;
        }

        /* HEADER */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-header h2 {
            font-size: 24px;
            font-weight: 600;
            color: #1a2332;
        }

        .page-header p {
            color: #6b7a8a;
            font-size: 14px;
            margin-top: 4px;
        }

        /* TOMBOL TAMBAH */
        .btn-primary {
            background: #2d7aff;
            color: #fff;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary:hover {
            background: #1a5fd9;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
        }

        .btn-edit {
            background: #ffc107;
            color: #1a2332;
        }

        .btn-edit:hover {
            background: #e0a800;
        }

        .btn-delete {
            background: #dc3545;
            color: #fff;
        }

        .btn-delete:hover {
            background: #b02a37;
        }

        /* TABLE */
        .table-container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            margin-top: 20px;
        }

        .table-wrapper {
            overflow-x: auto;
            padding: 0 20px 20px 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        thead {
            background: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
        }

        thead th {
            padding: 14px 16px;
            text-align: left;
            font-weight: 600;
            color: #4a5a6a;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }

        tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid #f0f0f0;
            color: #1a2332;
            vertical-align: middle;
        }

        tbody tr:hover {
            background: #f8faff;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-darat {
            background: #e3f2fd;
            color: #0d6efd;
        }

        .badge-laut {
            background: #e0f7fa;
            color: #00838f;
        }

        .badge-udara {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .badge-available {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .badge-maintenance {
            background: #fff3e0;
            color: #e65100;
        }

        .badge-booked {
            background: #fce4ec;
            color: #c62828;
        }

        .text-center {
            text-align: center;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7a8a;
        }

        .empty-state .icon {
            font-size: 48px;
            margin-bottom: 16px;
            display: block;
        }

        .empty-state h3 {
            font-size: 18px;
            color: #1a2332;
            margin-bottom: 8px;
        }

        .empty-state p {
            font-size: 14px;
            margin-bottom: 20px;
        }

        .action-group {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            .main-content {
                margin-left: 200px;
                padding: 20px;
            }
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
        }

        @media (max-width: 576px) {
            .sidebar {
                width: 60px;
            }
            .sidebar .logo h1,
            .sidebar .logo small,
            .sidebar ul li a span {
                display: none;
            }
            .sidebar ul li a {
                padding: 12px;
                text-align: center;
            }
            .main-content {
                margin-left: 60px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="logo">
            <h1>🚚 Cargo Ekspedisi</h1>
            <small>Logistik System</small>
        </div>
        <ul>
            <li><a href="#">📊 Dashboard</a></li>
            <li><a href="#" class="active">🚛 Armada</a></li>
            <li><a href="#">📦 Cargo</a></li>
            <li><a href="#">👤 Pelanggan</a></li>
            <li><a href="#">💳 Pembayaran</a></li>
            <li><a href="#">👥 Staff</a></li>
        </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">

        <!-- HEADER -->
        <div class="page-header">
            <div>
                <h2>Manajemen Armada</h2>
                <p>Kelola data armada - Truk Darat | Kapal Laut | Pesawat Kargo</p>
            </div>
            <button class="btn-primary" onclick="tambahArmada()">➕ Tambah Armada Baru</button>
        </div>

        <!-- TABLE -->
        <div class="table-container">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Armada</th>
                            <th>Jenis</th>
                            <th>Kapasitas (Kg)</th>
                            <th>Status</th>
                            <th>Lokasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="armadaTableBody">
                        <!-- Data akan diisi oleh JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <script>
        // DATA DUMMY ARMADA
        const dataArmada = [{
            kode: 'TRK-001',
            nama: 'Truk Kontainer 40ft',
            jenis: 'Darat',
            kapasitas: 25000,
            status: 'Tersedia',
            lokasi: 'Jakarta'
        }, {
            kode: 'TRK-002',
            nama: 'Truk Box 8m',
            jenis: 'Darat',
            kapasitas: 8000,
            status: 'Dalam Perawatan',
            lokasi: 'Surabaya'
        }, {
            kode: 'KPL-001',
            nama: 'Kapal Container KM Nusantara',
            jenis: 'Laut',
            kapasitas: 500000,
            status: 'Tersedia',
            lokasi: 'Pelabuhan Tanjung Priok'
        }, {
            kode: 'KPL-002',
            nama: 'Kapal Roro Bahari',
            jenis: 'Laut',
            kapasitas: 300000,
            status: 'Disewa',
            lokasi: 'Pelabuhan Makassar'
        }, {
            kode: 'PSW-001',
            nama: 'Boeing 747-8F',
            jenis: 'Udara',
            kapasitas: 140000,
            status: 'Tersedia',
            lokasi: 'Bandara Soekarno-Hatta'
        }, {
            kode: 'PSW-002',
            nama: 'Airbus A330-200F',
            jenis: 'Udara',
            kapasitas: 70000,
            status: 'Disewa',
            lokasi: 'Bandara Juanda'
        }];

        // BADGE COLOR
        function getBadgeJenis(jenis) {
            const map = {
                'Darat': 'badge-darat',
                'Laut': 'badge-laut',
                'Udara': 'badge-udara'
            };
            return map[jenis] || 'badge-darat';
        }

        function getBadgeStatus(status) {
            const map = {
                'Tersedia': 'badge-available',
                'Dalam Perawatan': 'badge-maintenance',
                'Disewa': 'badge-booked'
            };
            return map[status] || 'badge-available';
        }

        // RENDER TABLE
        function renderArmada(data) {
            const tbody = document.getElementById('armadaTableBody');

            if (!data || data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <span class="icon">🚛</span>
                                <h3>Belum ada data armada</h3>
                                <p>Klik tombol "Tambah Armada Baru" untuk menambahkan</p>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            let rows = '';
            data.forEach(item => {
                rows += `
                    <tr>
                        <td><strong>${item.kode}</strong></td>
                        <td>${item.nama}</td>
                        <td><span class="badge ${getBadgeJenis(item.jenis)}">${item.jenis}</span></td>
                        <td>${item.kapasitas.toLocaleString()}</td>
                        <td><span class="badge ${getBadgeStatus(item.status)}">${item.status}</span></td>
                        <td>${item.lokasi}</td>
                        <td>
                            <div class="action-group">
                                <button class="btn-sm btn-edit" onclick="editArmada('${item.kode}')">✏️ Edit</button>
                                <button class="btn-sm btn-delete" onclick="hapusArmada('${item.kode}')">🗑️ Hapus</button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = rows;
        }

        // FUNGSI TAMBAH
        function tambahArmada() {
            alert('Fungsi Tambah Armada akan muncul di sini (modal/form)');
            // Di sini nanti kamu bisa buka modal form tambah armada
        }

        // FUNGSI EDIT
        function editArmada(kode) {
            alert(`Edit armada dengan kode: ${kode}`);
        }

        // FUNGSI HAPUS
        function hapusArmada(kode) {
            if (confirm(`Yakin ingin menghapus armada dengan kode ${kode}?`)) {
                const index = dataArmada.findIndex(item => item.kode === kode);
                if (index !== -1) {
                    dataArmada.splice(index, 1);
                    renderArmada(dataArmada);
                }
            }
        }

        // RENDER AWAL
        renderArmada(dataArmada);
    </script>

</body>
</html>