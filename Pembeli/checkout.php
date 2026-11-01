<?php
// CREATE pesanan pembeli (tersimpan ke database). Isi keranjang dibaca dari localStorage
// 'keranjang_sikat' (dibuat lewat menu-pesan.php), lalu dikirim sebagai menu_id[] dan jumlah[].
// Harga selalu dihitung ulang dari database oleh stored procedure sp_tambah_pesanan.
session_start();
ob_start();

$halaman = 'keranjang';
date_default_timezone_set('Asia/Jakarta');

// pesanan harus punya akun pembeli, jadi wajib login dulu
if (!isset($_SESSION['id_pembeli'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/includes/koneksi.php';
require_once __DIR__ . '/includes/queries/pesanan.php';
require_once __DIR__ . '/includes/pesanan_helper.php';

$idPembeli  = (int) $_SESSION['id_pembeli'];
$tanggalMin = pbTanggalMinimal();
$error      = '';
$data       = ['nama' => '', 'tanggal' => '', 'jam' => '', 'alamat' => '', 'catatan' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $menuMap = pbMenuByIds($pdo, pbIdMenuDariPost($_POST));
        $hasil   = pbValidasiPesanan($_POST, $menuMap, $tanggalMin);
        $data    = $hasil['data'];
        $error   = $hasil['error'];

        if ($error === '') {
            $idAdmin = pbIdAdminPertama($pdo);
            if ($idAdmin === null) {
                throw new RuntimeException('Belum ada admin yang terdaftar.');
            }

            pbTambahPesanan(
                $pdo,
                $idAdmin,
                $idPembeli,
                $data['nama'],
                $hasil['id_menu'],
                $hasil['jumlah'],
                $data['alamat'],
                $data['tanggal'],
                $data['jam'],
                $data['catatan'] !== '' ? $data['catatan'] : null
            );

            header('Location: pesanan-saya.php?status=dibuat');
            exit;
        }
    } catch (Throwable $e) {
        error_log('Checkout gagal: ' . $e->getMessage());
        $error = 'Pesanan belum berhasil disimpan. Silakan coba lagi.';
    }
}

include 'includes/header.php';
?>
<div class="container py-4">
    <div class="mb-4">
        <h2 class="fw-bold mb-1 isi-red">Checkout</h2>
        <p class="text-muted mb-0">Periksa pesananmu, lengkapi data pengiriman, lalu kirim.</p>
    </div>

    <?php if ($error !== '') : ?>
        <div class="alert alert-danger"><?= pbE($error) ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-7 mb-4">
            <div class="bg-red rounded-3 p-3 teks-jumbotron-judul">
                <div class="row fw-medium" style="font-size: 18px;">
                    <div class="col-6">Menu</div>
                    <div class="col-2 text-center">Qty</div>
                    <div class="col-4 text-end">Harga</div>
                </div>
            </div>

            <div id="ringkasan-keranjang" class="mt-3"></div>

            <div class="d-flex gap-2 mt-3">
                <a href="menu-pesan.php" class="btn btn-outline-secondary text-dark">+ Tambah menu</a>
                <a href="keranjang.php" class="btn btn-outline-secondary text-dark">Ubah isi keranjang</a>
            </div>
        </div>

        <div class="col-md-5">
            <form method="post" action="checkout.php" id="form-checkout" class="bg-white rounded-3 p-4 border shadow-sm">
                <h6 class="fw-medium mb-3" style="font-size: 18px;">Data Pengiriman</h6>

                <div class="d-flex justify-content-between fw-semibold mb-3">
                    <span>Total</span>
                    <span id="total-checkout">Rp 0</span>
                </div>
                <hr class="border-2 opacity-10">

                <div class="mb-3 fw-semibold">
                    <label class="mb-2" for="nama">Atas Nama</label>
                    <input id="nama" name="nama" type="text" maxlength="50" required
                           class="form-control rounded-3 p-3 border shadow-sm" value="<?= pbE($data['nama']) ?>">
                </div>

                <div class="row">
                    <div class="col-6 mb-3 fw-semibold">
                        <label class="mb-2" for="tanggal" style="font-size: 14px;">Tanggal Kirim</label>
                        <input id="tanggal" name="tanggal" type="date" min="<?= pbE($tanggalMin) ?>" required
                               class="form-control rounded-3 p-3 border shadow-sm" value="<?= pbE($data['tanggal']) ?>">
                    </div>
                    <div class="col-6 mb-3 fw-semibold">
                        <label class="mb-2" for="jam" style="font-size: 14px;">Jam</label>
                        <input id="jam" name="jam" type="time" required
                               class="form-control rounded-3 p-3 border shadow-sm" value="<?= pbE($data['jam']) ?>">
                    </div>
                </div>
                <small class="text-muted d-block mb-3">Pemesanan minimal H-3 (mulai <?= pbE(pbTanggalIndo($tanggalMin)) ?>).</small>

                <div class="mb-3 fw-semibold">
                    <label class="mb-2" for="alamat">Alamat Pengiriman</label>
                    <input id="alamat" name="alamat" type="text" maxlength="255" required
                           class="form-control rounded-3 p-3 border shadow-sm" value="<?= pbE($data['alamat']) ?>">
                </div>

                <div class="mb-4 fw-semibold">
                    <label class="mb-2" for="catatan">Catatan tambahan (opsional)</label>
                    <input id="catatan" name="catatan" type="text" maxlength="255"
                           class="form-control rounded-3 p-3 border shadow-sm" value="<?= pbE($data['catatan']) ?>">
                </div>

                <div id="input-menu"></div>

                <button type="submit" class="btn bg-red text-white w-100 fw-bold">BUAT PESANAN</button>
                <small class="text-muted d-block mt-2">Harga final dihitung dari daftar harga terbaru. Ongkir dan pembayaran dikonfirmasi admin.</small>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var KUNCI = 'keranjang_sikat';

    function bacaKeranjang() {
        try { return JSON.parse(localStorage.getItem(KUNCI)) || []; } catch (e) { return []; }
    }
    function rupiah(angka) { return 'Rp ' + Number(angka).toLocaleString('id-ID'); }
    function aman(teks) {
        var el = document.createElement('div');
        el.textContent = teks == null ? '' : teks;
        return el.innerHTML;
    }

    var keranjang = bacaKeranjang();
    var wadah = document.getElementById('ringkasan-keranjang');
    var total = 0;

    if (keranjang.length === 0) {
        wadah.innerHTML = '<div class="bg-white rounded-3 p-5 border shadow-sm text-center">'
            + '<h5>Keranjangmu masih kosong nih.</h5>'
            + '<a href="menu-pesan.php" class="btn btn-success mt-3">Pilih Menu Dulu</a></div>';
    } else {
        var html = '';
        keranjang.forEach(function (item) {
            var sub = Number(item.harga) * Number(item.kuantitas);
            total += sub;
            html += '<div class="bg-white rounded-3 p-3 border shadow-sm mb-2"><div class="row align-items-center">'
                + '<div class="col-6 fw-medium">' + aman(item.nama) + '</div>'
                + '<div class="col-2 text-center">' + Number(item.kuantitas) + '</div>'
                + '<div class="col-4 text-end fw-semibold">' + rupiah(sub) + '</div>'
                + '</div></div>';
        });
        wadah.innerHTML = html;
    }
    document.getElementById('total-checkout').textContent = rupiah(total);

    // saat dikirim, isi keranjang diubah jadi input menu_id[] dan jumlah[]
    document.getElementById('form-checkout').addEventListener('submit', function (event) {
        var isi = bacaKeranjang();
        if (isi.length === 0) {
            event.preventDefault();
            alert('Keranjang kosong! Pilih menu dulu.');
            return;
        }
        var tempat = document.getElementById('input-menu');
        tempat.innerHTML = '';
        isi.forEach(function (item) {
            ['menu_id', 'jumlah'].forEach(function (nama) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = nama + '[]';
                input.value = nama === 'menu_id' ? Number(item.id) : Number(item.kuantitas);
                tempat.appendChild(input);
            });
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
