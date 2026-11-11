<?php
// READ satu pesanan dari database. Tampilan mengikuti detail_pesanan.php.
require_once __DIR__ . '/includes/koneksi.php';
require_once __DIR__ . '/includes/queries/pesanan_crud.php';
require_once __DIR__ . '/includes/pesanan_helper.php';

// halaman asal untuk menentukan tujuan tombol "kembali"
$asal = (($_GET['asal'] ?? 'daftar') === 'dashboard') ? 'dashboard' : 'daftar';
$urlKembali = ($asal === 'dashboard') ? 'index.php' : 'pesanan_daftar.php';

$idPesanan = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idPesanan || $idPesanan < 1) {
    header('Location: ' . $urlKembali);
    exit;
}

$pesanan = false;
$items   = [];
$error   = '';

try {
    $pesanan = psnAmbil($pdo, $idPesanan);
    if (!$pesanan) {
        header('Location: ' . $urlKembali . '?pesan=tidak_ditemukan');
        exit;
    }
    $items = psnItems($pdo, $idPesanan);
} catch (PDOException $e) {
    $error = 'Data pesanan gagal dibaca. Detail: ' . $e->getMessage();
}

$subtotal = 0.0;
foreach ($items as $item) {
    $subtotal += (float) $item['subtotal'];
}
$ongkir = $pesanan ? (float) $pesanan['biaya_ongkir'] : 0.0;
$total  = $subtotal + $ongkir;
$statusBayar = $pesanan ? (string) $pesanan['status_pembayaran'] : '';
if ($statusBayar === 'Lunas') {
    $sudahDibayar = $total;
} elseif ($statusBayar === 'DP') {
    $sudahDibayar = (float) $pesanan['jumlah_dp'];
} else {
    $sudahDibayar = 0.0;
}
$sisaBayar = max(0, $total - $sudahDibayar);

$pageTitle = 'Detail Pesanan';
$activeNav = 'dashboard';
require __DIR__ . '/includes/header.php';
?>

<?php if ($error !== '' || !$pesanan): ?>
    <section class="page-heading">
        <div><h1>Detail Pesanan</h1></div>
        <a class="button button-neutral" href="<?= psnE($urlKembali) ?>">Kembali</a>
    </section>
    <div class="form-message form-message--error"><?= psnE($error !== '' ? $error : 'Pesanan tidak ditemukan.') ?></div>
<?php else: ?>

<section class="page-heading">
    <div>
        <h1>Detail Pesanan</h1>
        <p class="page-description"><?= psnE(psnKode($idPesanan)) ?> · Dibuat <?= psnE(date('d/m/Y H:i', strtotime((string) $pesanan['created_at']))) ?></p>
    </div>
    <a class="button button-neutral" href="<?= psnE($urlKembali) ?>">Kembali</a>
</section>

<div class="detail-status-row">
    <div class="detail-status-item">
        <span>Status Pesanan</span>
        <strong class="detail-badge"><?= psnE($pesanan['status_pesanan']) ?></strong>
    </div>
    <div class="detail-status-item">
        <span>Status Pembayaran</span>
        <strong class="detail-badge <?= $statusBayar === 'Lunas' ? 'detail-badge--paid' : ($statusBayar === 'DP' ? 'detail-badge--dp' : '') ?>"><?= psnE($statusBayar) ?></strong>
    </div>
</div>

<div class="order-detail-grid">
    <section class="detail-card">
        <h2>Pemesan &amp; Pengiriman</h2>
        <dl class="detail-list">
            <div><dt>Nama</dt><dd><?= psnE($pesanan['nama_pembeli']) ?></dd></div>
            <div><dt>Nomor HP</dt><dd><?= psnE($pesanan['no_hp']) ?></dd></div>
            <div><dt>Alamat</dt><dd><?= psnE($pesanan['alamat_pengiriman']) ?></dd></div>
            <div><dt>Tanggal Kirim</dt><dd><?= psnE(date('d/m/Y', strtotime((string) $pesanan['tanggal_pengiriman']))) ?></dd></div>
            <div><dt>Jam Kirim</dt><dd><?= psnE(substr((string) $pesanan['jam_pengiriman'], 0, 5)) ?> WIB</dd></div>
        </dl>
    </section>

    <section class="detail-card">
        <h2>Ringkasan Pembayaran</h2>
        <dl class="detail-list">
            <div><dt>Subtotal Menu</dt><dd><?= psnRupiah($subtotal) ?></dd></div>
            <div><dt>Ongkir</dt><dd><?= psnRupiah($ongkir) ?></dd></div>
            <div class="detail-list-total"><dt>Total</dt><dd><?= psnRupiah($total) ?></dd></div>
            <div><dt>Sudah Dibayar</dt><dd><?= psnRupiah($sudahDibayar) ?></dd></div>
            <div class="detail-list-remaining"><dt>Sisa Pembayaran</dt><dd><?= psnRupiah($sisaBayar) ?></dd></div>
        </dl>
    </section>

    <section class="detail-card detail-card--wide">
        <h2>Rincian Menu Pesanan</h2>
        <div class="detail-table-wrap">
            <table class="detail-table">
                <thead><tr><th>Menu</th><th>Qty</th><th>Harga Satuan</th><th>Subtotal</th></tr></thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= psnE($item['nama_menu']) ?></td>
                            <td><?= (int) $item['jumlah'] ?> box</td>
                            <td><?= psnRupiah($item['harga_satuan']) ?></td>
                            <td><?= psnRupiah($item['subtotal']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="detail-card detail-card--wide">
        <h2>Catatan Pesanan</h2>
        <p class="detail-note"><?= trim((string) ($pesanan['catatan'] ?? '')) !== '' ? nl2br(psnE($pesanan['catatan'])) : 'Tidak ada catatan.' ?></p>
    </section>
</div>

<div class="detail-actions">
    <a class="button button-neutral" href="<?= psnE($urlKembali) ?>">Tutup</a>
    <form method="post" action="pesanan_hapus.php" style="margin:0"
          onsubmit="return confirm('Hapus pesanan <?= psnE(psnKode($idPesanan)) ?>? Data yang dihapus tidak bisa dikembalikan.');">
        <input type="hidden" name="id_pesanan" value="<?= (int) $idPesanan ?>">
        <input type="hidden" name="asal" value="<?= psnE($asal) ?>">
        <button class="button button-delete" type="submit">Hapus Pesanan</button>
    </form>
    <a class="button button-primary" href="pesanan_ubah.php?id=<?= (int) $idPesanan ?>&amp;asal=<?= psnE($asal) ?>">Edit Pesanan</a>
</div>

<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
