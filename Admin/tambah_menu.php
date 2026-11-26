<?php
require_once __DIR__ . '/includes/koneksi.php';
require_once __DIR__ . '/includes/menu_helper.php';
require_once __DIR__ . '/includes/gambar_upload.php';
require_once __DIR__ . '/includes/queries/tambah_menu.php';

$kategoriList = $pdo->query(
    'SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori'
)->fetchAll();

$pesanError = '';
$formData = [
    'nama_menu' => '',
    'harga_menu' => '',
    'deskripsi' => '',
    'id_kategori' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'nama_menu' => trim($_POST['nama_menu'] ?? ''),
        'harga_menu' => trim($_POST['harga_menu'] ?? ''),
        'deskripsi' => trim($_POST['deskripsi'] ?? ''),
        'id_kategori' => trim($_POST['id_kategori'] ?? ''),
    ];

    // gambar wajib diupload (tidak ada lagi pilihan gambar bawaan)
    $adaUpload = isset($_FILES['gambar_upload'])
        && ($_FILES['gambar_upload']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

    if (uploadMelebihiBatasServer()) {
        $pesanError = 'Ukuran gambar terlalu besar. Maksimal 2 MB.';
    } elseif ($formData['nama_menu'] === '') {
        $pesanError = 'Nama menu wajib diisi.';
    } elseif (!is_numeric($formData['harga_menu']) || (float) $formData['harga_menu'] < 1000) {
        $pesanError = 'Harga menu minimal Rp1.000.';
    } elseif (!in_array((int) $formData['id_kategori'], array_map('intval', array_column($kategoriList, 'id_kategori')), true)) {
        $pesanError = 'Pilih kategori menu.';
    } elseif (!$adaUpload) {
        $pesanError = 'Gambar menu wajib diupload.';
    } else {
        $gambarSimpan = null;
        $fileBaru = null;

        $hasilUpload = simpanGambarMenuUpload($_FILES['gambar_upload']);
        if ($hasilUpload['error'] !== '') {
            $pesanError = $hasilUpload['error'];
        } else {
            $fileBaru = $hasilUpload['path'];
            $gambarSimpan = $fileBaru;
        }

        if ($pesanError === '') {
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
                    [(int) $formData['id_kategori']],
                    $formData['nama_menu'],
                    (float) $formData['harga_menu'],
                    $formData['deskripsi'] !== '' ? $formData['deskripsi'] : null,
                    $gambarSimpan
                );

                header('Location: katalog.php?pesan=tambah');
                exit;
            } catch (Throwable $error) {
                // menu gagal disimpan: jangan tinggalkan file upload yang tidak terpakai
                hapusGambarUpload($fileBaru);
                $pesanError = 'Menu belum berhasil disimpan. Detail: ' . $error->getMessage();
            }
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
    <form method="post" action="tambah_menu.php" class="menu-form" enctype="multipart/form-data">
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
            <div class="select-with-action">
                <select name="id_kategori" id="kategori-select" required>
                    <option value="">Pilih kategori</option>
                    <?php foreach ($kategoriList as $kategori): ?>
                        <option value="<?= (int) $kategori['id_kategori'] ?>" <?= (string) $formData['id_kategori'] === (string) $kategori['id_kategori'] ? 'selected' : '' ?>>
                            <?= eMenu($kategori['nama_kategori']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="button button-neutral" id="btn-buka-kategori">+ Kategori</button>
            </div>
        </label>

        <label>
            Gambar menu
            <input type="file" name="gambar_upload" id="gambar-upload" accept="image/jpeg,image/png,image/webp" required>
            <small>Wajib diupload. Format JPG, PNG, atau WebP, maksimal 2 MB.</small>
            <img id="gambar-preview" alt="Pratinjau gambar" style="display:none; max-width:220px; max-height:160px; margin-top:6px; border-radius:8px; object-fit:cover;">
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
<!-- Overlay tambah kategori -->
<div class="modal-overlay" id="overlay-kategori" hidden>
    <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="judul-kategori">
        <h2 id="judul-kategori">Tambah Kategori</h2>
        <label class="modal-field">
            Nama kategori
            <input type="text" id="input-kategori" maxlength="50" autocomplete="off" placeholder="Contoh: Makanan Jepang">
        </label>
        <div class="modal-error" id="error-kategori" hidden></div>
        <div class="modal-actions">
            <button type="button" class="button button-primary" id="btn-simpan-kategori">Simpan</button>
            <button type="button" class="button button-neutral" id="btn-tutup-kategori">Batal</button>
        </div>
    </div>
</div>


<script>
// overlay tambah kategori: simpan lewat fetch supaya isian form menu (termasuk gambar) tidak hilang
(function () {
    var overlay   = document.getElementById('overlay-kategori');
    var input     = document.getElementById('input-kategori');
    var errorBox  = document.getElementById('error-kategori');
    var btnBuka   = document.getElementById('btn-buka-kategori');
    var btnSimpan = document.getElementById('btn-simpan-kategori');
    var btnTutup  = document.getElementById('btn-tutup-kategori');
    var select    = document.getElementById('kategori-select');
    if (!overlay) return;

    function tampilError(pesan) {
        errorBox.textContent = pesan;
        errorBox.hidden = false;
    }

    function buka() {
        input.value = '';
        errorBox.hidden = true;
        overlay.hidden = false;
        input.focus();
    }

    function tutup() {
        overlay.hidden = true;
        btnBuka.focus();
    }

    function simpan() {
        var nama = input.value.trim();
        if (nama === '') { tampilError('Nama kategori wajib diisi.'); input.focus(); return; }

        var data = new FormData();
        data.append('nama_kategori', nama);

        btnSimpan.disabled = true;
        btnSimpan.textContent = 'Menyimpan...';
        errorBox.hidden = true;

        fetch('kategori_tambah.php', { method: 'POST', body: data })
            .then(function (res) {
                // json() gagal kalau sesi login habis (server membalas halaman login)
                return res.json().catch(function () { return null; });
            })
            .then(function (hasil) {
                if (!hasil) { tampilError('Gagal menyimpan. Coba muat ulang halaman dan login kembali.'); return; }
                if (!hasil.ok) { tampilError(hasil.error || 'Kategori belum berhasil disimpan.'); return; }

                var opsi = document.createElement('option');
                opsi.value = hasil.id;
                opsi.textContent = hasil.nama;
                select.appendChild(opsi);
                select.value = String(hasil.id);   // langsung terpilih
                tutup();
            })
            .catch(function () { tampilError('Koneksi bermasalah. Coba lagi.'); })
            .then(function () {
                btnSimpan.disabled = false;
                btnSimpan.textContent = 'Simpan';
            });
    }

    btnBuka.addEventListener('click', buka);
    btnTutup.addEventListener('click', tutup);
    btnSimpan.addEventListener('click', simpan);

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); simpan(); }
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !overlay.hidden) tutup();
    });
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) tutup();   // klik area gelap di luar kotak
    });
})();
</script>
<script>
// pratinjau gambar yang dipilih + cek ukuran/format sebelum dikirim
(function () {
    var input = document.getElementById('gambar-upload');
    var preview = document.getElementById('gambar-preview');
    if (!input) return;
    input.addEventListener('change', function () {
        var file = input.files[0];
        if (!file) { preview.style.display = 'none'; return; }
        if (['image/jpeg', 'image/png', 'image/webp'].indexOf(file.type) === -1) {
            alert('Format gambar harus JPG, PNG, atau WebP.');
            input.value = ''; preview.style.display = 'none'; return;
        }
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran gambar maksimal 2 MB.');
            input.value = ''; preview.style.display = 'none'; return;
        }
        preview.src = URL.createObjectURL(file);
        preview.style.display = 'block';
    });
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>