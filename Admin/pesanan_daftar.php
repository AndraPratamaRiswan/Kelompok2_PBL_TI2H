<?php
// READ daftar pesanan dari database + tombol Detail / Edit / Hapus.
// Tampilan baris pesanan mengikuti "Daftar Pesanan" di index.php.
require_once __DIR__ . '/includes/koneksi.php';
require_once __DIR__ . '/includes/queries/pesanan_crud.php';
require_once __DIR__ . '/includes/pesanan_helper.php';

$q      = trim($_GET['q'] ?? '');
$status = trim($_GET['status'] ?? '');
if ($status !== '' && !in_array($status, psnDaftarStatusPesanan(), true)) {
    $status = '';
}

$pesanMap = [
    'ditambah'        => ['success', 'Pesanan berhasil ditambahkan.'],
    'diubah'          => ['success', 'Perubahan pesanan berhasil disimpan.'],
    'dihapus'         => ['success', 'Pesanan berhasil dihapus.'],
    'tidak_ditemukan' => ['error', 'Pesanan tidak ditemukan.'],
    'gagal_hapus'     => ['error', 'Pesanan gagal dihapus. Coba lagi.'],
];
$kodePesan = $_GET['pesan'] ?? '';
$pesan = $pesanMap[$kodePesan] ?? null;

$error = '';
try {
    $daftarPesanan = psnDaftar($pdo, $q, $status);
} catch (PDOException $e) {
    $daftarPesanan = [];
    $error = 'Data pesanan gagal dibaca. Periksa tabel pesanan, pembeli, dan detail_pesanan. Detail: ' . $e->getMessage();
}

$pageTitle = 'Daftar Pesanan';
$activeNav = 'dashboard';
require __DIR__ . '/includes/header.php';
?>

<section class="page-heading">
    <div>
        <h1>Daftar Pesanan</h1>
        <p class="page-description">Kelola pesanan katering SIKAT dari database.</p>
    </div>
    <div class="page-actions">
        <a class="button button-neutral" href="index.php">Kembali ke Dashboard</a>
        <a class="button button-primary" href="pesanan_tambah.php">+ Tambah Pesanan</a>
    </div>
</section>

<?php if ($pesan): ?>
    <div class="form-message form-message--<?= psnE($pesan[0]) ?>"><?= psnE($pesan[1]) ?></div>
<?php endif; ?>
<?php if ($error !== ''): ?>
    <div class="form-message form-message--error"><?= psnE($error) ?></div>
<?php endif; ?>

<section class="catalog-toolbar" style="grid-template-columns: 1fr;">
    <form method="get" action="pesanan_daftar.php" class="catalog-search">
        <input type="search" name="q" value="<?= psnE($q) ?>" placeholder="Cari nama, nomor HP, atau kode pesanan" aria-label="Cari pesanan">
        <select class="simple-select" name="status" aria-label="Status pesanan">
            <option value="">Semua status</option>
            <?php foreach (psnDaftarStatusPesanan() as $opsi): ?>
                <option value="<?= psnE($opsi) ?>" <?= $status === $opsi ? 'selected' : '' ?>><?= psnE($opsi) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="button button-primary" type="submit">Cari</button>
    </form>
</section>

<section class="orders-panel">
    <div class="orders-toolbar">
        <div class="orders-title-group"><h2>Semua Pesanan (<?= count($daftarPesanan) ?>)</h2></div>
    </div>

    <div class="order-list" style="max-height: none;">
        <?php if (empty($daftarPesanan) && $error === ''): ?>
            <p class="empty-state">Pesanan tidak ditemukan.</p>
        <?php endif; ?>

        <?php foreach ($daftarPesanan as $pesananRow): ?>
            <?php $kode = psnKode((int) $pesananRow['id_pesanan']); ?>
            <article class="order-row" style="grid-template-columns: minmax(210px, 1fr) 96px minmax(300px, 1fr);">
                <div class="order-info">
                    <strong>Pesanan <?= psnE($kode) ?></strong>
                    <span><?= psnE($pesananRow['no_hp']) ?> · <?= psnE($pesananRow['nama_pembeli']) ?></span>
                    <span><?= psnE($pesananRow['ringkasan_menu']) ?></span>
                    <small>Kirim <?= psnE(psnTanggalIndo($pesananRow['tanggal_pengiriman'])) ?> · Total <?= psnRupiah($pesananRow['total_harga']) ?></small>
                </div>
                <span class="status-pill <?= psnKelasBayar((string) $pesananRow['status_pembayaran']) ?>">
                    <?= psnE($pesananRow['status_pembayaran']) ?>
                </span>
                <div class="order-buttons">
                    <span class="process-status"><?= psnE($pesananRow['status_pesanan']) ?></span>
                    <a class="button button-neutral" href="pesanan_detail.php?id=<?= (int) $pesananRow['id_pesanan'] ?>&amp;asal=daftar">Detail</a>
                    <a class="button button-neutral" href="pesanan_ubah.php?id=<?= (int) $pesananRow['id_pesanan'] ?>&amp;asal=daftar">Edit</a>
                    <form method="post" action="pesanan_hapus.php" style="margin:0"
                          onsubmit="return confirm('Hapus pesanan <?= psnE($kode) ?>? Data yang dihapus tidak bisa dikembalikan.');">
                        <input type="hidden" name="id_pesanan" value="<?= (int) $pesananRow['id_pesanan'] ?>">
                        <button class="button button-delete" type="submit">Hapus</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
