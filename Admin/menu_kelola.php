<?php
// Kelola menu: lanjutan katalog.php. Melengkapi bagian "LANJUTAN KATALOG 3.4"
// (tombol aktif/nonaktif) dan menambah HAPUS menu. Tambah & edit memakai
// tambah_menu.php dan edit_menu.php yang sudah ada.
require_once __DIR__ . '/includes/koneksi.php';
require_once __DIR__ . '/includes/queries/katalog.php';     // ambilKatalogMenu(), gambarMenuLokal()
require_once __DIR__ . '/includes/queries/pesanan_crud.php'; // mnuHapus(), mnuUbahStatus()

$q = trim($_GET['q'] ?? ($_POST['q'] ?? ''));

// ---- proses tombol Hapus / Aktifkan / Nonaktifkan (POST lalu redirect) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi   = $_POST['aksi'] ?? '';
    $idMenu = filter_input(INPUT_POST, 'id_menu', FILTER_VALIDATE_INT);
    $kode   = 'gagal';

    if ($idMenu && $idMenu > 0) {
        try {
            if ($aksi === 'hapus') {
                $kode = mnuHapus($pdo, $idMenu);
            } elseif ($aksi === 'aktifkan' || $aksi === 'nonaktifkan') {
                $berhasil = mnuUbahStatus($pdo, $idMenu, $aksi === 'aktifkan');
                $kode = $berhasil ? ($aksi === 'aktifkan' ? 'diaktifkan' : 'dinonaktifkan') : 'tidak_ditemukan';
            }
        } catch (Throwable $e) {
            $kode = 'gagal';
        }
    }

    $query = ['pesan' => $kode];
    if ($q !== '') {
        $query['q'] = $q;
    }
    header('Location: menu_kelola.php?' . http_build_query($query));
    exit;
}

$pesanMap = [
    'dihapus'         => ['success', 'Menu berhasil dihapus.'],
    'diaktifkan'      => ['success', 'Menu diaktifkan dan tampil lagi untuk pembeli.'],
    'dinonaktifkan'   => ['success', 'Menu dinonaktifkan dan tidak tampil untuk pembeli.'],
    'dipakai'         => ['error', 'Menu tidak bisa dihapus karena sudah ada di riwayat pesanan. Nonaktifkan saja supaya tidak tampil untuk pembeli.'],
    'tidak_ditemukan' => ['error', 'Menu tidak ditemukan.'],
    'gagal'           => ['error', 'Aksi gagal diproses. Coba lagi.'],
];
$pesan = $pesanMap[$_GET['pesan'] ?? ''] ?? null;

$error = '';
try {
    $katalogMenu = ambilKatalogMenu($pdo, $q);
} catch (PDOException $e) {
    $katalogMenu = [];
    $error = 'Data menu gagal dibaca. Detail: ' . $e->getMessage();
}

function mnuE($nilai): string
{
    return htmlspecialchars((string) ($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

$pageTitle = 'Kelola Menu';
$activeNav = 'katalog';
require __DIR__ . '/includes/header.php';
?>

<section class="page-heading">
    <div>
        <h1>Kelola Menu</h1>
        <p class="page-description">Tambah, ubah, aktifkan/nonaktifkan, dan hapus menu katering SIKAT.</p>
    </div>
    <div class="orders-actions">
        <a class="button button-neutral" href="katalog.php">Katalog</a>
        <a class="button button-primary" href="tambah_menu.php">+ Tambah Menu</a>
    </div>
</section>

<?php if ($pesan): ?>
    <div class="form-message form-message--<?= mnuE($pesan[0]) ?>"><?= mnuE($pesan[1]) ?></div>
<?php endif; ?>
<?php if ($error !== ''): ?>
    <div class="form-message form-message--error"><?= mnuE($error) ?></div>
<?php endif; ?>

<section class="catalog-toolbar" style="grid-template-columns: 1fr;">
    <form method="get" action="menu_kelola.php" class="catalog-search">
        <input type="search" name="q" value="<?= mnuE($q) ?>" placeholder="Cari menu" aria-label="Cari menu">
        <button class="button button-primary" type="submit">Cari</button>
    </form>
</section>

<section class="catalog-grid">
    <?php if (empty($katalogMenu) && $error === ''): ?>
        <p class="empty-state">Menu tidak ditemukan.</p>
    <?php endif; ?>

    <?php foreach ($katalogMenu as $menu): ?>
        <?php $aktif = filter_var($menu['is_tersedia'], FILTER_VALIDATE_BOOLEAN); ?>
        <article class="menu-card <?= $aktif ? '' : 'menu-card--inactive' ?>">
            <img src="<?= mnuE(gambarMenuLokal($menu['gambar_url'] ?? null, $menu['nama_menu'])) ?>"
                 alt="Foto <?= mnuE($menu['nama_menu']) ?>" loading="lazy">
            <div class="menu-card__body">
                <h2><?= mnuE($menu['nama_menu']) ?></h2>
                <p><?= mnuE($menu['deskripsi'] ?? '') ?></p>
                <small class="menu-category"><?= mnuE($menu['daftar_kategori']) ?></small>
                <strong class="menu-price">Rp <?= number_format((float) $menu['harga_menu'], 0, ',', '.') ?></strong>
                <span class="menu-state <?= $aktif ? 'menu-state--active' : 'menu-state--inactive' ?>">
                    <?= $aktif ? 'Aktif' : 'Nonaktif' ?>
                </span>
                <div class="menu-card__buttons" style="flex-wrap: wrap;">
                    <a class="button button-neutral" href="edit_menu.php?id=<?= (int) $menu['id_menu'] ?>">Edit</a>

                    <form method="post" action="menu_kelola.php" style="margin:0">
                        <input type="hidden" name="id_menu" value="<?= (int) $menu['id_menu'] ?>">
                        <input type="hidden" name="q" value="<?= mnuE($q) ?>">
                        <input type="hidden" name="aksi" value="<?= $aktif ? 'nonaktifkan' : 'aktifkan' ?>">
                        <button class="button button-status <?= $aktif ? 'button-neutral' : 'button-primary' ?>" type="submit">
                            <?= $aktif ? 'Nonaktifkan' : 'Aktifkan' ?>
                        </button>
                    </form>

                    <form method="post" action="menu_kelola.php" style="margin:0"
                          onsubmit="return confirm('Hapus menu &quot;<?= mnuE(addslashes($menu['nama_menu'])) ?>&quot;? Menu yang sudah pernah dipesan tidak bisa dihapus.');">
                        <input type="hidden" name="id_menu" value="<?= (int) $menu['id_menu'] ?>">
                        <input type="hidden" name="q" value="<?= mnuE($q) ?>">
                        <input type="hidden" name="aksi" value="hapus">
                        <button class="button button-delete" type="submit">Hapus</button>
                    </form>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
