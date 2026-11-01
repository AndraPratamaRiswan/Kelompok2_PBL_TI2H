<?php 
$halaman = 'keranjang'; 

include 'includes/header.php'; 
?>
<div class="container py-4">

<div class = "row">

    <div class="col-md-8">

    <div class="bg-red rounded-3 p-3 teks-jumbotron-judul">

    <div class="row align-content-between fw-medium" style="font-size: 18px;">
        <div class="col-4 text-start ">Menu</div>
        <div class="col-4 text-center">Kuantitas</div>
        <div class="col-4 text-end ">Harga</div>
    </div> 

    </div>


    <div id ="keranjang-container"></div>


    
    </div>

<div class="col-md-4">
    
    <!-- 1. BANGUN KARDUSNYA DULU (Putih, punya padding, dan ada bayangan tipis) -->
    <div class="bg-white rounded-3 p-4 border shadow-sm">

        <h6 class="fw-medium mb-4" style="font-size: 18px;">Rangkuman Pesanan</h6>

        <div id = "ringkasan-item"></div>

        

        
        <hr class=" border-2 opacity-10">
        <!-- 2. INI BARIS UNTUK TOTAL HARGA -->
        <div class="row mt-4 mb-4">
            <div class="col-8 fw-semibold" style="font-size: 16px;">
                <p>Total</p>
            </div>  
            <div class="col-4 text-end fw-semibold" style="font-size: 16px;">
                <!-- ID TOTAL-HARGA HARUS ADA DI SINI -->
                <p id="total-harga">Rp. 0</p>
            </div>
        </div>

        <hr class=" border-2 opacity-10 mt-1">
        <div class="mb-3 fw-semibold">
                <p class="mb-2" style = "font-size: 16px;">Atas Nama</p>
            <input id="nama-pemesan" class="form-control bg-white rounded-3 p-3 border shadow-sm" type="text" placeholder="Default input" aria-label="default input example">
            
            

            
        </div>

 


        <div class="row">
    <div class="col-6 mb-3 fw-semibold">
        <p class="mb-2" style="font-size: 14px;">Tanggal Kirim</p>
        <input id="tanggal-pesan" type="date" class="form-control bg-white rounded-3 p-3 border shadow-sm">
    </div>
    <div class="col-6 mb-3 fw-semibold">
        <p class="mb-2" style="font-size: 14px;">Jam</p>
        <!-- Tambahkan id="jam-pesan" di baris bawah ini -->
        <input id="jam-pesan" type="time" class="form-control bg-white rounded-3 p-3 border shadow-sm">
    </div>
</div>

        


        <div class="mb-3 fw-semibold">
                <p class="mb-2" style = "font-size: 16px;">Masukkan Alamat</p>
            <input id="alamat-pesan" class="form-control bg-white rounded-3 p-3 border shadow-sm" type="text" placeholder="Default input" aria-label="default input example">
            
            

            
        </div>

        <div class="mb-4 fw-semibold ">
                <p class="mb-2" style = "font-size: 16px;">Catatan tambahan opsional</p>
            <input id="catatan-pesan"class="form-control bg-white rounded-3 p-3 border shadow-sm" type="text" placeholder="Default input" aria-label="default input example">
            
            

            
        </div>
        <hr class=" border-2 opacity-10 mt-4    ">
        
        <div class="row fw-bold mt-4 mb-4 ">
            
            <button id="btn-whatsapp" type="button" class="btn btn-success">
                <i class="bi bi-whatsapp me-2"></i>PESAN VIA WHATSAPP
            </button>

            
            
            

            
        </div>









        
    </div>

</div>
</div>

  
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. FUNGSI MENCETAK KERANJANG
    function renderKeranjang() {
        let keranjang = JSON.parse(localStorage.getItem('keranjang_sikat')) || [];
        let wadahKiri = document.getElementById('keranjang-container');
        let wadahKanan = document.getElementById('ringkasan-item');
        let wadahTotal = document.getElementById('total-harga');

        if (keranjang.length === 0) {
            wadahKiri.innerHTML = `<div class="bg-white rounded-3 p-5 border shadow-sm text-center"><h5>Keranjangmu masih kosong nih.</h5><a href="index.php" class="btn btn-success mt-3">Pilih Menu Dulu</a></div>`;
            wadahKanan.innerHTML = '';
            wadahTotal.innerText = `Rp 0`;
            return;
        }

        let htmlRakitanKiri = '';
        let htmlRakitanKanan = '';
        let totalHarga = 0;

        keranjang.forEach((item, index) => {
            let subtotal = item.harga * item.kuantitas;
            totalHarga += subtotal;

            htmlRakitanKiri += `
            <div class="bg-white rounded-3 p-3 border shadow-sm mb-3">
                <div class="row justify-content-center align-items-center">
                    <div class="col-md-2">
                        <img src="https://picsum.photos/100/100?random=${item.id}" class="img-fluid rounded">
                    </div>
                    <div class="col-md-3">
                        <h4 style="font-size: 16px;" class="fw-medium">${item.nama}</h4>
                        <p style="font-size: 10px;" class="fw-light">${item.deskripsi || 'Menu spesial'}</p>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex justify-content-center">
                            <div class="input-group input-group-sm shadow-sm">
                                <button class="btn btn-outline-secondary border-secondary-subtle btn-minus" data-index="${index}" type="button">-</button>
                                <input type="text" class="form-control text-center border-secondary-subtle" value="${item.kuantitas}" readonly>
                                <button class="btn btn-outline-secondary border-secondary-subtle btn-plus" data-index="${index}" type="button">+</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <p style="font-size: 16px;" class="fw-semibold">Rp ${subtotal.toLocaleString('id-ID')}</p>
                        <a href="#" class="text-danger text-decoration-none btn-hapus" data-index="${index}" style="font-size: 14px;">
                            <i class="bi bi-trash-fill"></i> Hapus
                        </a>
                    </div>
                </div>
            </div>
            `;

            htmlRakitanKanan += `
            <div class="row mb-2">
                <div class="col-8 fw-light" style="font-size: 16px;"><p>${item.nama} (${item.kuantitas})</p></div>  
                <div class="col-4 fw-light text-end" style="font-size:16px;"><p>Rp ${subtotal.toLocaleString('id-ID')}</p></div>
            </div>
            `;
        });

        wadahKiri.innerHTML = htmlRakitanKiri;
        wadahKanan.innerHTML = htmlRakitanKanan;
        wadahTotal.innerText = `Rp ${totalHarga.toLocaleString('id-ID')}`;
    }

    renderKeranjang();

    // 2. FUNGSI TOMBOL PLUS, MINUS, DAN HAPUS
    let containerKiri = document.getElementById('keranjang-container');
    if (containerKiri) {
        containerKiri.addEventListener('click', function(e) {
            let keranjang = JSON.parse(localStorage.getItem('keranjang_sikat')) || [];

            if (e.target.classList.contains('btn-plus')) {
                let index = e.target.getAttribute('data-index');
                keranjang[index].kuantitas += 1;
                localStorage.setItem('keranjang_sikat', JSON.stringify(keranjang));
                renderKeranjang();
            }
            
            if (e.target.classList.contains('btn-minus')) {
                let index = e.target.getAttribute('data-index');
                if (keranjang[index].kuantitas > 1) {
                    keranjang[index].kuantitas -= 1;
                    localStorage.setItem('keranjang_sikat', JSON.stringify(keranjang));
                    renderKeranjang(); 
                }
            }

            let tombolHapus = e.target.closest('.btn-hapus');
            if (tombolHapus) {
                e.preventDefault();
                let index = tombolHapus.getAttribute('data-index');
                keranjang.splice(index, 1);
                localStorage.setItem('keranjang_sikat', JSON.stringify(keranjang));
                renderKeranjang(); 
            }
        });
    }

    // 3. FUNGSI TANGGAL MINIMAL H-3 (Untuk input type="date")
    let d = new Date();
    d.setDate(d.getDate() + 3);
    let minDate = d.getFullYear() + "-" + 
                  String(d.getMonth() + 1).padStart(2, '0') + "-" + 
                  String(d.getDate()).padStart(2, '0');
    
    let dateInput = document.getElementById('tanggal-pesan');
    if(dateInput) {
        dateInput.setAttribute('min', minDate);
    }

    // 4. FUNGSI TOMBOL WHATSAPP (Membaca Tanggal & Jam Terpisah)
    let btnWA = document.getElementById('btn-whatsapp');
    if (btnWA) {
        btnWA.addEventListener('click', function() {
            let nama = document.getElementById('nama-pemesan') ? document.getElementById('nama-pemesan').value : '';
            let tgl = document.getElementById('tanggal-pesan') ? document.getElementById('tanggal-pesan').value : '';
            let jam = document.getElementById('jam-pesan') ? document.getElementById('jam-pesan').value : '';
            let alamat = document.getElementById('alamat-pesan') ? document.getElementById('alamat-pesan').value : '';
            let catatan = document.getElementById('catatan-pesan') ? document.getElementById('catatan-pesan').value : '';
            
            let keranjang = JSON.parse(localStorage.getItem('keranjang_sikat')) || [];

            if (keranjang.length === 0) return alert("Keranjang kosong!");
            if (!nama || !tgl || !jam || !alamat) return alert("Lengkapi Nama, Tanggal, Jam Pengiriman, dan Alamat!");

            if (tgl < minDate) {
                return alert("Pemesanan minimal H-3! Silakan periksa kembali tanggal acara Anda.");
            }

            let waktuRapi = `${tgl} (Jam ${jam})`;

            let pesan = `Halo SIKAT Catering, saya ingin memesan:\n\n`;
            pesan += `*Data Pemesan:*\n- Nama: ${nama}\n- Waktu Pengiriman: ${waktuRapi}\n- Alamat: ${alamat}\n`;
            if (catatan) pesan += `- Catatan: ${catatan}\n`;
            
            pesan += `\n*Detail Pesanan:*\n`;
            let totalSemua = 0;
            keranjang.forEach(item => {
                let sub = item.harga * item.kuantitas;
                totalSemua += sub;
                pesan += `- ${item.kuantitas}x ${item.nama} (Rp ${sub.toLocaleString('id-ID')})\n`;
            });
            
            pesan += `\n*Total Bayar: Rp ${totalSemua.toLocaleString('id-ID')}*`;

            let nomorWA = "6281234567890"; 
            let urlWA = `https://wa.me/${nomorWA}?text=${encodeURIComponent(pesan)}`;
            window.open(urlWA, '_blank');
        });
    }
});
</script>









<?php include 'includes/footer.php'; ?>
