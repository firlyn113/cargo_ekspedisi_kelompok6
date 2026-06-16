<?php
require_once 'AbstractPelanggan.php';

class PelangganVIP extends AbstractPelanggan {
    // Additional attributes specific to VIP
    private $akses_layanan_prioritas;
    private $personal_assistant;
    
    public function __construct($id_pelanggan_code, $nama_lengkap, $akses_layanan_prioritas = true, $personal_assistant = null) {
        parent::__construct($id_pelanggan_code, $nama_lengkap);
        $this->jenis_pelanggan = 'VIP';
        $this->akses_layanan_prioritas = $akses_layanan_prioritas;
        $this->personal_assistant = $personal_assistant;
        
        // VIP mendapatkan bonus poin awal
        $this->poin_reward = 100;
    }
    
    // Polymorphism implementation - Diskon lebih besar untuk VIP
    public function hitungDiskonPengiriman($total_biaya) {
        $diskon = 0;
        
        // Diskon 15% untuk VIP
        $diskon = $total_biaya * 0.15;
        
        // Diskon tambahan berdasarkan poin (1 poin = 2000 rupiah untuk VIP)
        $diskon_poin = min($this->poin_reward * 2000, $total_biaya * 0.2);
        $diskon += $diskon_poin;
        
        return min($diskon, $total_biaya * 0.35); // Maksimal diskon 35% untuk VIP
    }
    
    // Polymorphism implementation - Benefit lebih banyak untuk VIP
    public function dapatkanBenefitTambahan() {
        $benefits = [];
        
        // Benefit VIP
        $benefits[] = "✅ Akses layanan prioritas (tanpa antri)";
        
        if ($this->personal_assistant) {
            $benefits[] = "👤 Personal Assistant: " . $this->personal_assistant;
        }
        
        $benefits[] = "🎁 Free packing kayu untuk semua pengiriman";
        $benefits[] = "📊 Laporan pengiriman bulanan analitik";
        $benefits[] = "🚚 Pickup gratis (minimal 2kg)";
        $benefits[] = "⭐ Poin reward x2 (2 poin per 100k)";
        
        return $benefits;
    }
    
    // Getter & Setter (Encapsulation)
    public function getAksesLayananPrioritas() {
        return $this->akses_layanan_prioritas;
    }
    
    public function setAksesLayananPrioritas($status) {
        $this->akses_layanan_prioritas = $status;
    }
    
    public function getPersonalAssistant() {
        return $this->personal_assistant;
    }
    
    public function setPersonalAssistant($assistant) {
        $this->personal_assistant = $assistant;
    }
    
    // Override tambah transaksi dengan perolehan poin double
    public function tambahTransaksi($nominal) {
        $this->total_transaksi_bulan_ini += $nominal;
        $this->poin_reward += floor($nominal / 100000) * 2; // 2 poin per 100k untuk VIP
    }
    
    // Method untuk mendapatkan data lengkap sebagai array (untuk keperluan CRUD)
    public function toArray() {
        return [
            'id_pelanggan' => $this->id_pelanggan,
            'id_pelanggan_code' => $this->id_pelanggan_code,
            'nama_lengkap' => $this->nama_lengkap,
            'jenis_pelanggan' => $this->jenis_pelanggan,
            'total_transaksi_bulan_ini' => $this->total_transaksi_bulan_ini,
            'poin_reward' => $this->poin_reward,
            'created_at' => $this->created_at,
            'akses_layanan_prioritas' => $this->akses_layanan_prioritas,
            'personal_assistant' => $this->personal_assistant
        ];
    }
    
    // Method khusus VIP - Request layanan prioritas
    public function requestLayananPrioritas() {
        if ($this->akses_layanan_prioritas) {
            return [
                'status' => 'success',
                'message' => "✅ Layanan prioritas aktif! Anda akan dilayani tanpa antrian.",
                'personal_assistant' => $this->personal_assistant ? "Dilayani oleh: {$this->personal_assistant}" : "Dilayani oleh tim VIP"
            ];
        }
        return [
            'status' => 'failed',
            'message' => "❌ Layanan prioritas tidak aktif. Silakan upgrade ke VIP Premium!",
            'action' => 'Hubungi customer service untuk upgrade'
        ];
    }
    
    // Method khusus VIP - Cek status layanan prioritas
    public function cekStatusPrioritas() {
        $status = $this->akses_layanan_prioritas ? 'Aktif' : 'Tidak Aktif';
        $assistant = $this->personal_assistant ?: 'Belum ditentukan';
        
        return [
            'status_prioritas' => $status,
            'personal_assistant' => $assistant,
            'poin_reward' => $this->poin_reward,
            'total_transaksi' => $this->total_transaksi_bulan_ini
        ];
    }
    
    // Method khusus VIP - Request personal assistant
    public function requestPersonalAssistant($nama_assistant) {
        if (empty($nama_assistant)) {
            return [
                'status' => 'failed',
                'message' => "❌ Nama Personal Assistant tidak boleh kosong!"
            ];
        }
        
        $this->personal_assistant = $nama_assistant;
        return [
            'status' => 'success',
            'message' => "✅ Personal Assistant '{$nama_assistant}' berhasil ditugaskan!",
            'personal_assistant' => $this->personal_assistant
        ];
    }
    
    // Method khusus VIP - Hitung benefit nilai
    public function hitungNilaiBenefit() {
        $nilai = 0;
        
        // Nilai dari diskon (asumsi transaksi 1 juta)
        $diskon = $this->hitungDiskonPengiriman(1000000);
        $nilai += $diskon;
        
        // Nilai dari poin reward (1 poin = 2000)
        $nilai += $this->poin_reward * 2000;
        
        // Nilai dari free packing (estimasi 50k per pengiriman)
        $nilai += 50000;
        
        // Nilai dari pickup gratis (estimasi 25k per pickup)
        $nilai += 25000;
        
        return [
            'total_nilai_benefit' => $nilai,
            'formatted' => 'Rp ' . number_format($nilai, 0, ',', '.'),
            'detail' => [
                'diskon' => 'Rp ' . number_format($diskon, 0, ',', '.'),
                'poin_reward' => $this->poin_reward . ' poin (Rp ' . number_format($this->poin_reward * 2000, 0, ',', '.') . ')',
                'free_packing' => 'Rp 50.000',
                'pickup_gratis' => 'Rp 25.000'
            ]
        ];
    }
    
    // Method khusus VIP - Cek kelayakan upgrade ke VIP Plus
    public function cekKelayakanUpgrade() {
        $kriteria = [
            'min_transaksi' => 5000000,
            'min_poin' => 500,
            'min_bulan_aktif' => 3
        ];
        
        $layak = true;
        $pesan = [];
        
        if ($this->total_transaksi_bulan_ini < $kriteria['min_transaksi']) {
            $layak = false;
            $pesan[] = "❌ Total transaksi kurang dari Rp " . number_format($kriteria['min_transaksi'], 0, ',', '.');
        }
        
        if ($this->poin_reward < $kriteria['min_poin']) {
            $layak = false;
            $pesan[] = "❌ Poin reward kurang dari {$kriteria['min_poin']} poin";
        }
        
        if ($layak) {
            return [
                'status' => 'success',
                'message' => "✅ Selamat! Anda layak untuk upgrade ke VIP Plus!",
                'benefit_tambahan' => [
                    'Diskon 25%',
                    'Personal Assistant 24/7',
                    'Free shipping unlimited',
                    'Akses lounge bandara'
                ]
            ];
        }
        
        return [
            'status' => 'failed',
            'message' => "❌ Belum memenuhi syarat upgrade ke VIP Plus",
            'kriteria' => $kriteria,
            'kekurangan' => $pesan
        ];
    }
}
?>