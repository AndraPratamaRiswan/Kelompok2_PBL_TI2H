<?php
require __DIR__ . '/includes/koneksi.php';
require __DIR__ . '/includes/queries/katalog.php';
require __DIR__ . '/includes/queries/edit_menu.php';

$idMenu = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idMenu || $idMenu < 1) {
    header('Location: katalog.php');
    exit;
}

$error = '';
$kategoriList = [];
$gambarList = daftarGambarMenu();
$menu = null;
$idKategori = 0;

try {
    $menu = ambilMenuById($pdo, $idMenu);
    if (!$menu) {
        header('Location: katalog.php');
        exit;
    }

    $kategoriList = ambilKategori($pdo);
    $idKategori = ambilIdKategoriMenu($pdo, $idMenu);
} catch (PDOException $e) {
    $error = 'Data menu gagal dibaca. Detail: ' . $e->getMessage();
}

$nama = trim($_POST['nama_menu'] ?? ($menu['nama_menu'] ?? ''));
$deskripsi = trim($_POST['deskripsi'] ?? ($menu['deskripsi'] ?? ''));
$harga = trim($_POST['harga_menu'] ?? (string) ($menu['harga_menu'] ?? ''));
$idKategoriForm = (int) ($_POST['id_kategori'] ?? $idKategori);
$gambarAwal = basename(parse_url((string) ($menu['gambar_url'] ?? ''), PHP_URL_PATH) ?: (string) ($menu['gambar_url'] ?? ''));
$gambarFile = $_POST['gambar_file'] ?? (isset($gambarList[$gambarAwal]) ? $gambarAwal : 'nasikuning.jpg');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error === '' && $menu) {
    if ($nama === '' || !is_numeric($harga) || (float) $harga <= 0 ||!isset($gambarList[$gambarFile])) {
        $error = 'Isi nama, harga yang valid, dan gambar terlebih dahulu.';
    } else {
        try {
            editMenu(
                $pdo,
                $idMenu,
                $idKategoriForm > 0 ? [$idKategoriForm] : [],
                $nama,
                (float) $harga,
                $deskripsi !== '' ? $deskripsi : null,
                '../Admin/assets/img/' . $gambarFile
            );

    header('Location: katalog.php?pesan=diedit');
    exit;
} catch (Throwable $e) {
    $error = 'Perubahan menu gagal disimpan. Detail: ' . $e->getMessage();
}
    }
}

$pageTitle = 'Edit Menu';
$activeNav = 'katalog';
require __DIR__ . '/includes/header.php';
?>

<section class="page-heading">
    <div>
        <h1>Katalog Menu</h1>
        <p class="page-description">Ubah informasi menu yang sudah ada.</p>
    </div>
    <a class="button button-neutral" href="katalog.php" onclick="return confirm('Batal mengedit menu? Perubahan yang belum disimpan akan hilang.');">Kembali ke Katalog</a>
</section>

<section class="menu-form-panel">
    <div class="menu-form-heading"><h2>Edit Menu</h2></div>

    <?php if ($error !== ''): ?>
        <div class="form-message form-message--error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($menu): ?>
        <form method="post" action="edit_menu.php?id=<?= (int) $idMenu ?>" class="menu-form">
            <label>Nama menu
                <input type="text" name="nama_menu" maxlength="100" value="<?= htmlspecialchars($nama) ?>" required>
            </label>
            <label>Harga (Rp)
                <input type="number" name="harga_menu" min="1000" step="1000" value="<?= htmlspecialchars($harga) ?>" required>
            </label>
            <label>Deskripsi
                <textarea name="deskripsi" maxlength="255" rows="3"><?= htmlspecialchars($deskripsi) ?></textarea>
            </label>
            <label>Kategori
                <select name="id_kategori" <?= empty($kategoriList) ? 'disabled' : '' ?>>
                    <option value="">Pilih kategori</option>
                    <?php foreach ($kategoriList as $kategori): ?>
                        <option value="<?= (int) $kategori['id_kategori'] ?>" <?= $idKategoriForm === (int) $kategori['id_kategori'] ? 'selected' : '' ?>><?= htmlspecialchars($kategori['nama_kategori']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Gambar menu
                <select name="gambar_file" required>
                    <?php foreach ($gambarList as $file => $label): ?>
                        <option value="<?= htmlspecialchars($file) ?>" <?= $gambarFile === $file ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <small>Gambar diambil dari folder Admin/assets/img.</small>
            </label>
            <div class="menu-form-actions">
                <button class="button button-primary" type="submit">Simpan Perubahan</button>
                <a class="button button-neutral" href="katalog.php" onclick="return confirm('Batal mengedit menu? Perubahan yang belum disimpan akan hilang.');">Batal</a>
            </div>
        </form>
    <?php else: ?>
        <p>Menu tidak ditemukan.</p>
        <a class="button button-neutral" href="katalog.php">Kembali ke Katalog</a>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>