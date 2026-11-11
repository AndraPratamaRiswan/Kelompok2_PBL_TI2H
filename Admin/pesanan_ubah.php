<?php
// UPDATE pesanan (tersimpan ke database). Tampilan mengikuti edit_pesanan.php.
require_once __DIR__ . '/includes/koneksi.php';
require_once __DIR__ . '/includes/queries/edit_pesanan.php'; // editPesanan() yang sudah ada
require_once __DIR__ . '/includes/queries/pesanan_crud.php';
require_once __DIR__ . '/includes/pesanan_helper.php';

date_default_timezone_set('Asia/Jakarta');

// halaman asal untuk menentukan tujuan tombol "kembali"
$asal = (($_GET['asal'] ?? 'daftar') === 'dashboard') ? 'dashboard' : 'daftar';
$urlKembali = ($asal === 'dashboard') ? 'index.php' : 'pesanan_daftar.php';

$idPesanan = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idPesanan || $idPesanan < 1) {
    header('Location: ' . $urlKembali);
    exit;
}

$pesanForm  = '';
$pesanan    = false;
$items      = [];
$daftarMenu = [];

try {
    $pesanan = psnAmbil($pdo, $idPesanan);
    if (!$pesanan) {
        header('Location: ' . $urlKembali . '?pesan=tidak_ditemukan');
        exit;
    }
    $items = psnItems($pdo, $idPesanan);
    $idTerpakai = array_map(function ($item) { return (int) $item['id_menu']; }, $items);
    $daftarMenu = psnMenuUntukForm($pdo, $idTerpakai);
} catch (PDOException $e) {
    $pesanForm = 'Data pesanan gagal dibaca. Detail: ' . $e->getMessage();
}

// Batas tanggal kirim = tanggal pesanan dibuat (aturan chk_pesanan_tanggal di database)
$tanggalMin = $pesanan ? (string) $pesanan['tanggal_dipesan'] : date('Y-m-d');

// nilai awal form diambil dari database
$data = [
    'nomor'             => $pesanan ? (string) $pesanan['no_hp'] : '',
    'nama'              => $pesanan ? (string) $pesanan['nama_pembeli'] : '',
    'alamat'            => $pesanan ? (string) $pesanan['alamat_pengiriman'] : '',
    'status_pesanan'    => $pesanan ? (string) $pesanan['status_pesanan'] : '',
    'status_pembayaran' => $pesanan ? (string) $pesanan['status_pembayaran'] : '',
    'tanggal'           => $pesanan ? (string) $pesanan['tanggal_pengiriman'] : '',
    'jam'               => $pesanan ? substr((string) $pesanan['jam_pengiriman'], 0, 5) : '',
    'ongkir'            => $pesanan ? (string) (int) $pesanan['biaya_ongkir'] : '0',
    'jumlah_dp'         => $pesanan && (float) $pesanan['jumlah_dp'] > 0 ? (string) (int) $pesanan['jumlah_dp'] : '',
    'catatan'           => $pesanan ? (string) ($pesanan['catatan'] ?? '') : '',
    'baris'             => [],
];
foreach ($items as $item) {
    $data['baris'][] = ['id' => (string) $item['id_menu'], 'jumlah' => (string) $item['jumlah']];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pesanan && $pesanForm === '') {
    $_POST['nomor'] = $data['nomor']; // nomor HP tidak bisa diubah dari form ini
    $hasil = psnValidasiForm($_POST, $daftarMenu, $tanggalMin, false);
    $data = $hasil['data'];
    $pesanForm = $hasil['error'];

    if ($pesanForm === '') {
        try {
            editPesanan(
                $pdo,
                $idPesanan,
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

            header('Location: ' . ($asal === 'dashboard' ? 'index.php' : 'pesanan_daftar.php?pesan=diubah'));
            exit;
        } catch (Throwable $e) {
            $pesanForm = 'Perubahan pesanan gagal disimpan. Detail: ' . $e->getMessage();
        }
    }
}

$mode        = 'ubah';
$formAction  = 'pesanan_ubah.php?id=' . (int) $idPesanan . '&asal=' . $asal;
$labelSimpan = 'Simpan Perubahan';
$urlBatal    = $urlKembali;

$pageTitle = 'Edit Pesanan';
$activeNav = 'dashboard';
require __DIR__ . '/includes/header.php';
?>

<section class="page-heading">
    <div>
        <h1>Edit Pesanan</h1>
        <p class="page-description"><?= $pesanan ? psnE(psnKode($idPesanan)) . ' · ubah data pesanan yang sudah ada.' : 'Pesanan tidak ditemukan.' ?></p>
    </div>
    <a class="button button-neutral" href="<?= psnE($urlKembali) ?>" onclick="return confirm('Yakin ingin keluar? Perubahan yang belum disimpan akan hilang.');">Kembali</a>
</section>

<?php if ($pesanan): ?>
    <?php require __DIR__ . '/includes/pesanan_form.php'; ?>
<?php else: ?>
    <div class="form-message form-message--error"><?= psnE($pesanForm) ?></div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
