<?php
// menampilkan detail sesuai baris yang dipencet
$idPesanan = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$contohPesanan = [
    1 => [
        'kode' => 'SKKT-001', 'nomor' => '0812-6786-0980', 'nama' => 'Ibu Narsis',
        'items' => [
            ['menu_id' => 1, 'nama_menu' => 'Nasi Ayam Bakar', 'qty' => 50, 'harga' => 25000],
        ],
        'status_pesanan' => 'Dikonfirmasi', 'status_pembayaran' => 'Lunas',
        'tanggal' => '2026-11-02', 'jam' => '10:00', 'ongkir' => 15000, 'jumlah_dp' => 0,
        'catatan' => 'Sambal dipisah.', 'dibuat' => '12 Okt 2026, 14:30 WIB',
    ],
    2 => [
        'kode' => 'SKKT-002', 'nomor' => '0812-6786-0981', 'nama' => 'Ibu Sulami',
        'items' => [
            ['menu_id' => 1, 'nama_menu' => 'Nasi Ayam Bakar', 'qty' => 50, 'harga' => 25000],
        ],
        'status_pesanan' => 'Diproses', 'status_pembayaran' => 'DP',
        'tanggal' => '2026-11-03', 'jam' => '11:30', 'ongkir' => 10000, 'jumlah_dp' => 200000,
        'catatan' => 'Minta sambal dipisah.', 'dibuat' => '12 Okt 2026, 14:45 WIB',
    ],
    3 => [
        'kode' => 'SKKT-003', 'nomor' => '0812-6786-0982', 'nama' => 'Pak Yanto',
        'items' => [
            ['menu_id' => 3, 'nama_menu' => 'Nasi Rendang', 'qty' => 30, 'harga' => 25000],
            ['menu_id' => 8, 'nama_menu' => 'Nasi Uduk', 'qty' => 10, 'harga' => 25000],
        ],
        'status_pesanan' => 'Dikirim', 'status_pembayaran' => 'Lunas',
        'tanggal' => '2026-11-04', 'jam' => '15:00', 'ongkir' => 20000, 'jumlah_dp' => 0,
        'catatan' => 'Kirim tepat waktu.', 'dibuat' => '13 Okt 2026, 09:15 WIB',
    ],
    4 => [
        'kode' => 'SKKT-004', 'nomor' => '0812-6786-0983', 'nama' => 'Bu Ratna',
        'items' => [
            ['menu_id' => 4, 'nama_menu' => 'Nasi Kuning', 'qty' => 20, 'harga' => 25000],
        ],
        'status_pesanan' => 'Dikonfirmasi', 'status_pembayaran' => 'DP',
        'tanggal' => '2026-11-05', 'jam' => '09:00', 'ongkir' => 12000, 'jumlah_dp' => 100000,
        'catatan' => '', 'dibuat' => '14 Okt 2026, 10:00 WIB',
    ],
    5 => [
        'kode' => 'SKKT-005', 'nomor' => '0812-6786-0984', 'nama' => 'Ibu Siti',
        'items' => [
            ['menu_id' => 5, 'nama_menu' => 'Snack Box Premium', 'qty' => 25, 'harga' => 22000],
        ],
        'status_pesanan' => 'Selesai', 'status_pembayaran' => 'Lunas',
        'tanggal' => '2026-10-15', 'jam' => '13:00', 'ongkir' => 15000, 'jumlah_dp' => 0,
        'catatan' => 'Terima kasih.', 'dibuat' => '15 Okt 2026, 08:00 WIB',
    ],
    6 => [
        'kode' => 'SKKT-006', 'nomor' => '0812-6786-0985', 'nama' => 'Pak Budi',
        'items' => [
            ['menu_id' => 6, 'nama_menu' => 'Tumpeng Mini', 'qty' => 5, 'harga' => 150000],
        ],
        'status_pesanan' => 'Diproses', 'status_pembayaran' => 'DP',
        'tanggal' => '2026-11-06', 'jam' => '08:30', 'ongkir' => 25000, 'jumlah_dp' => 300000,
        'catatan' => 'Gunakan tampah.', 'dibuat' => '16 Okt 2026, 11:30 WIB',
    ],
];

$pesanan = $contohPesanan[$idPesanan] ?? null;
if (!$pesanan) {
    header('Location: index.php');
    exit;
}

// perhitungan detail pesanan, untuk tampilan detail
$subtotal = 0;
foreach ($pesanan['items'] as $item) {
    $subtotal += $item['qty'] * $item['harga'];
}
$total = $subtotal + $pesanan['ongkir'];
$sudahDibayar = $pesanan['status_pembayaran'] === 'Lunas' ? $total : $pesanan['jumlah_dp'];
$sisaBayar = max(0, $total - $sudahDibayar);

function rupiahDetail($angka): string
{
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

$pageTitle = 'Detail Pesanan';
$activeNav = 'dashboard';
require __DIR__ . '/includes/header.php';
?>

<section class="page-heading">
    <div>
        <h1>Detail Pesanan</h1>
        <p class="page-description"><?= htmlspecialchars($pesanan['kode'], ENT_QUOTES, 'UTF-8') ?> · Dibuat <?= htmlspecialchars($pesanan['dibuat'], ENT_QUOTES, 'UTF-8') ?></p>
    </div>
    <a class="button button-neutral" href="index.php" onclick="return confirm('Yakin ingin keluar dari detail pesanan?');">Kembali</a>
</section>

<div class="detail-status-row">
    <div class="detail-status-item">
        <span>Status Pesanan</span>
        <strong class="detail-badge"><?= htmlspecialchars($pesanan['status_pesanan'], ENT_QUOTES, 'UTF-8') ?></strong>
    </div>
    <div class="detail-status-item">
        <span>Status Pembayaran</span>
        <strong class="detail-badge <?= $pesanan['status_pembayaran'] === 'Lunas' ? 'detail-badge--paid' : 'detail-badge--dp' ?>"><?= htmlspecialchars($pesanan['status_pembayaran'], ENT_QUOTES, 'UTF-8') ?></strong>
    </div>
</div>

<div class="order-detail-grid">
    <section class="detail-card">
        <h2>Pemesan & Pengiriman</h2>
        <dl class="detail-list">
            <div><dt>Nama</dt><dd><?= htmlspecialchars($pesanan['nama'], ENT_QUOTES, 'UTF-8') ?></dd></div>
            <div><dt>Nomor HP</dt><dd><?= htmlspecialchars($pesanan['nomor'], ENT_QUOTES, 'UTF-8') ?></dd></div>
            <div><dt>Tanggal Kirim</dt><dd><?= date('d/m/Y', strtotime($pesanan['tanggal'])) ?></dd></div>
            <div><dt>Jam Kirim</dt><dd><?= htmlspecialchars($pesanan['jam'], ENT_QUOTES, 'UTF-8') ?> WIB</dd></div>
        </dl>
    </section>

    <section class="detail-card">
        <h2>Ringkasan Pembayaran</h2>
        <dl class="detail-list">
            <div><dt>Subtotal Menu</dt><dd><?= rupiahDetail($subtotal) ?></dd></div>
            <div><dt>Ongkir</dt><dd><?= rupiahDetail($pesanan['ongkir']) ?></dd></div>
            <div class="detail-list-total"><dt>Total</dt><dd><?= rupiahDetail($total) ?></dd></div>
            <div><dt>Sudah Dibayar</dt><dd><?= rupiahDetail($sudahDibayar) ?></dd></div>
            <div class="detail-list-remaining"><dt>Sisa Pembayaran</dt><dd><?= rupiahDetail($sisaBayar) ?></dd></div>
        </dl>
    </section>

    <section class="detail-card detail-card--wide">
        <h2>Rincian Menu Pesanan</h2>
        <div class="detail-table-wrap">
            <table class="detail-table">
                <thead><tr><th>Menu</th><th>Qty</th><th>Harga Satuan</th><th>Subtotal</th></tr></thead>
                <tbody>
                    <?php foreach ($pesanan['items'] as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['nama_menu'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= (int) $item['qty'] ?> box</td>
                            <td><?= rupiahDetail($item['harga']) ?></td>
                            <td><?= rupiahDetail($item['qty'] * $item['harga']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="detail-card detail-card--wide">
        <h2>Catatan Pesanan</h2>
        <p class="detail-note"><?= $pesanan['catatan'] !== '' ? nl2br(htmlspecialchars($pesanan['catatan'], ENT_QUOTES, 'UTF-8')) : 'Tidak ada catatan.' ?></p>
    </section>
</div>

<div class="detail-actions">
    <a class="button button-neutral" href="index.php" onclick="return confirm('Yakin ingin menutup detail pesanan?');">Tutup</a>
    <!-- bisa redirect ke edit kalo maw -->
    <a class="button button-primary" href="edit_pesanan.php?id=<?= (int) $idPesanan ?>">Edit Pesanan</a>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>