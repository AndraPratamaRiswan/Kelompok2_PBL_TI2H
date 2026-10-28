
<?php
$halaman = 'menu';
require __DIR__ . '/../pembeli/includes/koneksi.php';
require __DIR__ . '/../pembeli/includes/queries/index.php';

$q        = trim($_GET['q'] ?? '');
$kategori = trim($_GET['kategori'] ?? '');

$katalog_menu = ambilKatalogMenu($pdo, $q, $kategori);

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

<!-- Search bar -->
<div class="row">
    
    <!-- Grid for component -->
    <div class="col-md-3 mb-3">
        <form method="get" class="input-group">
            <?php if ($kategori !== '') : ?>
                <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategori); ?>">
            <?php endif; ?>

            <input type="text" name="q" value="<?= htmlspecialchars($q); ?>"
                class="form-control rounded-start-pill-3 border-end-0" placeholder="Cari menu...">
            <button class="btn border border-start-0 rounded-end-pill-3" type="submit">
                <i class="bi bi-search isi-red"></i>
            </button>
        </form>
    </div>  
<!-- filter makanan -->
    <div class="col-md-9 mb-3">
        <?php
        $daftar_kategori = ['Makanan Oriental', 'Makanan Khas Indonesia', 'Makanan Western'];
        ?>

        <a href="menu.php" 
        class="btn me-2 mb-2 <?= $kategori === '' ? 'bg-red text-white' : 'btn-outline-secondary text-dark'; ?>">
            Semua
        </a>

        <?php foreach ($daftar_kategori as $kat) : ?>
            <a href="menu.php?kategori=<?= urlencode($kat); ?>" 
            class="btn me-2 mb-2 <?= $kategori === $kat ? 'bg-red text-white' : 'btn-outline-secondary text-dark'; ?>">
                <?= $kat; ?>
            </a>
        <?php endforeach; ?>
    </div>
<!-- filter makanan end -->

    
    
</div>

<!-- serach bar end -->

<!-- card product start -->
<!-- Baris Baru Khusus untuk Manajer Grid Produk -->
<div class="row gx-4 gy-4 mt-4">
    
    <!-- MULAI LOOPING DATABASE -->
    <?php if (empty($katalog_menu)) : ?>
    <div class="col-12 text-center text-muted py-5">
        Menu tidak ditemukan.
    </div>
    <?php endif; ?>

    <?php foreach ($katalog_menu as $item) : ?>
        <div class="col-md-3">
            <div class="card h-100 shadow-sm border-0 rounded-4">

                <img src="<?= htmlspecialchars($item['gambar_url']); ?>" 
                    class="card-img-top gambar-menu rounded-top-4" 
                    alt="<?= htmlspecialchars($item['nama_menu']); ?>">

                <div class="card-body">
                    <h6 class="card-title fw-bold mb-1">
                        <?= htmlspecialchars($item['nama_menu']); ?>
                    </h6>
                    <small class="text-muted d-block mb-3">
                        <?= htmlspecialchars($item['deskripsi']); ?>
                    </small>

                    <span class="fw-bold fs-5 d-block mb-2">
                        Rp <?= number_format($item['harga_menu'], 0, ',', '.'); ?>
                    </span>

                    <div class="d-flex gap-2">
                        <div class="input-group input-group-sm w-50 shadow-sm">
                            <button class="btn btn-outline-secondary border-secondary-subtle" type="button">-</button>
                            <input type="text" class="form-control text-center border-secondary-subtle" value="1">
                            <button class="btn btn-outline-secondary border-secondary-subtle" type="button">+</button>
                        </div>
                        <button class="btn btn-success flex-grow-1 rounded-3 py-1 shadow-sm">
                            <i class="bi bi-cart-fill"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    <?php endforeach; ?>
        <!-- AKHIR LOOPING DATABASE -->

</div>

<!-- card product end -->
<?php include 'includes/footer.php'; ?>