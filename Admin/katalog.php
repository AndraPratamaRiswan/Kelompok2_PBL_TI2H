<?php
require __DIR__ . '/includes/koneksi.php';
require __DIR__ . '/includes/queries/katalog.php';

$q = trim($_GET['q'] ?? '');
$error = '';
$pesan = '';
$pesanMap = [
    'ditambah' => 'Menu berhasil ditambahkan.',
    'diedit' => 'Perubahan menu berhasil disimpan.',
];
$kodePesan = $_GET['pesan'] ?? '';
if (isset($pesanMap[$kodePesan])) {
    $pesan = $pesanMap[$kodePesan];
}

try {
    $katalogMenu = ambilKatalogMenu($pdo, $q);
} catch (PDOException $e) {
    $katalogMenu = [];
    $error = 'Data menu gagal dibaca. Periksa apakah tabel menu, kategori, dan kategori_menu sudah dibuat. Detail: ' . $e->getMessage();
}

$pageTitle = 'Katalog Menu';
$activeNav = 'katalog';
require __DIR__ . '/includes/header.php';
?>

<!-- judul katalog -->
<section class="page-heading">
    <div>
        <h1>Katalog Menu</h1>
        <p class="page-description">Daftar menu katering SIKAT dari database.</p>
    </div>
    <a class="button button-primary" href="tambah_menu.php">+ Tambah Menu</a>
</section>

<?php if ($pesan !== ''): ?>
    <div class="form-message form-message--success"><?= htmlspecialchars($pesan) ?></div>
<?php endif; ?>

<?php if ($error !== ''): ?>
    <div class="form-message form-message--error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<section class="catalog-toolbar">
    <form method="get" action="katalog.php" class="catalog-search">
        <input type="search" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Cari menu" aria-label="Cari menu">
        <button class="button button-primary" type="submit">Cari</button>
    </form>
    <select class="simple-select" aria-label="Kategori menu">
        <option>Kategori Menu</option>
    </select>
    <select class="simple-select" aria-label="Rentang harga">
        <option>Rentang Harga</option>
    </select>
    <select class="simple-select" aria-label="Menu terlaris">
        <option>Menu Terlaris</option>
    </select>
</section>

<section class="catalog-grid">
    <?php if (empty($katalogMenu) && $error === ''): ?>
        <p class="empty-state">Menu tidak ditemukan.</p>
    <?php endif; ?>

    <?php foreach ($katalogMenu as $menu): ?>
        <?php $aktif = filter_var($menu['is_tersedia'], FILTER_VALIDATE_BOOLEAN); ?>
        <article class="menu-card <?= $aktif ? '' : 'menu-card--inactive' ?>">
            <img src="<?= htmlspecialchars(gambarMenuLokal($menu['gambar_url'] ?? null, $menu['nama_menu'])) ?>"
                 alt="Foto <?= htmlspecialchars($menu['nama_menu']) ?>" loading="lazy">
            <div class="menu-card__body">
                <h2><?= htmlspecialchars($menu['nama_menu']) ?></h2>
                <p><?= htmlspecialchars($menu['deskripsi'] ?? '') ?></p>
                <small class="menu-category"><?= htmlspecialchars($menu['daftar_kategori']) ?></small>
                <strong class="menu-price">Rp <?= number_format((float) $menu['harga_menu'], 0, ',', '.') ?></strong>
                <span class="menu-state <?= $aktif ? 'menu-state--active' : 'menu-state--inactive' ?>">
                    <?= $aktif ? 'Aktif' : 'Nonaktif' ?>
                </span>
                <div class="menu-card__buttons">
                    <a class="button button-neutral" href="edit_menu.php?id=<?= (int) $menu['id_menu'] ?>">Edit</a>
                    <!-- LANJUTAN KATALOG 3.4 — Tombol status sengaja belum menjalankan UPDATE. -->
                    <button class="button button-status <?= $aktif ? 'button-delete' : 'button-primary' ?>" type="button" disabled title="Fungsi aktif/nonaktif belum dibuat">
                        <?= $aktif ? 'Nonaktifkan' : 'Aktifkan' ?>
                    </button>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
