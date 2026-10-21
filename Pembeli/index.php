
<?php 
$halaman = 'menu'; 

// Simulasi data dari tabel 'menu_katering'
$katalog_menu = [
    [
        'nama' => 'Chicken breast frozen',
        'porsi' => '450-500gr /pack',
        'harga' => 29000,
        'gambar' => 'https://picsum.photos/400/300?random=1'
    ],
    [
        'nama' => 'Ayam Bakar Madu',
        'porsi' => '1 Porsi Lengkap',
        'harga' => 35000,
        'gambar' => 'https://picsum.photos/400/300?random=2'
    ],
    [
        'nama' => 'Nasi Kuning Bento',
        'porsi' => '1 Porsi Anak',
        'harga' => 25000,
        'gambar' => 'https://picsum.photos/400/300?random=3'
    ],
    [
        'nama' => 'Sapi Lada Hitam',
        'porsi' => '300gr /pack',
        'harga' => 45000,
        'gambar' => 'https://picsum.photos/400/300?random=4'
    ],
    [
        'nama' => 'Sapi Lada Hitam',
        'porsi' => '300gr /pack',
        'harga' => 45000,
        'gambar' => 'https://picsum.photos/400/300?random=4'
    ],
    [
        'nama' => 'Sapi Lada Hitam',
        'porsi' => '300gr /pack',
        'harga' => 45000,
        'gambar' => 'https://picsum.photos/400/300?random=4'
    ]

];

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
        
        <!--  Komponen Search Bar -->
        <div class="input-group">
            
            <input type="text" class="form-control rounded-start-pill-3 border-end-0" placeholder="Cari menu...">
            
            <!-- Tombol Ikon, Garis batas kiri dihapus dengan border-start-0, background diubah jadi putih -->
            <button class="btn border  border-start-0 rounded-end-pill-3" type="button">
                <!-- Ikon Kaca Pembesar (Warna merah disesuaikan dengan tema di css) -->
                <i class="bi bi-search isi-red"></i>
            </button>
            
        </div>
        
    </div>
<!-- filter makanan -->
<div class="col-md-9 mb-3">
    
    <button type="button" class="btn btn-outline-secondary  text-dark me-2 mb-2">Makanan Oriental</button>
    
    
    <button type="button" class="btn bg-red text-white  me-2 mb-2">Semua</button>
    
    
    <button type="button" class="btn btn-outline-secondary  text-dark me-2 mb-2">Makanan Khas Indonesia</button>
    
    
    <button type="button" class="btn btn-outline-secondary l text-dark me-2 mb-2">Makanan Western</button>
</div>
<!-- filter makanan end -->

    
    
</div>

<!-- serach bar end -->

<!-- card product start -->
<!-- Baris Baru Khusus untuk Manajer Grid Produk -->
<div class="row gx-4 gy-4 mt-4">
    
    <!-- MULAI LOOPING DATABASE -->
    <?php foreach ($katalog_menu as $item) : ?>
        
        <div class="col-md-3">
            <div class="card h-100 shadow-sm border-0 rounded-4">
                
                <!-- Injeksi Variabel Gambar -->
                <img src="<?= $item['gambar']; ?>" class="card-img-top gambar-menu rounded-top-4" alt="<?= $item['nama']; ?>">
                
                <div class="card-body">
                    <!-- Injeksi Variabel Judul & Porsi -->
                    <h6 class="card-title fw-bold mb-1"><?= $item['nama']; ?></h6>
                    <small class="text-muted d-block mb-3"><?= $item['porsi']; ?></small>
                    
                    <!-- Injeksi Variabel Harga dengan Format Rupiah -->
                    <span class="fw-bold fs-5 d-block mb-2">
                        Rp <?= number_format($item['harga'], 0, ',', '.'); ?>
                    </span>

                    <!-- Area Action, UNTUK Tombol - 1 + dan Keranjang -->
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