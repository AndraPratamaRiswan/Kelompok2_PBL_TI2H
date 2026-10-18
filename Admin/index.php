<?php
// fitur 2, data dummy kartu statistik
$statistik = [
    ['label' => 'Total Pesanan', 'nilai' => '140', 'tema' => 'soft'],
    ['label' => 'Pesanan Diproses', 'nilai' => '12', 'tema' => 'dark'],
    ['label' => 'Pesanan Selesai', 'nilai' => '136', 'tema' => 'soft'],
    ['label' => 'Total Pendapatan', 'nilai' => 'Rp 1M', 'tema' => 'dark'],
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