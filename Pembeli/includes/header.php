<?php
// Header dipakai semua halaman pembeli. Status login dibaca dari session.
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
$sudahLogin = isset($_SESSION['id_pembeli']);

// jumlah box di keranjang (keranjang disimpan di session oleh index.php)
$jumlahKeranjang = 0;
if ($sudahLogin && !empty($_SESSION['keranjang']) && is_array($_SESSION['keranjang'])) {
    foreach ($_SESSION['keranjang'] as $itemKeranjang) {
        $jumlahKeranjang += (int) ($itemKeranjang['kuantitas'] ?? 0);
    }
}
$cssTambahan = $cssTambahan ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=1">
    <?php foreach ($cssTambahan as $fileCss) : ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($fileCss, ENT_QUOTES, 'UTF-8') ?>">
    <?php endforeach; ?>
 
     <!-- Bootstrap Icons CDN -->
     <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
     />

     <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@100..900&display=swap" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cedarville+Cursive&display=swap" rel="stylesheet">

    
     
   
     
    <title>Navbar</title>
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-krim py-3   ">
  <div class="container">
    <a class="navbar-brand" href="#"><img src="assets/img/logo.png" alt="Bootstrap" width="150" height="50"></a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
      <div class="navbar-nav ms-auto gap-4 align-items-center">
    <a class="nav-link <?php echo ($halaman == 'menu') ? 'active' : ''; ?>" href="index.php">Menu</a>
    
    <a class="nav-link <?php echo ($halaman == 'cek-pesanan') ? 'active' : ''; ?>" href="cek-pesanan.php">Cek Pesanan</a>
    
    <a class="nav-link <?php echo ($halaman == 'tentang-kami') ? 'active' : ''; ?>" href="tentang-kami.php">Tentang kami</a>
    
    <a class="nav-link position-relative <?php echo ($halaman == 'keranjang') ? 'active' : ''; ?>" href="keranjang.php" aria-label="Keranjang">
        <i class="bi bi-cart3" style="-webkit-text-stroke: 1px;"></i>
        <?php if ($jumlahKeranjang > 0) : ?>
            <span class="badge rounded-pill bg-red position-absolute top-0 start-100 translate-middle" style="font-size: 11px;"><?= (int) $jumlahKeranjang ?></span>
        <?php endif; ?>
    </a>

    <?php if ($sudahLogin) : ?>
        <a class="nav-link" href="logout.php">Keluar</a>
    <?php else : ?>
        <a class="nav-link" href="login.php">Masuk</a>
    <?php endif; ?>
        
      </div>
    </div>
  </div>
</nav>
    
