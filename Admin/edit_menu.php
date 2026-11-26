<?php
require __DIR__ . '/includes/koneksi.php';
require __DIR__ . '/includes/queries/katalog.php';
require __DIR__ . '/includes/queries/edit_menu.php';
require_once __DIR__ . '/includes/gambar_upload.php';

$idMenu = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idMenu || $idMenu < 1) {
    header('Location: katalog.php');
    exit;
}

$error = '';
$kategoriList = [];
$menu = null;
$idKategori = 0;

try {
    $menu = ambilMenuById($pdo, $idMenu);
    if (!$menu) {
        header('Location: katalog.php');
        exit;
    }

    pastikanKategoriDasar($pdo);
    $kategoriList = ambilKategori($pdo);
    $idKategori = ambilIdKategoriMenu($pdo, $idMenu);
} catch (PDOException $e) {
    $error = 'Data menu gagal dibaca. Detail: ' . $e->getMessage();
}

$nama = trim($_POST['nama_menu'] ?? ($menu['nama_menu'] ?? ''));
$deskripsi = trim($_POST['deskripsi'] ?? ($menu['deskripsi'] ?? ''));
$harga = trim($_POST['harga_menu'] ?? (string) ($menu['harga_menu'] ?? ''));
$idKategoriForm = (int) ($_POST['id_kategori'] ?? $idKategori);
$gambarAwalDb = (string) ($menu['gambar_url'] ?? '');

// Gambar hanya bisa diganti lewat upload. Kalau tidak ada file dipilih, gambar sekarang dipertahankan apa adanya.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error === '' && $menu) {
    $adaUpload = isset($_FILES['gambar_upload'])
        && ($_FILES['gambar_upload']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

    if (uploadMelebihiBatasServer()) {
        $error = 'Ukuran gambar terlalu besar. Maksimal 2 MB.';
    } elseif ($nama === '' || !is_numeric($harga) || (float) $harga <= 0) {
        $error = 'Isi nama dan harga yang valid terlebih dahulu.';
    } elseif (!in_array($idKategoriForm, array_map('intval', array_column($kategoriList, 'id_kategori')), true)) {
        $error = 'Pilih kategori menu.';
    } else {
        $fileBaru = null;
        $gambarSimpan = $gambarAwalDb;

        if ($adaUpload) {
            $hasilUpload = simpanGambarMenuUpload($_FILES['gambar_upload']);
            if ($hasilUpload['error'] !== '') {
                $error = $hasilUpload['error'];
            } else {
                $fileBaru = $hasilUpload['path'];
                $gambarSimpan = $fileBaru;
            }
        }

        if ($error === '') {
            try {
                editMenu(
                    $pdo,
                    $idMenu,
                    [$idKategoriForm],
                    $nama,
                    (float) $harga,
                    $deskripsi !== '' ? $deskripsi : null,
                    $gambarSimpan
                );

                // gambar hasil upload yang lama sudah diganti: hapus filenya (gambar bawaan tidak pernah dihapus)
                if ($fileBaru !== null) {
                    hapusGambarUpload($gambarAwalDb);
                }

                header('Location: katalog.php?pesan=diedit');
                exit;
            } catch (Throwable $e) {
                hapusGambarUpload($fileBaru);
                $error = 'Perubahan menu gagal disimpan. Detail: ' . $e->getMessage();
            }
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
        <form method="post" action="edit_menu.php?id=<?= (int) $idMenu ?>" class="menu-form" enctype="multipart/form-data">
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
                <select name="id_kategori" required <?= empty($kategoriList) ? 'disabled' : '' ?>>
                    <option value="">Pilih kategori</option>
                    <?php foreach ($kategoriList as $kategori): ?>
                        <option value="<?= (int) $kategori['id_kategori'] ?>" <?= $idKategoriForm === (int) $kategori['id_kategori'] ? 'selected' : '' ?>><?= htmlspecialchars($kategori['nama_kategori']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Gambar menu
                <input type="file" name="gambar_upload" id="gambar-upload" accept="image/jpeg,image/png,image/webp">
                <small>Pilih file untuk mengganti gambar (JPG, PNG, atau WebP, maksimal 2 MB). Kosongkan kalau gambar tidak diganti.</small>
                <img id="gambar-preview" src="<?= htmlspecialchars(gambarMenuLokal($menu['gambar_url'] ?? null, $menu['nama_menu'])) ?>" alt="Gambar menu saat ini"
                     style="max-width:220px; max-height:160px; margin-top:6px; border-radius:8px; object-fit:cover;">
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

<script>
// pratinjau gambar yang dipilih + cek ukuran/format sebelum dikirim
(function () {
    var input = document.getElementById('gambar-upload');
    var preview = document.getElementById('gambar-preview');
    if (!input) return;
    var gambarSemula = preview.getAttribute('src');
    input.addEventListener('change', function () {
        var file = input.files[0];
        if (!file) { preview.src = gambarSemula; return; }
        if (['image/jpeg', 'image/png', 'image/webp'].indexOf(file.type) === -1) {
            alert('Format gambar harus JPG, PNG, atau WebP.');
            input.value = ''; preview.src = gambarSemula; return;
        }
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran gambar maksimal 2 MB.');
            input.value = ''; preview.src = gambarSemula; return;
        }
        preview.src = URL.createObjectURL(file);
    });
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>