<?php
// isi data sesuai baris mana yang dipencet
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

//dropdown dropdown
$daftarMenu = [
    ['id' => 1, 'nama' => 'Nasi Ayam Bakar', 'harga' => 25000],
    ['id' => 2, 'nama' => 'Nasi Ayam Geprek', 'harga' => 25000],
    ['id' => 3, 'nama' => 'Nasi Rendang', 'harga' => 25000],
    ['id' => 4, 'nama' => 'Nasi Kuning', 'harga' => 25000],
    ['id' => 5, 'nama' => 'Snack Box Premium', 'harga' => 22000],
    ['id' => 6, 'nama' => 'Tumpeng Mini', 'harga' => 150000],
    ['id' => 7, 'nama' => 'Gule Kambing', 'harga' => 30000],
    ['id' => 8, 'nama' => 'Nasi Uduk', 'harga' => 25000],
];
$statusPesanan = ['Dikonfirmasi', 'Diproses', 'Dikirim', 'Selesai'];
$statusPembayaran = ['DP', 'Lunas'];

// manggil notif
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Location: index.php?demo=diedit');
    exit;
}

$pageTitle = 'Edit Pesanan';
$activeNav = 'dashboard';
require __DIR__ . '/includes/header.php';
?>

<section class="page-heading">
    <div>
        <h1>Edit Pesanan</h1>
        <p class="page-description">(Contoh edit <?= htmlspecialchars($pesanan['kode'], ENT_QUOTES, 'UTF-8') ?>. Perubahan tidak disimpan)</p>
    </div>
    <a class="button button-neutral" href="index.php" onclick="return confirm('Yakin ingin keluar? Perubahan yang belum disimpan akan hilang.');">Kembali</a>
</section>

<section class="order-form-panel">
        <form method="post"
            action="edit_pesanan.php?id=<?= (int) $idPesanan ?>"
            class="order-form"
            novalidate>
        <div class="order-form-grid">
            <label>Nomor HP
                <input type="tel" name="nomor" value="<?= htmlspecialchars($pesanan['nomor'], ENT_QUOTES, 'UTF-8') ?>" required>
            </label>
            <label>Nama Pembeli
                <input type="text" name="nama" value="<?= htmlspecialchars($pesanan['nama'], ENT_QUOTES, 'UTF-8') ?>" required>
            </label>
        </div>

        <!-- data lama stay (karna edit menu) -->
        <div class="order-items-section">
            <div class="order-section-heading">
                <div>
                    <h2>Menu yang dipesan</h2>
                </div>
                <button class="button button-add-item" type="button" id="tambah-baris-menu">+ Tambah Menu</button>
            </div>
            <div class="order-item-list" id="daftar-baris-menu">
                <?php foreach ($pesanan['items'] as $item): ?>
                    <div class="order-item-row">
                        <label class="order-menu-field">Menu
                            <select name="menu_id[]" required>
                                <option value="">Pilih Menu</option>
                                <?php foreach ($daftarMenu as $menu): ?>
                                    <option value="<?= (int) $menu['id'] ?>" <?= (int) $item['menu_id'] === (int) $menu['id'] ? 'selected' : '' ?>><?= htmlspecialchars($menu['nama'], ENT_QUOTES, 'UTF-8') ?> — Rp <?= number_format($menu['harga'], 0, ',', '.') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <div class="order-qty-field">
                            <span>Jumlah (box)</span>
                            <div class="qty-control">
                                <button type="button" class="qty-button" data-qty-change="-1">−</button>
                                <input type="number" name="jumlah[]" min="1" value="<?= (int) $item['qty'] ?>" required>
                                <button type="button" class="qty-button" data-qty-change="1">+</button>
                            </div>
                        </div>
                        <button type="button" class="remove-order-item">Hapus</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="order-form-grid">
            <label>Status Pesanan
                <select name="status_pesanan" required>
                    <?php foreach ($statusPesanan as $status): ?>
                        <option value="<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>" <?= $pesanan['status_pesanan'] === $status ? 'selected' : '' ?>><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Status Pembayaran
                <select name="status_pembayaran" id="status-pembayaran" required>
                    <?php foreach ($statusPembayaran as $status): ?>
                        <option value="<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>" <?= $pesanan['status_pembayaran'] === $status ? 'selected' : '' ?>><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Tanggal Kirim
                <input type="date" name="tanggal" value="<?= htmlspecialchars($pesanan['tanggal'], ENT_QUOTES, 'UTF-8') ?>" required>
            </label>
            <label>Jam Kirim
                <input type="time" name="jam" value="<?= htmlspecialchars($pesanan['jam'], ENT_QUOTES, 'UTF-8') ?>" required>
            </label>
            <label>Ongkir (Rp)
                <input type="number" name="ongkir" min="0" step="1000" value="<?= (int) $pesanan['ongkir'] ?>">
            </label>
            <!-- field dp muncul berdasarkan data pembayaran lama -->
            <label id="field-jumlah-dp" <?= $pesanan['status_pembayaran'] === 'DP' ? '' : 'hidden' ?>>Jumlah DP (Rp)
                <input type="number" name="jumlah_dp" id="jumlah-dp" min="0" step="1000" value="<?= (int) $pesanan['jumlah_dp'] ?>">
            </label>
        </div>

        <label class="order-note-field">Catatan Pesanan
            <textarea name="catatan" rows="3" maxlength="255"><?= htmlspecialchars($pesanan['catatan'], ENT_QUOTES, 'UTF-8') ?></textarea>
        </label>

        <div class="order-form-actions">
            <button class="button button-primary" type="submit">Simpan Perubahan</button>
            <a class="button button-neutral" href="index.php" onclick="return confirm('Yakin ingin membatalkan perubahan? Data yang kamu ubah tidak akan disimpan.');">Batal</a>
        </div>
    </form>
</section>

<!-- template baris menu kedua dan strsnya, mainan js -->
<template id="template-baris-menu">
    <div class="order-item-row">
        <label class="order-menu-field">Menu
            <select name="menu_id[]" required>
                <option value="">Pilih Menu</option>
                <?php foreach ($daftarMenu as $menu): ?>
                    <option value="<?= (int) $menu['id'] ?>"><?= htmlspecialchars($menu['nama'], ENT_QUOTES, 'UTF-8') ?> — Rp <?= number_format($menu['harga'], 0, ',', '.') ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <div class="order-qty-field"><span>Jumlah (box)</span>
            <div class="qty-control">
                <button type="button" class="qty-button" data-qty-change="-1">−</button>
                <input type="number" name="jumlah[]" min="1" value="1" required>
                <button type="button" class="qty-button" data-qty-change="1">+</button>
            </div>
        </div>
        <button type="button" class="remove-order-item">Hapus</button>
    </div>
</template>

<script src="assets/js/pesanan.js"></script>
<?php require __DIR__ . '/includes/footer.php'; ?>