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