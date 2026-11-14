<?php
require __DIR__ . '/includes/koneksi.php';
require __DIR__ . '/includes/queries/katalog.php';
require_once __DIR__ . '/includes/queries/pesanan_crud.php'; // mnuUbahStatus()

$q = trim($_GET['q'] ?? '');
$idKategori = filter_input(INPUT_GET, 'kategori', FILTER_VALIDATE_INT);
$idKategori = ($idKategori !== false && $idKategori !== null && $idKategori > 0) ? $idKategori : 0;

// alamat katalog dengan pencarian dan kategori yang sedang aktif (dipakai form dan redirect)
$queryAktif = [];
if ($q !== '') {
    $queryAktif['q'] = $q;
}
if ($idKategori > 0) {
    $queryAktif['kategori'] = $idKategori;
}
$urlKatalog = 'katalog.php' . ($queryAktif ? '?' . http_build_query($queryAktif) : '');

// ---- tombol Aktifkan / Nonaktifkan (POST lalu redirect) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi   = $_POST['aksi'] ?? '';
    $idMenu = filter_input(INPUT_POST, 'id_menu', FILTER_VALIDATE_INT);
    $kode   = 'gagal';

    if ($idMenu && $idMenu > 0 && ($aksi === 'aktifkan' || $aksi === 'nonaktifkan')) {
        try {
            $berhasil = mnuUbahStatus($pdo, $idMenu, $aksi === 'aktifkan');
            $kode = $berhasil ? ($aksi === 'aktifkan' ? 'diaktifkan' : 'dinonaktifkan') : 'tidak_ditemukan';
        } catch (Throwable $e) {
            $kode = 'gagal';
        }
    }

    $query = $queryAktif;
    $query['pesan'] = $kode;
    header('Location: katalog.php?' . http_build_query($query));
    exit;
}
$error = '';
$pesan = '';
$pesanMap = [
    'ditambah' => 'Menu berhasil ditambahkan.',
    'diedit' => 'Perubahan menu berhasil disimpan.',
    'diaktifkan' => 'Menu diaktifkan dan tampil lagi untuk pembeli.',
    'dinonaktifkan' => 'Menu dinonaktifkan dan tidak tampil untuk pembeli.',
];
$pesanError = [
    'tidak_ditemukan' => 'Menu tidak ditemukan.',
    'gagal' => 'Aksi gagal diproses. Coba lagi.',
];
$kodePesan = $_GET['pesan'] ?? '';
if (isset($pesanMap[$kodePesan])) {
    $pesan = $pesanMap[$kodePesan];
} elseif (isset($pesanError[$kodePesan])) {
    $error = $pesanError[$kodePesan];
}

$daftarKategori = [];
try {
    $daftarKategori = ambilKategori($pdo);
    $katalogMenu = ambilKatalogMenu($pdo, $q, $idKategori);
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

<section class="catalog-toolbar" style="grid-template-columns: 1fr;">
    <form method="get" action="katalog.php" class="catalog-search">
        <input type="search" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Cari menu" aria-label="Cari menu">
        <select class="simple-select" name="kategori" aria-label="Kategori menu" onchange="this.form.submit()">
            <option value="">Semua Kategori</option>
            <?php foreach ($daftarKategori as $kat): ?>
                <option value="<?= (int) $kat['id_kategori'] ?>" <?= $idKategori === (int) $kat['id_kategori'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($kat['nama_kategori']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button class="button button-primary" type="submit">Cari</button>
        <?php if ($q !== '' || $idKategori > 0): ?>
            <a class="button button-neutral" href="katalog.php">Reset</a>
        <?php endif; ?>
    </form>
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
                    <form method="post" action="<?= htmlspecialchars($urlKatalog) ?>" style="margin:0"
                          <?php if ($aktif): ?>onsubmit="return confirm('Nonaktifkan menu ini? Menu tidak akan tampil untuk pembeli.');"<?php endif; ?>>
                        <input type="hidden" name="id_menu" value="<?= (int) $menu['id_menu'] ?>">
                        <input type="hidden" name="aksi" value="<?= $aktif ? 'nonaktifkan' : 'aktifkan' ?>">
                        <button class="button button-status <?= $aktif ? 'button-delete' : 'button-primary' ?>" type="submit">
                            <?= $aktif ? 'Nonaktifkan' : 'Aktifkan' ?>
                        </button>
                    </form>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
