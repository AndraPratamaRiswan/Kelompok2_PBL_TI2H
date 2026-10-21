<?php
// data dummy untuk kartu statistik, nanti konek ke database
$statistik = [
    ['label' => 'Total Pesanan', 'nilai' => '140', 'tema' => 'soft'],
    ['label' => 'Pesanan Diproses', 'nilai' => '12', 'tema' => 'dark'],
    ['label' => 'Pesanan Selesai', 'nilai' => '136', 'tema' => 'soft'],
    ['label' => 'Total Pendapatan', 'nilai' => 'Rp 1.000.000', 'tema' => 'dark'],
];

// angka tinggi cuman buat tampilan tinggi batang grafik
$trenPesanan = [
    ['hari' => 'Sen', 'tinggi' => 58],
    ['hari' => 'Sel', 'tinggi' => 68],
    ['hari' => 'Rab', 'tinggi' => 46],
    ['hari' => 'Kam', 'tinggi' => 58],
    ['hari' => 'Jum', 'tinggi' => 29],
    ['hari' => 'Sab', 'tinggi' => 72],
    ['hari' => 'Ming', 'tinggi' => 44],
];

// daftar menu yang laris, persentase ngatur panjang bar nya
$menuTerlaris = [
    ['nama' => 'Ayam Goreng', 'persentase' => 86],
    ['nama' => 'Nasi Kuning', 'persentase' => 76],
    ['nama' => 'Ayam Geprek', 'persentase' => 63],
    ['nama' => 'Nasi Rendang', 'persentase' => 48],
    ['nama' => 'Nasi Tumpeng', 'persentase' => 34],
];

// pesanan dummy, tombol blon ada yang jalan
$daftarPesanan = [
    ['id' => 'SKKT-001', 'nomor' => '0812-6786-0980', 'nama' => 'Ibu Narsis', 'menu' => 'Nasi Ayam Bakar (50 box)', 'tanggal' => '12 Okt 2026', 'status' => 'Lunas', 'proses' => 'Dikonfirmasi'],
    ['id' => 'SKKT-002', 'nomor' => '0812-6786-0981', 'nama' => 'Ibu Sulami', 'menu' => 'Nasi Ayam Bakar (50 box)', 'tanggal' => '12 Okt 2026', 'status' => 'DP', 'proses' => 'Diproses'],
    ['id' => 'SKKT-003', 'nomor' => '0812-6786-0982', 'nama' => 'Pak Yanto', 'menu' => 'Nasi Rendang (30 box)', 'tanggal' => '13 Okt 2026', 'status' => 'Lunas', 'proses' => 'Dikirim'],
    ['id' => 'SKKT-004', 'nomor' => '0812-6786-0983', 'nama' => 'Bu Ratna', 'menu' => 'Nasi Kuning (20 box)', 'tanggal' => '14 Okt 2026', 'status' => 'DP', 'proses' => 'Dikonfirmasi'],
    ['id' => 'SKKT-005', 'nomor' => '0812-6786-0984', 'nama' => 'Ibu Siti', 'menu' => 'Snack Box Premium (25 box)', 'tanggal' => '15 Okt 2026', 'status' => 'Lunas', 'proses' => 'Selesai'],
    ['id' => 'SKKT-006', 'nomor' => '0812-6786-0985', 'nama' => 'Pak Budi', 'menu' => 'Tumpeng Mini (5 box)', 'tanggal' => '16 Okt 2026', 'status' => 'DP', 'proses' => 'Diproses'],
];

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
require __DIR__ . '/includes/header.php';
?>

<!-- bagian judul halaman, dan tombol cetak laporan (blom jalan) -->
<section class="page-heading">
    <div>
        <h1>Dashboard</h1>
        <p class="page-description">Pantau pesanan dan aktivitas SIKAT hari ini.</p>
    </div>
    <button class="button button-report" type="button">Cetak Laporan</button>
</section>

<!-- kartu statistik dijadiin array, biar ndak nulis berulang -->
<section class="stats-grid" aria-label="Ringkasan statistik">
    <?php foreach ($statistik as $stat): ?>
        <article class="stat-card stat-card--<?= htmlspecialchars($stat['tema']) ?>">
            <p><?= htmlspecialchars($stat['label']) ?></p>
            <strong><?= htmlspecialchars($stat['nilai']) ?></strong>
        </article>
    <?php endforeach; ?>
</section>

<!-- dua panel ringkasan, tren pesanan ama tren menu -->
<section class="analytics-grid">
    <article class="panel">
        <h2>Tren Pesanan Mingguan</h2>
        <div class="bar-chart">
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

    <article class="panel">
        <h2>Menu Terlaris</h2>
        <div class="popular-list">
            <?php foreach ($menuTerlaris as $menu): ?>
                <div class="popular-item">
                    <span class="popular-item__name"><?= htmlspecialchars($menu['nama']) ?></span>
                    <div class="popular-item__track">
                        <span style="width: <?= (int) $menu['persentase'] ?>%"></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </article>
</section>

<!-- daftar pesanan dummy -->
<section class="orders-panel">
    <div class="orders-toolbar">
        <div class="orders-title-group">
            <h2>Daftar Pesanan</h2>
            <select class="simple-select" aria-label="Bulan pesanan">
                <option>Oktober 2026</option>
                <option>September 2026</option>
                <option>Agustus 2026</option>
            </select>
        </div>
        <div class="orders-actions">
            <button class="button button-primary" type="button">+ Add</button>
            <button class="text-button" type="button">Lihat Semua</button>
        </div>
    </div>

    <div class="order-list">
        <?php foreach ($daftarPesanan as $pesanan): ?>
            <article class="order-row">
                <div class="order-info">
                    <strong>Pesanan <?= htmlspecialchars(substr($pesanan['id'], -3)) ?></strong>
                    <span><?= htmlspecialchars($pesanan['nomor']) ?> · <?= htmlspecialchars($pesanan['nama']) ?></span>
                    <span><?= htmlspecialchars($pesanan['menu']) ?></span>
                    <small><?= htmlspecialchars($pesanan['tanggal']) ?></small>
                </div>
                <span class="status-pill <?= $pesanan['status'] === 'Lunas' ? 'status-pill--paid' : 'status-pill--dp' ?>">
                    <?= htmlspecialchars($pesanan['status']) ?>
                </span>
                <div class="order-buttons">
                    <span class="process-status"><?= htmlspecialchars($pesanan['proses']) ?></span>
                    <button class="button button-neutral" type="button">Detail</button>
                    <button class="button button-neutral" type="button">Edit</button>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>