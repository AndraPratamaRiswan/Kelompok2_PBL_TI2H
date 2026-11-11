<?php
// DELETE pesanan. Hanya menerima POST dari tombol "Hapus", lalu kembali ke daftar.
require_once __DIR__ . '/includes/koneksi.php';
require_once __DIR__ . '/includes/queries/pesanan_crud.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: pesanan_daftar.php');
    exit;
}

$asal = (($_POST['asal'] ?? 'daftar') === 'dashboard') ? 'dashboard' : 'daftar';
$tujuan = ($asal === 'dashboard') ? 'index.php' : 'pesanan_daftar.php';

$idPesanan = filter_input(INPUT_POST, 'id_pesanan', FILTER_VALIDATE_INT);
$kode = 'tidak_ditemukan';

if ($idPesanan && $idPesanan > 0) {
    try {
        $kode = psnHapus($pdo, $idPesanan) ? 'dihapus' : 'tidak_ditemukan';
    } catch (Throwable $e) {
        $kode = 'gagal_hapus';
    }
}

header('Location: ' . $tujuan . '?pesan=' . $kode);
exit;
