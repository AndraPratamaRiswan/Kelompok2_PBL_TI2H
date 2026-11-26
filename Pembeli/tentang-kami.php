

<?php 
// 1. Buat kartu namanya dulu (jangan lupa tanda kutip!)
$halaman = 'tentang-kami'; 

// 2. Baru panggil header-nya agar dia bisa membaca variabel di atas
include 'includes/header.php'; 
?>


<!-- struktur 1  -->
<div class="container text-left ">

  <div class="row badgegx-5 align-items-center mt-5 mb-5 pb-4">
    <div class="col-md-6"   >
      <img src="assets/img/katering-1.jpg" class="img-fluid rounded-3" alt="Placeholder">
    </div>
    <div class="col-md-6">
        <h1 class="judul-testimoni mb-4 fw-semibold">Mengapa Harus Memilih Kami</h1>
      <p>Setiap pesanan disiapkan sesuai tanggal dan jam yang kamu tentukan, dengan menu yang bisa dipilih langsung di katalog. Pemesanan dilakukan lewat keranjang dan dikonfirmasi melalui WhatsApp, sehingga kamu bisa menyampaikan catatan khusus langsung ke penjual. Status pesanan juga bisa dipantau kapan saja di halaman Cek Pesanan.</p>
    </div>
</div>

<!-- struktur 1 end -->

<!-- struktur 2 -->

    <div class = "row gx-5 align-items-center mt-5 mb-5 pb-4">
    <div class="col-md-6">
        <h1 class="judul-tentang-kami mb-4 fw-semibold">Tentang Kami</h1>
 <p>Catering Nasi Kotak Malang by Tya melayani pemesanan nasi kotak untuk berbagai acara di Malang, seperti acara keluarga, kantor, dan kegiatan kelompok. Pemesanan minimal 15 box per menu dan dilakukan paling lambat tiga hari sebelum acara agar persiapan berjalan baik.</p>
    </div>
    <div class="col-md-6">
      <img src="assets/img/katering-2.jpg" class="img-fluid rounded-3" alt="Placeholder">
    </div>
  </div>
</div>
<!-- struktur 2 end -->






<?php include 'includes/footer.php'; ?>