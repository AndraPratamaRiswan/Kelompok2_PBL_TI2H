<?php
// CREATE pesanan (tersimpan ke database). Tampilan mengikuti tambah_pesanan.php.
require_once __DIR__ . '/includes/koneksi.php';
require_once __DIR__ . '/includes/pesanan_helper.php';
require_once __DIR__ . '/includes/queries/pesanan_tambah.php'; // tambahPesanan() yang sudah ada

date_default_timezone_set('Asia/Jakarta');
$hariIni = date('Y-m-d');

$pesanForm  = '';
$jumlahMin  = PSN_MIN_BOX_TAMBAH; // minimal 15 box per menu
$daftarMenu = [];
$data = [
    'nomor' => '', 'nama' => '', 'alamat' => '',
    'status_pesanan' => '', 'status_pembayaran' => '',
    'tanggal' => '', 'jam' => '', 'ongkir' => '0', 'jumlah_dp' => '',
    'catatan' => '', 'baris' => [],
];

try {
    $daftarMenu = psnMenuUntukForm($pdo);
} catch (PDOException $e) {
    $pesanForm = 'Daftar menu gagal dibaca. Detail: ' . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pesanForm === '') {
    $hasil = psnValidasiForm($_POST, $daftarMenu, $hariIni, true, PSN_MIN_BOX_TAMBAH);
    $data = $hasil['data'];
    $pesanForm = $hasil['error'];

    if ($pesanForm === '') {
            // buat akun pembeli (kalau perlu) dan pesanan dalam satu transaksi
                try {
            $idAdmin = adminId(); // admin yang sedang login
            // pembeli (cari/buat dari no HP) dan pesanan disimpan atomik oleh prosedur database
            tambahPesananAdmin(
                $pdo,
                $idAdmin,
                $data['nomor'],
                $data['nama'],
                $hasil['id_menu'],
                $hasil['jumlah'],
                $data['alamat'],
                $data['status_pesanan'],
                $data['status_pembayaran'],
                $data['tanggal'],
                $data['jam'],
                $data['catatan'] !== '' ? $data['catatan'] : null,
                $hasil['dp_num'],
                $hasil['ongkir_num']
            );
            header('Location: pesanan_daftar.php?pesan=ditambah');
            exit;
        } catch (Throwable $e) {
            $pesanForm = 'Pesanan belum berhasil disimpan. Detail: ' . $e->getMessage();
        }
    }
}


$mode        = 'tambah';
$formAction  = 'pesanan_tambah.php';
$tanggalMin  = $hariIni;
$labelSimpan = 'Simpan Pesanan';
$urlBatal    = 'pesanan_daftar.php';

$pageTitle = 'Tambah Pesanan';
$activeNav = 'dashboard';
require __DIR__ . '/includes/header.php';
?>

<section class="page-heading">
    <div>
        <h1>Tambah Pesanan</h1>
        <p class="page-description">Isi data pesanan, lalu simpan ke database.</p>
    </div>
    <a class="button button-neutral" href="pesanan_daftar.php" onclick="return confirm('Yakin ingin keluar? Isian pesanan ini tidak akan disimpan.');">Kembali</a>
</section>

<?php require __DIR__ . '/includes/pesanan_form.php'; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>