<?php
// dummy menu 
$katalogMenu = [
    ['nama' => 'Nasi Kuning', 'deskripsi' => 'Nasi kuning, telur dadar, bihun, kering tempe', 'harga' => 25000, 'gambar' => 'assets/img/nasikuning.jpg'],
    ['nama' => 'Nasi Ayam Geprek', 'deskripsi' => 'Nasi putih, ayam geprek, sambal', 'harga' => 25000, 'gambar' => 'assets/img/ayamgeprek.jpg'],
    ['nama' => 'Nasi Rendang', 'deskripsi' => 'Nasi putih, rendang, sayuran', 'harga' => 25000, 'gambar' => 'assets/img/nasirendang.jpg'],
    ['nama' => 'Nasi Ayam Bakar', 'deskripsi' => 'Nasi putih, ayam bakar, sambal', 'harga' => 25000, 'gambar' => 'assets/img/ayambakar.jpg'],
    ['nama' => 'Nasi Tumpeng', 'deskripsi' => 'Nasi kuning dan lauk pelengkap', 'harga' => 150000, 'gambar' => 'assets/img/nasitumpeng.jpg'],
    ['nama' => 'Nasi Ayam Goreng', 'deskripsi' => 'Nasi putih, ayam goreng, lalapan', 'harga' => 25000, 'gambar' => 'assets/img/ayamgoreng.jpg'],
    ['nama' => 'Gule Kambing', 'deskripsi' => 'Gule kambing dengan kuah rempah', 'harga' => 30000, 'gambar' => 'assets/img/gulekambing.jpg'],
    ['nama' => 'Nasi Uduk', 'deskripsi' => 'Nasi uduk dan lauk pelengkap', 'harga' => 22000, 'gambar' => 'assets/img/nasiuduk.jpg'],
];

$pageTitle = 'Katalog Menu';
$activeNav = 'katalog';
require __DIR__ . '/includes/header.php';
?>

<!-- judul katalog -->
<section class="page-heading">
    <div>
        <h1>Katalog Menu</h1>
        <p class="page-description">Daftar menu katering SIKAT.</p>
    </div>
    <button class="button button-primary" type="button">+ Tambah Menu</button>
</section>

<!-- input dan dropdown masih tampilan doang, blom berfungsi -->
<section class="catalog-toolbar">
    <input type="search" placeholder="Cari menu" aria-label="Cari menu">
    <select class="simple-select" aria-label="Kategori menu">
        <option>Kategori Menu</option>
        <option>Nasi Box</option>
        <option>Snack Box</option>
        <option>Tumpeng</option>
    </select>
    <select class="simple-select" aria-label="Rentang harga">
        <option>Rentang Harga</option>
        <option>Di bawah Rp25.000</option>
        <option>Rp25.000 ke atas</option>
    </select>
    <select class="simple-select" aria-label="Menu terlaris">
        <option>Menu Terlaris</option>
        <option>Terlaris</option>
    </select>
</section>

<section class="catalog-grid">
    <?php foreach ($katalogMenu as $menu): ?>
        <article class="menu-card">
            <img src="<?= htmlspecialchars($menu['gambar']) ?>" alt="Foto <?= htmlspecialchars($menu['nama']) ?>" loading="lazy">
            <div class="menu-card__body">
                <h2><?= htmlspecialchars($menu['nama']) ?></h2>
                <p><?= htmlspecialchars($menu['deskripsi']) ?></p>
                <div class="menu-card__bottom">
                    <strong>Rp <?= number_format($menu['harga'], 0, ',', '.') ?></strong>
                    <div class="menu-card__buttons">
                        <button class="button button-neutral" type="button">Edit</button>
                        <button class="button button-delete" type="button">Hapus</button>
                    </div>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
