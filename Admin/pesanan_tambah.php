<?php
// CREATE pesanan (tersimpan ke database). Tampilan mengikuti tambah_pesanan.php.
require_once __DIR__ . '/includes/koneksi.php';
require_once __DIR__ . '/includes/queries/tambah_pesanan.php'; // tambahPesanan() yang sudah ada
require_once __DIR__ . '/includes/queries/pesanan_crud.php';
require_once __DIR__ . '/includes/pesanan_helper.php';

date_default_timezone_set('Asia/Jakarta');
$hariIni = date('Y-m-d');

$pesanForm  = '';
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
    $hasil = psnValidasiForm($_POST, $daftarMenu, $hariIni, true);
    $data = $hasil['data'];
    $pesanForm = $hasil['error'];

    if ($pesanForm === '') {
        try {
            $idAdmin = psnIdAdminPertama($pdo); // sama seperti tambah_menu.php: pakai admin pertama
            if ($idAdmin === null) {
                throw new RuntimeException('Tabel admin masih kosong. Masukkan data admin dulu.');
            }

            // buat akun pembeli (kalau perlu) dan pesanan dalam satu transaksi
            $pdo->beginTransaction();
            $idPembeli = psnCariAtauBuatPembeli($pdo, $data['nomor']);

            tambahPesanan(
                $pdo,
                $idAdmin,
                $data['nama'],
                $idPembeli,
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
            $pdo->commit();

            header('Location: pesanan_daftar.php?pesan=ditambah');
            exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
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
