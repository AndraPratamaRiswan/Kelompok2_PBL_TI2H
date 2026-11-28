<?php
// Dashboard admin: semua angka, grafik, dan daftar pesanan dibaca langsung dari database.
require_once __DIR__ . '/includes/koneksi.php';
require_once __DIR__ . '/includes/queries/pesanan_daftar.php';
require_once __DIR__ . '/includes/queries/dashboard.php';
require_once __DIR__ . '/includes/pesanan_helper.php';

date_default_timezone_set('Asia/Jakarta');

$errorDashboard = '';
$statistik      = [];
$trenPesanan    = [];
$menuTerlaris   = [];
$daftarPesanan  = [];
$errorPesanan   = '';

// kartu statistik, tren pesanan mingguan, dan menu terlaris
try {
    $angka = dashStatistik($pdo);
    $statistik = [
        ['label' => 'Total Pesanan',     'nilai' => number_format((int) $angka['total_pesanan'], 0, ',', '.'),    'tema' => 'soft'],
        ['label' => 'Pesanan Diproses',  'nilai' => number_format((int) $angka['pesanan_diproses'], 0, ',', '.'), 'tema' => 'dark'],
        ['label' => 'Pesanan Selesai',   'nilai' => number_format((int) $angka['pesanan_selesai'], 0, ',', '.'),  'tema' => 'soft'],
        ['label' => 'Total Pendapatan',  'nilai' => psnRupiah($angka['total_pendapatan']),                        'tema' => 'dark'],
    ];
    $trenPesanan  = dashTrenMingguan($pdo);
    $menuTerlaris = dashMenuTerlaris($pdo, 5);
} catch (PDOException $e) {
    $errorDashboard = 'Data dashboard gagal dibaca. Detail: ' . $e->getMessage();
}

// daftar pesanan: hanya yang belum selesai
try {
    foreach (psnDaftar($pdo, '', '', true) as $baris) {
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

<!-- bagian judul halaman, dan tombol cetak laporan -->
<section class="page-heading">
    <div>
        <h1>Dashboard</h1>
        <p class="page-description">Pantau pesanan dan aktivitas SIKAT hari ini.</p>
    </div>
    <a class="button button-report" href="pesanan_laporan.php">Cetak Laporan</a>
</section>

<?php if ($errorDashboard !== ''): ?>
    <div class="form-message form-message--error"><?= psnE($errorDashboard) ?></div>
<?php endif; ?>

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
        <div class="chart-wrap">
            <canvas id="grafikMingguan"></canvas>
        </div>
        <?php if (!empty($trenPesanan)): ?>
            <p class="chart-note">Jumlah pesanan masuk <?= psnE(psnTanggalIndo($trenPesanan[0]['tanggal'])) ?> – <?= psnE(psnTanggalIndo($trenPesanan[6]['tanggal'])) ?>.</p>
        <?php endif; ?>
    </article>

    <article class="panel">
        <h2>Menu Terlaris</h2>
        <?php if (empty($menuTerlaris)): ?>
    <p class="chart-note">Belum ada data pesanan.</p>
<?php else: ?>
    <div class="chart-wrap">
        <canvas id="grafikMenu"></canvas>
    </div>
<?php endif; ?>
    </article>
</section>

<!-- daftar pesanan dari database, hanya yang belum selesai -->
<section class="orders-panel">
    <div class="orders-toolbar">
        <div class="orders-title-group">
            <h2>Daftar Pesanan</h2>
            <span class="process-status">Menampilkan pesanan yang belum selesai (<?= count($daftarPesanan) ?>)</span>
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
            <p class="empty-state">Tidak ada pesanan yang belum selesai.</p>
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
                    <a class="button button-neutral" href="pesanan_detail.php?id=<?= (int) $pesanan['id'] ?>&amp;asal=dashboard">Detail</a>
                    <a class="button button-neutral" href="pesanan_ubah.php?id=<?= (int) $pesanan['id'] ?>&amp;asal=dashboard">Edit</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
const labelHari    = <?= json_encode(array_column($trenPesanan, 'hari')) ?>;
const jumlahPesan  = <?= json_encode(array_column($trenPesanan, 'jumlah')) ?>;
const tanggalIndo  = <?= json_encode(array_map(fn($d) => psnTanggalIndo($d['tanggal']), $trenPesanan)) ?>;

new Chart(document.getElementById('grafikMingguan'), {
    type: 'bar',
    data: {
        labels: labelHari,
        datasets: [{ label: 'Jumlah pesanan', data: jumlahPesan, borderRadius: 4 }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { title: (items) => tanggalIndo[items[0].dataIndex] } }
        },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
});
const namaMenu = <?= json_encode(array_column($menuTerlaris, 'nama'), JSON_HEX_TAG | JSON_HEX_AMP) ?>;
const totalBox = <?= json_encode(array_map(fn($m) => (int) $m['total_box'], $menuTerlaris)) ?>;

const kanvasMenu = document.getElementById('grafikMenu');
if (kanvasMenu) {
    new Chart(kanvasMenu, {
        type: 'bar',
        data: {
            labels: namaMenu,
            datasets: [{ label: 'Total box', data: totalBox, borderRadius: 4 }]
        },
        options: {
            indexAxis: 'y',              // batang mendatar
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });
}
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>