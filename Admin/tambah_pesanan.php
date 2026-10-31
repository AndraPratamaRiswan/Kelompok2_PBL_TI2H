<?php
// dropdown dropdown
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

//manggil notif dari index.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Location: index.php?demo=ditambah');
    exit;
}

$pageTitle = 'Tambah Pesanan';
$activeNav = 'dashboard';
require __DIR__ . '/includes/header.php';
?>

<section class="page-heading">
    <div>
        <h1>Tambah Pesanan</h1>
    </div>
    <a class="button button-neutral" href="index.php" onclick="return confirm('Yakin ingin keluar? Isian pesanan ini tidak akan disimpan.');">Kembali</a>
</section>

<section class="order-form-panel">
    <form method="post" action="tambah_pesanan.php" class="order-form">
        <div class="order-form-grid">
            <label>
                Nomor HP
                <input type="tel" name="nomor" placeholder="Contoh: 081234567890" required>
            </label>
            <label>
                Nama Pembeli
                <input type="text" name="nama" placeholder="Nama pembeli" maxlength="100" required>
            </label>
        </div>

    <!-- satu pembeli bisa banyak menu -->
    <div class="order-items-section">
            <div class="order-section-heading">
                <div>
                    <h2>Menu yang dipesan</h2>
                    <p>Pilih menu dan jumlah box untuk setiap baris.</p>
                </div>
                <button class="button button-add-item" type="button" id="tambah-baris-menu">+ Tambah Menu</button>
            </div>

            <div class="order-item-list" id="daftar-baris-menu">
                <div class="order-item-row">
                    <label class="order-menu-field">
                        Menu
                        <select name="menu_id[]" required>
                            <option value="">Pilih Menu</option>
                            <?php foreach ($daftarMenu as $menu): ?>
                                <option value="<?= (int) $menu['id'] ?>"><?= htmlspecialchars($menu['nama'], ENT_QUOTES, 'UTF-8') ?> — Rp <?= number_format($menu['harga'], 0, ',', '.') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <div class="order-qty-field">
                        <span>Jumlah (box)</span>
                        <div class="qty-control">
                            <button type="button" class="qty-button" data-qty-change="-1">−</button>
                            <input type="number" name="jumlah[]" min="1" value="1" required>
                            <button type="button" class="qty-button" data-qty-change="1">+</button>
                        </div>
                    </div>
                    <button type="button" class="remove-order-item">Hapus</button>
                </div>
            </div>
        </div>

        <div class="order-form-grid">
            <label>
                Status Pesanan
                <select name="status_pesanan" required>
                    <option value="">Pilih status pesanan</option>
                    <?php foreach ($statusPesanan as $status): ?>
                        <option value="<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Status Pembayaran
                <select name="status_pembayaran" id="status-pembayaran" required>
                    <option value="">Pilih status pembayaran</option>
                    <?php foreach ($statusPembayaran as $status): ?>
                        <option value="<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Tanggal Kirim
                <input type="date" name="tanggal" required>
            </label>
            <label>
                Jam Kirim
                <input type="time" name="jam" required>
                <small>Pilih jam dari kontrol browser.</small>
            </label>
            <label>
                Ongkir (Rp) <span class="optional-label">Opsional</span>
                <input type="number" name="ongkir" min="0" step="1000" value="0">
            </label>
            <!-- dp muncul kalo status bayar nya dp -->
            <label id="field-jumlah-dp" hidden>
                Jumlah DP (Rp)
                <input type="number" name="jumlah_dp" id="jumlah-dp" min="1" step="1000" placeholder="Masukkan jumlah DP">
            </label>
        </div>

        <label class="order-note-field">
            Catatan Pesanan <span class="optional-label">Opsional</span>
            <textarea name="catatan" rows="3" maxlength="255" placeholder="Contoh: sambal dipisah"></textarea>
        </label>

        <div class="order-form-actions">
            <button class="button button-primary" type="submit">Simpan Pesanan</button>
            <a class="button button-neutral" href="index.php" onclick="return confirm('Yakin ingin membatalkan pengisian? Data pesanan tidak akan disimpan.');">Batal</a>
        </div>
    </form>
</section>

<!-- template baris menu kedua dan strsnya, mainan js -->
<template id="template-baris-menu">
    <div class="order-item-row">
        <label class="order-menu-field">
            Menu
            <select name="menu_id[]" required>
                <option value="">Pilih Menu</option>
                <?php foreach ($daftarMenu as $menu): ?>
                    <option value="<?= (int) $menu['id'] ?>"><?= htmlspecialchars($menu['nama'], ENT_QUOTES, 'UTF-8') ?> — Rp <?= number_format($menu['harga'], 0, ',', '.') ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <div class="order-qty-field">
            <span>Jumlah (box)</span>
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