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

// daftar pesanan diambil dari database (sebelumnya data dummy)
require_once __DIR__ . '/includes/koneksi.php';
require_once __DIR__ . '/includes/queries/pesanan_crud.php';
require_once __DIR__ . '/includes/pesanan_helper.php';

$daftarPesanan = [];
$errorPesanan = '';
try {
    foreach (array_slice(psnDaftar($pdo), 0, 20) as $baris) {
        $daftarPesanan[] = [
            'id' => (int) $baris['id_pesanan'],
            'kode' => psnKode((int) $baris['id_pesanan']),
            'nomor' => $baris['no_hp'],
            'nama' => $baris['nama_pembeli'],
            'ringkasan_menu' => $baris['ringkasan_menu'],
            'status_pesanan' => $baris['status_pesanan'],
            'status_pembayaran' => $baris['status_pembayaran'],
            'tanggal' => psnTanggalIndo($baris['tanggal_pengiriman']),
        ];
    }
} catch (PDOException $e) {
    $errorPesanan = 'Data pesanan gagal dibaca. Detail: ' . $e->getMessage();
}

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

<!-- daftar pesanan dummy, tombol dan jalan -->
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
            <a class="button button-primary" href="pesanan_tambah.php">+ Add</a>
            <a class="text-button" href="pesanan_daftar.php">Lihat Semua</a>
        </div>
    </div>

    <div class="order-list">
        <?php if ($errorPesanan !== ''): ?>
            <div class="form-message form-message--error"><?= htmlspecialchars($errorPesanan, ENT_QUOTES, 'UTF-8') ?></div>
        <?php elseif (empty($daftarPesanan)): ?>
            <p class="empty-state">Belum ada pesanan.</p>
        <?php endif; ?>
        <?php foreach ($daftarPesanan as $pesanan): ?>
            <article class="order-row" style="grid-template-columns: minmax(210px, 1fr) 96px minmax(240px, 1fr);">
                <div class="order-info">
                    <strong>Pesanan <?= htmlspecialchars($pesanan['kode'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <span><?= htmlspecialchars($pesanan['nomor'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($pesanan['nama'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span><?= htmlspecialchars($pesanan['ringkasan_menu'], ENT_QUOTES, 'UTF-8') ?></span>
                    <small><?= htmlspecialchars($pesanan['tanggal'], ENT_QUOTES, 'UTF-8') ?></small>
                </div>
                <span class="status-pill <?= psnKelasBayar((string) $pesanan['status_pembayaran']) ?>">
                    <?= htmlspecialchars($pesanan['status_pembayaran'], ENT_QUOTES, 'UTF-8') ?>
                </span>
                <div class="order-buttons">
                    <span class="process-status"><?= htmlspecialchars($pesanan['status_pesanan'], ENT_QUOTES, 'UTF-8') ?></span>
                    <a class="button button-neutral" href="pesanan_detail.php?id=<?= (int) $pesanan['id'] ?>">Detail</a>
                    <a class="button button-neutral" href="pesanan_ubah.php?id=<?= (int) $pesanan['id'] ?>">Edit</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>