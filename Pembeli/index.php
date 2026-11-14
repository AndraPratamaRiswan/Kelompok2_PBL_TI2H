<?php
// Halaman menu pembeli. Menu dibaca dari database, dan di sinilah menu dipilih lalu
// dimasukkan ke keranjang (disimpan di session). Keranjang sendiri (keranjang.php)
// hanya menampilkan rangkuman pesanan. Menambah menu wajib login dulu.
session_start();

$halaman = 'menu';
date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/includes/koneksi.php';
require_once __DIR__ . '/includes/queries/index.php';    // ambilKatalogMenu()
require_once __DIR__ . '/includes/queries/pesanan.php';  // pbKategoriMenu(), pbMenuByIds()
require_once __DIR__ . '/includes/pesanan_helper.php';   // pbE(), pbRupiah(), pbGambarMenu(), pbTambahKeKeranjang()

$sudahLogin = isset($_SESSION['id_pembeli']);

$q        = trim($_GET['q'] ?? '');
$kategori = trim($_GET['kategori'] ?? '');

// alamat halaman ini lengkap dengan pencarian dan kategori yang sedang aktif
function urlMenu(string $q, string $kategori, string $anchor = ''): string
{
    $query = [];
    if ($q !== '') {
        $query['q'] = $q;
    }
    if ($kategori !== '') {
        $query['kategori'] = $kategori;
    }
    return 'index.php' . ($query ? '?' . http_build_query($query) : '') . $anchor;
}

// ---------- tombol keranjang di kartu menu (POST lalu redirect) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'tambah') {
    // belum login: tidak boleh memesan, arahkan ke halaman masuk
    if (!$sudahLogin) {
        header('Location: login.php?status=wajib&next=index.php');
        exit;
    }

    $idMenu = filter_input(INPUT_POST, 'id_menu', FILTER_VALIDATE_INT);
    $jumlah = filter_var($_POST['jumlah'] ?? '', FILTER_VALIDATE_INT);
    if ($jumlah === false || $jumlah < 1) {
        $jumlah = 0; // ditolak di pbTambahKeKeranjang (di bawah minimal)
    }

    try {
        if ($idMenu && $idMenu > 0) {
            $_SESSION['flash_keranjang'] = pbTambahKeKeranjang($pdo, $idMenu, $jumlah);
        } else {
            $_SESSION['flash_keranjang'] = ['danger', 'Menu tidak valid.'];
        }
    } catch (Throwable $e) {
        error_log('Tambah keranjang gagal: ' . $e->getMessage());
        $_SESSION['flash_keranjang'] = ['danger', 'Terjadi kesalahan. Silakan coba lagi.'];
    }

    header('Location: ' . urlMenu($q, $kategori, $idMenu ? '#menu-' . (int) $idMenu : ''));
    exit;
}

$flash = $_SESSION['flash_keranjang'] ?? null;
unset($_SESSION['flash_keranjang']);

$error = '';
try {
    $daftar_kategori = pbKategoriMenu($pdo);
    $katalog_menu    = ambilKatalogMenu($pdo, $q, $kategori);
} catch (PDOException $e) {
    error_log('Baca menu gagal: ' . $e->getMessage());
    $daftar_kategori = [];
    $katalog_menu    = [];
    $error = 'Menu belum bisa ditampilkan. Coba lagi nanti.';
}

include 'includes/header.php';
?>
<!-- Jumbotron -->
<div class="container mt-5 mb-5">

<div class="p-1 mb-4 rounded-3 bg-red text-white">
  <div class="container-fluid py-2">
    
    
    <h2 class="fw-bold teks-jumbotron-judul">Custom Jumbotron</h2>
    
    
    <p class="col-md-10 fw-light teks-jumbotron-deskripsi">Using a series of utilities, you can create this jumbotron, just like the one in previous versions of Bootstrap. Remix and restyle it to your liking.</p>
    
  </div>
</div>

<!-- jumbotron end -->

<?php if ($flash) : ?>
    <div class="alert alert-<?= pbE($flash[0]) ?> py-2"><?= pbE($flash[1]) ?>
        <?php if ($flash[0] === 'success') : ?>
            <a href="keranjang.php" class="alert-link ms-2">Lihat keranjang</a>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php if ($error !== '') : ?>
    <div class="alert alert-danger"><?= pbE($error) ?></div>
<?php endif; ?>
<div class="alert alert-info py-2">Pemesanan minimal <strong><?= PB_MIN_BOX ?> box</strong> untuk setiap menu.</div>
<?php if (!$sudahLogin) : ?>
    <div class="alert alert-warning py-2">
        Kamu bisa melihat menu, tetapi harus <a href="login.php?next=index.php" class="alert-link">masuk</a> dulu untuk memesan.
    </div>
<?php endif; ?>

<!-- Search bar -->
<div class="row">
    
    <!-- Grid for component -->
    <div class="col-md-3 mb-3">
        <form method="get" action="index.php" class="input-group">
            <?php if ($kategori !== '') : ?>
                <input type="hidden" name="kategori" value="<?= pbE($kategori) ?>">
            <?php endif; ?>

            <input type="text" name="q" value="<?= pbE($q) ?>"
                class="form-control rounded-start-pill-3 border-end-0" placeholder="Cari menu...">
            <button class="btn border border-start-0 rounded-end-pill-3" type="submit">
                <i class="bi bi-search isi-red"></i>
            </button>
        </form>
    </div>  
<!-- filter makanan: kategori diambil dari database -->
    <div class="col-md-9 mb-3">
        <a href="<?= pbE(urlMenu($q, '')) ?>"
        class="btn me-2 mb-2 <?= $kategori === '' ? 'bg-red text-white' : 'btn-outline-secondary text-dark'; ?>">
            Semua
        </a>

        <?php foreach ($daftar_kategori as $kat) : ?>
            <a href="<?= pbE(urlMenu($q, $kat)) ?>"
            class="btn me-2 mb-2 <?= $kategori === $kat ? 'bg-red text-white' : 'btn-outline-secondary text-dark'; ?>">
                <?= pbE($kat) ?>
            </a>
        <?php endforeach; ?>
    </div>
<!-- filter makanan end -->

    
    
</div>

<!-- serach bar end -->

<!-- card product start -->
<!-- Baris Baru Khusus untuk Manajer Grid Produk -->
<div class="row gx-4 gy-4 mt-4" id="daftar-menu">
    
    <!-- MULAI LOOPING DATABASE -->
    <?php if (empty($katalog_menu) && $error === '') : ?>
    <div class="col-12 text-center text-muted py-5">
        Menu tidak ditemukan.
    </div>
    <?php endif; ?>

    <?php foreach ($katalog_menu as $item) : ?>
        <div class="col-md-3" id="menu-<?= (int) $item['id_menu'] ?>">
            <div class="card h-100 shadow-sm border-0 rounded-4">

                <img src="<?= pbE(pbGambarMenu($item['gambar_url'] ?? null, $item['nama_menu'])) ?>" 
                    class="card-img-top gambar-menu rounded-top-4" 
                    style="height: 160px; object-fit: cover;"
                    alt="<?= pbE($item['nama_menu']) ?>">

                <div class="card-body">
                    <h6 class="card-title fw-bold mb-1">
                        <?= pbE($item['nama_menu']) ?>
                    </h6>
                    <small class="text-muted d-block mb-3">
                        <?= pbE($item['deskripsi']) ?>
                    </small>

                    <span class="fw-bold fs-5 d-block mb-2">
                        <?= pbRupiah($item['harga_menu']) ?>
                    </span>

                    <form method="post" action="<?= pbE(urlMenu($q, $kategori)) ?>" class="d-flex gap-2">
                        <input type="hidden" name="aksi" value="tambah">
                        <input type="hidden" name="id_menu" value="<?= (int) $item['id_menu'] ?>">

                        <div class="input-group input-group-sm w-50 shadow-sm">
                            <button class="btn btn-outline-secondary border-secondary-subtle btn-qty-minus" type="button">-</button>
                            <input type="text" name="jumlah" inputmode="numeric" value="<?= PB_MIN_BOX ?>"
                                   class="form-control text-center border-secondary-subtle input-qty">
                            <button class="btn btn-outline-secondary border-secondary-subtle btn-qty-plus" type="button">+</button>
                        </div>

                        <?php if ($sudahLogin) : ?>
                            <button type="submit" class="btn btn-success flex-grow-1 rounded-3 py-1 shadow-sm"
                                    aria-label="Tambah <?= pbE($item['nama_menu']) ?> ke keranjang">
                                <i class="bi bi-cart-fill"></i>
                            </button>
                        <?php else : ?>
                            <!-- belum login: ikon keranjang mengarah ke halaman masuk -->
                            <a href="login.php?status=wajib&amp;next=index.php"
                               class="btn btn-success flex-grow-1 rounded-3 py-1 shadow-sm"
                               aria-label="Masuk dulu untuk memesan <?= pbE($item['nama_menu']) ?>"
                               title="Masuk dulu untuk memesan">
                                <i class="bi bi-cart-fill"></i>
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

            </div>
        </div>
    <?php endforeach; ?>
        <!-- AKHIR LOOPING DATABASE -->

</div>

<!-- card product end -->
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var MIN_BOX = <?= (int) PB_MIN_BOX ?>;
    var daftar = document.getElementById('daftar-menu');
    if (!daftar) return;

    // tombol - dan + jumlah di kartu menu
    daftar.addEventListener('click', function (event) {
        var tombol = event.target.closest('button');
        if (!tombol) return;
        var form = tombol.closest('form');
        var input = form ? form.querySelector('.input-qty') : null;
        if (!input) return;
        var sekarang = parseInt(input.value, 10) || MIN_BOX;
        if (tombol.classList.contains('btn-qty-plus')) {
            input.value = Math.max(MIN_BOX, sekarang + 1);
        } else if (tombol.classList.contains('btn-qty-minus')) {
            input.value = Math.max(MIN_BOX, sekarang - 1);
        }
    });
});
</script>
<?php include 'includes/footer.php'; ?>
