// mboh kurang ngerti js
document.addEventListener('DOMContentLoaded', function () {
    const daftarBaris = document.getElementById('daftar-baris-menu');
    const tombolTambah = document.getElementById('tambah-baris-menu');
    const template = document.getElementById('template-baris-menu');
    const statusPembayaran = document.getElementById('status-pembayaran');
    const fieldDp = document.getElementById('field-jumlah-dp');

    // nampilin kolom DP pas status bayar nya DP
    if (statusPembayaran && fieldDp) {
        function tampilkanFieldDp() {
            fieldDp.hidden = statusPembayaran.value !== 'DP';
        }
        statusPembayaran.addEventListener('change', tampilkanFieldDp);
        tampilkanFieldDp();
    }

    if (!daftarBaris) return;

    // nambah baris menu pas pencet tombol "+ tambah menu", ngeduplikat template
    if (tombolTambah && template) {
        tombolTambah.addEventListener('click', function () {
            daftarBaris.appendChild(template.content.cloneNode(true));
        });
    }

    // tombol qty + -,  dan hapus baris menu
    daftarBaris.addEventListener('click', function (event) {
        const tombolJumlah = event.target.closest('[data-qty-change]');
        const tombolHapus = event.target.closest('.remove-order-item');

        if (tombolJumlah) {
            const inputJumlah = tombolJumlah.parentElement.querySelector('input[name="jumlah[]"]');
            const perubahan = Number(tombolJumlah.dataset.qtyChange);
            const jumlahSekarang = Number(inputJumlah.value) || 1;
            inputJumlah.value = Math.max(1, jumlahSekarang + perubahan);
        }

        if (tombolHapus) {
            const semuaBaris = daftarBaris.querySelectorAll('.order-item-row');
            if (semuaBaris.length > 1) {
                tombolHapus.closest('.order-item-row').remove();
            }
        }
    });
});