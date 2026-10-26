<?php 
$halaman = 'cek-pesanan'; 
include 'includes/header.php'; 
?>

<div class="card p-4 col-md-8 mx-auto mt-5 mb-5">
    <div class="row align-items-center">
        <!-- Kolom Kiri -->
        <div class="col-6 text-start">
            <!-- ID MENU -->
            <h5 class="fw-bold font-24 mb-1">SKT-002</h5>
            <!-- ID MENU END -->

            <!-- KIRIM  -->
            <p class="font-18 mb-0">Dikirim: Sabtu, 22 Des</p> 
            <!-- KIRIM END -->
        </div>

        <!-- Kolom Kanan -->
        <div class="col-6 text-end">
            <!-- [UNTUK BACKEND] Daftar class warna badge status... -->
            <span class="badge bg-red rounded-pill px-3 py-2 mb-2 d-inline-block font-18 fw-normal">
                Dikonfirmasi
            </span>
            <br>
            <br>
            <button type="button" class="btn bg-red text-white rounded-pill font-18 fw-normal" data-bs-toggle="collapse" data-bs-target="#demo" style="--bs-btn-padding-y: .120rem; --bs-btn-padding-x: 2.3rem; --bs-btn-font-size: .75rem;">
                Rincian
            </button>
        </div>
    </div>

    <div id="demo" class="collapse mt-4 pt-4 pb-4 border-top ">
        <div class="position-relative mt-2 mb-5">
            <!-- Bar -->
            <div style="position: absolute; top: 35px; left: 12%; right: 12%; height: 3px; background-color: #000; z-index: 1;"></div>
            
            <!-- 4 Kolom bundar -->
            <div class="row text-center position-relative" style="z-index: 2;">
                <!-- Status 1 -->
                <div class="col-3">
                    <p class="mb-2 fw-bold text-success font-18">Dikonfirmasi</p>
                    <div class="rounded-circle bg-success mx-auto" style="width: 24px; height: 24px;"></div>
                    <p class="mt-2 text-muted font-18">Rabu, 1 Jan</p>
                </div>
                <!-- Status 2 -->
                <div class="col-3">
                    <p class="mb-2 fw-bold font-18">Diproses</p>
                    <div class="rounded-circle bg-dark mx-auto" style="width: 24px; height: 24px;"></div>
                    <p class="mt-2 text-muted font-18">&nbsp;</p>
                </div>
                <!-- Status 3 -->
                <div class="col-3">
                    <p class="mb-2 fw-bold font-18">Dikirim</p>
                    <div class="rounded-circle bg-dark mx-auto" style="width: 24px; height: 24px;"></div>
                    <p class="mt-2 text-muted font-18">&nbsp;</p>
                </div>
                <!-- Status 4 -->
                <div class="col-3">
                    <p class="mb-2 fw-bold font-18">Selesai</p>
                    <div class="rounded-circle bg-dark mx-auto" style="width: 24px; height: 24px;"></div>
                    <p class="mt-2 text-muted font-18">&nbsp;</p>
                </div>
            </div>
        </div>

        <!-- [BACKEND MENU] BUAT LIST MENU DAN HARGA -->
         <!-- manu satuan -->
        <div class="row">
            <div class="col-8 text-start">
                 <p class="fw-light font-18 mb-0">Nasi box ayam x10</p>
            </div>

            <div class="col-4 text-end">
                <p class="fw-light font-18 ">rp.50000</p>
            </div>
            
        </div>
        <!-- menu satuan end -->


        <!-- total harga keranajnga -->
        <div class="row">
            <div class="col-6 text-start fw-bold font-18">TOTAL</div>
            <div class="col-6 text-end fw-bold font-18">rp.50000</div>
        </div>
        <!-- total harga keranjang end -->


        <!-- ceteak nota -->
        <div class="d-grid gap-2 mt-4">
                <button class="btn bg-red text-white font-18 fw-bold " type="button">Cetak Nota</button>
        </div>
        <!-- cetak nota enda -->

    </div>

    

</div>



<?php 
// Perhatikan bagian ini, pastikan path foldernya benar
include 'includes/footer.php'; 
?>