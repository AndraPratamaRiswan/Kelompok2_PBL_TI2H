<?php
// fitur 2, data dummy kartu statistik
$statistik = [
    ['label' => 'Total Pesanan', 'nilai' => '140', 'tema' => 'soft'],
    ['label' => 'Pesanan Diproses', 'nilai' => '12', 'tema' => 'dark'],
    ['label' => 'Pesanan Selesai', 'nilai' => '136', 'tema' => 'soft'],
    ['label' => 'Total Pendapatan', 'nilai' => 'Rp 1M', 'tema' => 'dark'],
];

// fitur 3, data dummy tren mingguan
$trenPesanan = [
    ['hari' => 'Sen', 'tinggi' => 58],
    ['hari' => 'Sel', 'tinggi' => 68],
    ['hari' => 'Rab', 'tinggi' => 46],
    ['hari' => 'Kam', 'tinggi' => 58],
    ['hari' => 'Jum', 'tinggi' => 29],
    ['hari' => 'Sab', 'tinggi' => 72],
    ['hari' => 'Ming', 'tinggi' => 44],
];

// fitur 3, data dummy menu terlaris
$menuTerlaris = [
    ['nama' => 'Ayam Goreng', 'persentase' => 86],
    ['nama' => 'Nasi Kuning', 'persentase' => 76],
    ['nama' => 'Ayam Geprek', 'persentase' => 63],
    ['nama' => 'Nasi Rendang', 'persentase' => 48],
    ['nama' => 'Nasi Tumpeng', 'persentase' => 34],
];

// fitur 4, data dummy daftar pesanan
$daftarPesanan = [
    [
        'nomor' => 'Pesanan 1',
        'telepon' => '0812-6786-0980',
        'pelanggan' => 'Ibu Narsis',
        'menu' => 'Nasi Ayam Bakar (50 box)',
        'tanggal' => '12 Okt 2026',
        'pembayaran' => 'Lunas',
        'status' => 'Dikonfirmasi',
    ],
    [
        'nomor' => 'Pesanan 2',
        'telepon' => '0812-6786-0980',
        'pelanggan' => 'Ibu Sulami',
        'menu' => 'Nasi Ayam Bakar (50 box)',
        'tanggal' => '12 Okt 2026',
        'pembayaran' => 'Lunas',
        'status' => 'Dikirim',
    ],
    [
        'nomor' => 'Pesanan 3',
        'telepon' => '0812-6786-0980',
        'pelanggan' => 'Ibu Sulami',
        'menu' => 'Nasi Ayam Bakar (50 box)',
        'tanggal' => '12 Okt 2026',
        'pembayaran' => 'DP',
        'status' => 'Diproses',
    ],
    [
        'nomor' => 'Pesanan 4',
        'telepon' => '0812-6786-0980',
        'pelanggan' => 'Ibu Sulami',
        'menu' => 'Nasi Ayam Bakar (50 box)',
        'tanggal' => '12 Okt 2026',
        'pembayaran' => 'DP',
        'status' => 'Diproses',
    ],
    [
        'nomor' => 'Pesanan 5',
        'telepon' => '0812-6786-0980',
        'pelanggan' => 'Ibu Sulami',
        'menu' => 'Nasi Ayam Bakar (50 box)',
        'tanggal' => '12 Okt 2026',
        'pembayaran' => 'Lunas',
        'status' => 'Dikonfirmasi',
    ],
    [
        'nomor' => 'Pesanan 6',
        'telepon' => '0812-6786-0980',
        'pelanggan' => 'Ibu Sulami',
        'menu' => 'Nasi Ayam Bakar (50 box)',
        'tanggal' => '12 Okt 2026',
        'pembayaran' => 'DP',
        'status' => 'Diproses',
    ],
];

// fitur 1, konfigurasi header/navigasi
$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
require __DIR__ . '/includes/header.php';
?>
<!-- fitur 1, judul dashboard -->
<section class="page-heading">
    <div>
        <p class="eyebrow">RINGKASAN TOKO</p>
        <h1>Dashboard</h1>
        <p class="page-description">Pantau pesanan dan aktivitas SIKAT hari ini.</p>
    </div>
    <!-- fitur 5, tombol cetak laporan (demo) -->
    <button class="button button-report" id="printReport" type="button">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 8V3h10v5M7 17H5a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2M7 14h10v7H7z"/><path d="M17 11h.01"/></svg>
        Cetak Laporan
    </button>
</section>

<!-- fitur 2, kartu statistik -->
<section class="stats-grid" aria-label="Ringkasan statistik">
    <?php foreach ($statistik as $stat): ?>
        <article class="stat-card stat-card--<?= htmlspecialchars($stat['tema']) ?>">
            <p><?= htmlspecialchars($stat['label']) ?></p>
            <strong><?= htmlspecialchars($stat['nilai']) ?></strong>
        </article>
    <?php endforeach; ?>
</section>

<!-- fitur 3, analisis dashboard -->
<section class="analytics-grid" aria-label="Analisis pesanan dan menu">
    <!-- fitur 3, grafik tren pesanan -->
    <article class="panel trend-panel">
        <div class="panel-heading">
            <div>
                <h2>Tren Pesanan Mingguan</h2>
                <p class="panel-subtitle">Jumlah pesanan selama satu minggu</p>
            </div>
            <span class="mini-tag">Minggu ini</span>
        </div>

        <div class="bar-chart" role="img" aria-label="Grafik batang tren pesanan selama Senin sampai Minggu">
            <?php foreach ($trenPesanan as $data): ?>
                <div class="bar-chart__item">
                    <div class="bar-chart__track">
                        <span class="bar-chart__bar" style="height: <?= (int) $data['tinggi'] ?>%"></span>
                    </div>
                    <span class="bar-chart__label"><?= htmlspecialchars($data['hari']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </article>

    <!-- fitur 3, daftar menu terlaris -->
    <article class="panel popular-panel">
        <div class="panel-heading">
            <div>
                <h2>Menu Terlaris</h2>
                <p class="panel-subtitle">Menu yang paling sering dipesan</p>
            </div>
        </div>
        <div class="popular-list">
            <?php foreach ($menuTerlaris as $menu): ?>
                <div class="popular-item">
                    <span class="popular-item__name"><?= htmlspecialchars($menu['nama']) ?></span>
                    <span class="popular-item__track" aria-label="Indeks popularitas <?= (int) $menu['persentase'] ?> persen">
                        <span style="width: <?= (int) $menu['persentase'] ?>%"></span>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </article>
</section>

<!-- fitur 4, daftar pesanan prototype -->
<section class="orders-panel" aria-labelledby="ordersTitle">
    <div class="orders-toolbar">
        <div class="orders-title-group">
            <div>
                <h2 id="ordersTitle">Daftar Pesanan</h2>
                <p class="panel-subtitle">Contoh tampilan pesanan terbaru</p>
            </div>
            
            <!-- fitur 4, filter bulan (demo) -->
            <label class="month-filter">
                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg>
                <select id="monthFilter" aria-label="Pilih bulan pesanan">
                    <option value="Oktober 2026">Oktober 2026</option>
                    <option value="September 2026">September 2026</option>
                    <option value="Agustus 2026">Agustus 2026</option>
                </select>
            </label>
        </div>
        
        <!-- fitur 4, tombol tambah dan lihat semua (demo) -->
        <div class="orders-actions">
            <button class="button button-primary button-small" type="button" data-toast="Fitur tambah pesanan dijadwalkan untuk tahap berikutnya.">+ Add</button>
            <button class="text-button" type="button" data-toast="Daftar lengkap pesanan belum diaktifkan.">Lihat Semua <span aria-hidden="true">→</span></button>
        </div>
    </div>

    <!-- fitur 4, baris dummy pesanan -->
    <div class="order-list">
        <?php foreach ($daftarPesanan as $pesanan): ?>
            <article class="order-row">
                <div class="order-info">
                    <strong class="order-number"><?= htmlspecialchars($pesanan['nomor']) ?></strong>
                    <div class="order-meta">
                        <span><?= htmlspecialchars($pesanan['telepon']) ?></span>
                        <span><?= htmlspecialchars($pesanan['pelanggan']) ?></span>
                    </div>
                    <p class="order-menu"><?= htmlspecialchars($pesanan['menu']) ?></p>
                    <time class="order-date" datetime="2026-10-12"><?= htmlspecialchars($pesanan['tanggal']) ?></time>
                </div>

                <!-- fitur 4, status pembayaran pesanan -->
                <span class="payment-badge payment-badge--<?= $pesanan['pembayaran'] === 'Lunas' ? 'paid' : 'dp' ?>">
                    <?= htmlspecialchars($pesanan['pembayaran']) ?>
                </span>

                <div class="order-actions">
                    <button class="button button-neutral" type="button" data-toast="Aksi status pesanan belum terhubung ke backend."><?= htmlspecialchars($pesanan['status']) ?></button>
                    <button class="button button-neutral" type="button" data-toast="Detail pesanan akan tersedia pada tahap fitur pesanan.">Detail</button>
                    <button class="button button-neutral" type="button" data-toast="Edit pesanan belum diaktifkan.">Edit</button>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
    <!-- fitur 4, penanda data pesanan dummy -->
    <p class="prototype-note"><span class="prototype-dot"></span> Data dummy, belum terhubung ke database PostgreSQL.</p>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>