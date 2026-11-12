<?php
// Daftar pesanan milik pembeli yang login. Tampilannya sama dengan "Daftar Pesanan" di
// panel Admin (kode pesanan, nomor HP · nama, ringkasan menu, tanggal kirim, total,
// status pembayaran, status pesanan). Tombol Detail/Edit/Hapus khusus admin tidak ikut.
session_start();
ob_start();

$halaman = 'cek-pesanan';
date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/includes/koneksi.php';
require_once __DIR__ . '/includes/queries/pesanan.php';
require_once __DIR__ . '/includes/pesanan_helper.php';

$sudahLogin    = isset($_SESSION['id_pembeli']);
$daftarPesanan = [];
$error         = '';

if ($sudahLogin) {
    try {
        $daftarPesanan = pbDaftarPesanan($pdo, (int) $_SESSION['id_pembeli']);
    } catch (PDOException $e) {
        error_log('Baca pesanan gagal: ' . $e->getMessage());
        $error = 'Pesananmu belum bisa ditampilkan. Coba lagi nanti.';
    }
}

$cssTambahan = ['assets/css/daftar-pesanan.css'];
include 'includes/header.php';
?>

<div class="container mt-5 mb-5">

    <?php if (!$sudahLogin) : ?>
        <div class="card p-5 text-center col-md-8 mx-auto">
            <h5>Masuk dulu untuk melihat pesananmu.</h5>
            <p class="text-muted">Pesanan ditampilkan berdasarkan nomor HP akun kamu.</p>
            <div><a href="login.php?next=cek-pesanan.php" class="btn bg-red text-white">Masuk</a></div>
        </div>
    <?php else : ?>

        <div class="mb-3">
            <h2 class="fw-bold mb-1 isi-red">Daftar Pesanan</h2>
            <p class="text-muted mb-0">Pantau status pesanan katering kamu.</p>
        </div>

        <?php if ($error !== '') : ?>
            <div class="alert alert-danger"><?= pbE($error) ?></div>
        <?php endif; ?>

        <section class="orders-panel">
            <div class="orders-toolbar">
                <div class="orders-title-group"><h2>Semua Pesanan (<?= count($daftarPesanan) ?>)</h2></div>
            </div>

            <div class="order-list">
                <?php if (empty($daftarPesanan) && $error === '') : ?>
                    <p class="empty-state">
                        Kamu belum punya pesanan. <a href="index.php">Pilih menu</a>
                    </p>
                <?php endif; ?>

                <?php foreach ($daftarPesanan as $pesanan) : ?>
                    <?php
                    $ringkasan = [];
                    foreach ($pesanan['items'] as $item) {
                        $ringkasan[] = $item['nama_menu'] . ' (' . (int) $item['jumlah'] . ' box)';
                    }
                    ?>
                    <article class="order-row">
                        <div class="order-info">
                            <strong>Pesanan <?= pbE(pbKode((int) $pesanan['id_pesanan'])) ?></strong>
                            <span><?= pbE($_SESSION['no_hp'] ?? '') ?> · <?= pbE($pesanan['nama_pembeli']) ?></span>
                            <span><?= pbE($ringkasan ? implode(', ', $ringkasan) : '-') ?></span>
                            <small>Kirim <?= pbE(pbTanggalPanjang($pesanan['tanggal_pengiriman'])) ?> · Total <?= pbRupiah($pesanan['total_harga']) ?></small>
                        </div>
                        <span class="status-pill <?= pbKelasBayar((string) $pesanan['status_pembayaran']) ?>">
                            <?= pbE($pesanan['status_pembayaran']) ?>
                        </span>
                        <div class="order-buttons">
                            <span class="process-status"><?= pbE($pesanan['status_pesanan']) ?></span>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

    <?php endif; ?>
</div>

<?php
include 'includes/footer.php';
?>
