<?php
// Menu dari database + tombol "tambah ke keranjang" yang benar-benar berfungsi.
// Tampilan kartu mengikuti index.php. Keranjang disimpan di localStorage dengan kunci
// 'keranjang_sikat' (format sama dengan yang dibaca keranjang.php), jadi plus/minus/hapus
// di keranjang.php langsung bekerja untuk item dari halaman ini.
session_start();
ob_start();

$halaman = 'menu';
require_once __DIR__ . '/includes/koneksi.php';
require_once __DIR__ . '/includes/queries/pesanan.php';
require_once __DIR__ . '/includes/pesanan_helper.php';

$q        = trim($_GET['q'] ?? '');
$kategori = trim($_GET['kategori'] ?? '');

$error = '';
try {
    $daftarKategori = pbKategoriMenu($pdo);
    $katalogMenu    = pbMenuTersedia($pdo, $q, $kategori);
} catch (PDOException $e) {
    error_log('menu-pesan gagal: ' . $e->getMessage());
    $daftarKategori = [];
    $katalogMenu    = [];
    $error = 'Menu belum bisa ditampilkan. Coba lagi nanti.';
}

include 'includes/header.php';
?>
<div class="container mt-5 mb-5">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="fw-bold mb-1 isi-red">Pilih Menu</h2>
            <p class="text-muted mb-0">Tambahkan menu ke keranjang, lalu lanjut ke checkout.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="keranjang.php" class="btn btn-outline-secondary text-dark">
                <i class="bi bi-cart3"></i> Keranjang <span id="jumlah-keranjang" class="badge bg-red">0</span>
            </a>
            <a href="checkout.php" class="btn bg-red text-white">Checkout</a>
        </div>
    </div>

    <?php if ($error !== '') : ?>
        <div class="alert alert-danger"><?= pbE($error) ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-3 mb-3">
            <form method="get" class="input-group">
                <?php if ($kategori !== '') : ?>
                    <input type="hidden" name="kategori" value="<?= pbE($kategori) ?>">
                <?php endif; ?>
                <input type="text" name="q" value="<?= pbE($q) ?>" class="form-control border-end-0" placeholder="Cari menu...">
                <button class="btn border border-start-0" type="submit"><i class="bi bi-search isi-red"></i></button>
            </form>
        </div>

        <div class="col-md-9 mb-3">
            <a href="menu-pesan.php<?= $q !== '' ? '?q=' . urlencode($q) : '' ?>"
               class="btn me-2 mb-2 <?= $kategori === '' ? 'bg-red text-white' : 'btn-outline-secondary text-dark' ?>">Semua</a>
            <?php foreach ($daftarKategori as $kat) : ?>
                <a href="menu-pesan.php?kategori=<?= urlencode($kat) ?><?= $q !== '' ? '&q=' . urlencode($q) : '' ?>"
                   class="btn me-2 mb-2 <?= $kategori === $kat ? 'bg-red text-white' : 'btn-outline-secondary text-dark' ?>">
                    <?= pbE($kat) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="row gx-4 gy-4 mt-1" id="daftar-menu">
        <?php if (empty($katalogMenu) && $error === '') : ?>
            <div class="col-12 text-center text-muted py-5">Menu tidak ditemukan.</div>
        <?php endif; ?>

        <?php foreach ($katalogMenu as $item) : ?>
            <div class="col-md-3">
                <div class="card h-100 shadow-sm border-0 rounded-4">
                    <img src="<?= pbE(pbGambarMenu($item['gambar_url'] ?? null, $item['nama_menu'])) ?>"
                         class="card-img-top rounded-top-4" style="height: 160px; object-fit: cover;"
                         alt="<?= pbE($item['nama_menu']) ?>">

                    <div class="card-body">
                        <h6 class="card-title fw-bold mb-1"><?= pbE($item['nama_menu']) ?></h6>
                        <small class="text-muted d-block mb-3"><?= pbE($item['deskripsi']) ?></small>

                        <span class="fw-bold fs-5 d-block mb-2"><?= pbRupiah($item['harga_menu']) ?></span>

                        <div class="d-flex gap-2">
                            <div class="input-group input-group-sm w-50 shadow-sm">
                                <button class="btn btn-outline-secondary border-secondary-subtle btn-qty-minus" type="button">-</button>
                                <input type="text" class="form-control text-center border-secondary-subtle input-qty" value="1" inputmode="numeric">
                                <button class="btn btn-outline-secondary border-secondary-subtle btn-qty-plus" type="button">+</button>
                            </div>
                            <button class="btn btn-success flex-grow-1 rounded-3 py-1 shadow-sm btn-tambah-keranjang" type="button"
                                    data-id="<?= (int) $item['id_menu'] ?>"
                                    data-nama="<?= pbE($item['nama_menu']) ?>"
                                    data-harga="<?= (float) $item['harga_menu'] ?>"
                                    data-deskripsi="<?= pbE($item['deskripsi']) ?>"
                                    aria-label="Tambah <?= pbE($item['nama_menu']) ?> ke keranjang">
                                <i class="bi bi-cart-fill"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- notifikasi kecil saat menu masuk keranjang -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
    <div id="notif-keranjang" class="toast align-items-center text-bg-success border-0" role="status" aria-live="polite">
        <div class="d-flex">
            <div class="toast-body" id="notif-keranjang-teks">Masuk keranjang.</div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var KUNCI = 'keranjang_sikat';

    function bacaKeranjang() {
        try { return JSON.parse(localStorage.getItem(KUNCI)) || []; } catch (e) { return []; }
    }
    function simpanKeranjang(keranjang) {
        localStorage.setItem(KUNCI, JSON.stringify(keranjang));
    }
    function perbaruiLencana() {
        var total = 0;
        bacaKeranjang().forEach(function (item) { total += Number(item.kuantitas) || 0; });
        document.getElementById('jumlah-keranjang').textContent = total;
    }
    function tampilkanNotif(teks) {
        document.getElementById('notif-keranjang-teks').textContent = teks;
        bootstrap.Toast.getOrCreateInstance(document.getElementById('notif-keranjang'), { delay: 2000 }).show();
    }

    perbaruiLencana();

    document.getElementById('daftar-menu').addEventListener('click', function (event) {
        var tombol = event.target.closest('button');
        if (!tombol) return;
        var kartu = tombol.closest('.card-body');
        if (!kartu) return;
        var inputJumlah = kartu.querySelector('.input-qty');

        if (tombol.classList.contains('btn-qty-plus')) {
            inputJumlah.value = (Number(inputJumlah.value) || 1) + 1;
        } else if (tombol.classList.contains('btn-qty-minus')) {
            inputJumlah.value = Math.max(1, (Number(inputJumlah.value) || 1) - 1);
        } else if (tombol.classList.contains('btn-tambah-keranjang')) {
            var jumlah = Math.max(1, parseInt(inputJumlah.value, 10) || 1);
            var id = Number(tombol.dataset.id);
            var keranjang = bacaKeranjang();
            var sudahAda = keranjang.find(function (item) { return Number(item.id) === id; });

            if (sudahAda) {
                sudahAda.kuantitas = Number(sudahAda.kuantitas) + jumlah;
            } else {
                keranjang.push({
                    id: id,
                    nama: tombol.dataset.nama,
                    harga: Number(tombol.dataset.harga),
                    deskripsi: tombol.dataset.deskripsi,
                    kuantitas: jumlah
                });
            }
            simpanKeranjang(keranjang);
            inputJumlah.value = 1;
            perbaruiLencana();
            tampilkanNotif(jumlah + ' × ' + tombol.dataset.nama + ' masuk keranjang.');
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
