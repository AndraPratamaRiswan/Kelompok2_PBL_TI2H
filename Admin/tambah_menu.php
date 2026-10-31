<?php
require_once __DIR__ . '/includes/koneksi.php';
require_once __DIR__ . '/includes/menu_helper.php';
require_once __DIR__ . '/includes/queries/tambah_menu.php';

$pilihanGambar = pilihanGambarMenu();
$kategoriList = $pdo->query(
    'SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori'
)->fetchAll();

$pesanError = '';
$formData = [
    'nama_menu' => '',
    'harga_menu' => '',
    'deskripsi' => '',
    'id_kategori' => '',
    'gambar_url' => 'nasikuning.jpg',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'nama_menu' => trim($_POST['nama_menu'] ?? ''),
        'harga_menu' => trim($_POST['harga_menu'] ?? ''),
        'deskripsi' => trim($_POST['deskripsi'] ?? ''),
        'id_kategori' => trim($_POST['id_kategori'] ?? ''),
        'gambar_url' => $_POST['gambar_url'] ?? 'nasikuning.jpg',
    ];

    if ($formData['nama_menu'] === '') {
        $pesanError = 'Nama menu wajib diisi.';
    } elseif (!is_numeric($formData['harga_menu']) || (float) $formData['harga_menu'] < 1000) {
        $pesanError = 'Harga menu minimal Rp1.000.';
    } elseif (!isset($pilihanGambar[$formData['gambar_url']])) {
        $pesanError = 'Pilih gambar yang tersedia.';
    } else {
        try {
    // Cari admin yang sudah ada supaya fk id_admin valid
            $idAdmin = $pdo->query(
                'SELECT id_admin FROM admin ORDER BY id_admin LIMIT 1'
            )->fetchColumn();

            if ($idAdmin === false) {
                throw new RuntimeException('Tabel admin masih kosong. Masukkan data admin dulu.');
            }

            tambahMenu(
                $pdo,
                (int) $idAdmin,
                $formData['id_kategori'] !== '' ? [(int) $formData['id_kategori']] : [],
                $formData['nama_menu'],
                (float) $formData['harga_menu'],
                $formData['deskripsi'] !== '' ? $formData['deskripsi'] : null,
                $formData['gambar_url']
            );

            header('Location: katalog.php?pesan=tambah');
            exit;
        } catch (Throwable $error) {
            $pesanError = 'Menu belum berhasil disimpan. Detail: ' . $error->getMessage();
        }
    }
}

$pageTitle = 'Tambah Menu';
$activeNav = 'katalog';
require __DIR__ . '/includes/header.php';
?>

<section class="page-heading">
    <div>
        <h1>Tambah Menu</h1>
        <p class="page-description">Isi data menu baru, lalu simpan ke database.</p>
    </div>
    <a class="button button-neutral"
                href="katalog.php"
                onclick="return confirm('Yakin ingin membatalkan? Data yang belum disimpan akan hilang.');">
                Kembali ke Katalog
            </a>
</section>

<?php if ($pesanError !== ''): ?>
    <div class="form-message form-message--error"><?= eMenu($pesanError) ?></div>
<?php endif; ?>

<section class="menu-form-panel">
    <form method="post" action="tambah_menu.php" class="menu-form">
        <label>
            Nama menu
            <input type="text" name="nama_menu" maxlength="100" required value="<?= eMenu($formData['nama_menu']) ?>">
        </label>

        <label>
            Harga (Rp)
            <input type="number" name="harga_menu" min="1000" step="1000" required value="<?= eMenu($formData['harga_menu']) ?>">
        </label>

        <label>
            Deskripsi
            <textarea name="deskripsi" maxlength="255" rows="3"><?= eMenu($formData['deskripsi']) ?></textarea>
        </label>

        <label>
            Kategori
            <select name="id_kategori">
                <option value="">Belum pilih kategori</option>
                <?php foreach ($kategoriList as $kategori): ?>
                    <option value="<?= (int) $kategori['id_kategori'] ?>" <?= (string) $formData['id_kategori'] === (string) $kategori['id_kategori'] ? 'selected' : '' ?>>
                        <?= eMenu($kategori['nama_kategori']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Gambar menu
            <select name="gambar_url" required>
                <?php foreach ($pilihanGambar as $namaFile => $labelGambar): ?>
                    <option value="<?= eMenu($namaFile) ?>" <?= $formData['gambar_url'] === $namaFile ? 'selected' : '' ?>>
                        <?= eMenu($labelGambar) ?> (<?= eMenu($namaFile) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <small>Pakai gambar yang sudah ada di folder Admin/assets/img.</small>
        </label>

        <div class="menu-form-actions">
            <button class="button button-primary" type="submit">Simpan Menu</button>
            <a class="button button-neutral"
                href="katalog.php"
                onclick="return confirm('Yakin ingin membatalkan? Data yang belum disimpan akan hilang.');">
                Batal
            </a>
        </div>
    </form>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>