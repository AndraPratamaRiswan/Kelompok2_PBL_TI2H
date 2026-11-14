<?php
// READ daftar pesanan milik pembeli yang login + DELETE (batalkan) pesanan.
// Tampilan kartu mengikuti cek-pesanan.php, tapi datanya dari database.
session_start();
ob_start();

$halaman = 'cek-pesanan';
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['id_pembeli'])) {
    header('Location: login.php?status=wajib&next=pesanan-saya.php');
    exit;
}

require_once __DIR__ . '/includes/koneksi.php';
require_once __DIR__ . '/includes/queries/pesanan.php';
require_once __DIR__ . '/includes/pesanan_helper.php';

$idPembeli = (int) $_SESSION['id_pembeli'];

// ---- tombol "Batalkan" (POST lalu redirect) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'hapus') {
    $idPesanan = filter_input(INPUT_POST, 'id_pesanan', FILTER_VALIDATE_INT);
    $kode = 'tidak_bisa';

    if ($idPesanan && $idPesanan > 0) {
        try {
            $kode = pbHapusPesanan($pdo, $idPesanan, $idPembeli) ? 'dihapus' : 'tidak_bisa';
        } catch (Throwable $e) {
            error_log('Hapus pesanan gagal: ' . $e->getMessage());
            $kode = 'gagal';
        }
    }
    header('Location: pesanan-saya.php?status=' . $kode);
    exit;
}

$pesanMap = [
    'dibuat'     => ['success', 'Pesanan berhasil dibuat. Admin akan segera mengonfirmasi pesananmu.'],
    'diubah'     => ['success', 'Perubahan pesanan berhasil disimpan.'],
    'dihapus'    => ['success', 'Pesanan berhasil dibatalkan.'],
    'tidak_bisa' => ['danger', 'Pesanan tidak bisa diubah atau dibatalkan karena sudah dikonfirmasi admin atau tidak ditemukan.'],
    'gagal'      => ['danger', 'Terjadi kesalahan. Coba lagi nanti.'],
];
$statusUrl = $_GET['status'] ?? '';
$pesan = $pesanMap[$statusUrl] ?? null;

$error = '';
try {
    $daftarPesanan = pbDaftarPesanan($pdo, $idPembeli);
} catch (PDOException $e) {
    error_log('Baca pesanan gagal: ' . $e->getMessage());
    $daftarPesanan = [];
    $error = 'Pesananmu belum bisa ditampilkan. Coba lagi nanti.';
}

$langkahStatus = ['Dikonfirmasi', 'Diproses', 'Dikirim', 'Selesai'];

include 'includes/header.php';
?>

<style>
    @media print {
        .navbar, footer, .tidak-dicetak { display: none !important; }
        .kartu-pesanan:not(.sedang-dicetak) { display: none !important; }
        .kartu-pesanan .collapse { display: block !important; }
    }
</style>

<div class="container mt-5 mb-5">
    <div class="col-md-8 mx-auto">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 tidak-dicetak">
            <div>
                <h2 class="fw-bold mb-1 isi-red">Pesanan Saya</h2>
                <p class="text-muted mb-0">Nomor HP: <?= pbE($_SESSION['no_hp'] ?? '') ?></p>
            </div>
            <a href="index.php" class="btn bg-red text-white">+ Pesan Lagi</a>
        </div>

        <?php if ($pesan) : ?>
            <div class="alert alert-<?= pbE($pesan[0]) ?> tidak-dicetak"><?= pbE($pesan[1]) ?></div>
        <?php endif; ?>
        <?php if ($error !== '') : ?>
            <div class="alert alert-danger tidak-dicetak"><?= pbE($error) ?></div>
        <?php endif; ?>

        <?php if (empty($daftarPesanan) && $error === '') : ?>
            <div class="card p-5 text-center tidak-dicetak">
                <h5>Kamu belum punya pesanan.</h5>
                <div><a href="index.php" class="btn btn-success mt-3">Pilih Menu</a></div>
            </div>
        <?php endif; ?>

        <?php foreach ($daftarPesanan as $pesanan) : ?>
            <?php
            $id          = (int) $pesanan['id_pesanan'];
            $statusNow   = (string) $pesanan['status_pesanan'];
            $posisi      = array_search($statusNow, $langkahStatus, true); // false = belum dikonfirmasi
            $posisi      = $posisi === false ? -1 : (int) $posisi;
            $bisaDiubah  = pbBisaDiubah($pesanan);
            $ongkir      = (float) $pesanan['biaya_ongkir'];
            $dp          = (float) $pesanan['jumlah_dp'];
            $total       = (float) $pesanan['total_harga'];
            $idCollapse  = 'rincian-' . $id;
            ?>
            <div class="card p-4 mb-4 kartu-pesanan" id="pesanan-<?= $id ?>">
                <div class="row align-items-center">
                    <div class="col-6 text-start">
                        <h5 class="fw-bold font-24 mb-1"><?= pbE(pbKode($id)) ?></h5>
                        <p class="font-18 mb-0">Dikirim: <?= pbE(pbTanggalIndo($pesanan['tanggal_pengiriman'])) ?></p>
                    </div>
                    <div class="col-6 text-end">
                        <span class="badge bg-red rounded-pill px-3 py-2 mb-2 d-inline-block font-18 fw-normal">
                            <?= pbE($statusNow) ?>
                        </span>
                        <br><br>
                        <button type="button" class="btn bg-red text-white rounded-pill font-18 fw-normal tidak-dicetak"
                                data-bs-toggle="collapse" data-bs-target="#<?= $idCollapse ?>"
                                style="--bs-btn-padding-y: .120rem; --bs-btn-padding-x: 2.3rem; --bs-btn-font-size: .75rem;">
                            Rincian
                        </button>
                    </div>
                </div>

                <div id="<?= $idCollapse ?>" class="collapse mt-4 pt-4 pb-4 border-top">
                    <div class="position-relative mt-2 mb-5">
                        <div style="position: absolute; top: 35px; left: 12%; right: 12%; height: 3px; background-color: #000; z-index: 1;"></div>
                        <div class="row text-center position-relative" style="z-index: 2;">
                            <?php foreach ($langkahStatus as $i => $langkah) : ?>
                                <?php $tercapai = $i <= $posisi; ?>
                                <div class="col-3">
                                    <p class="mb-2 fw-bold font-18 <?= $tercapai ? 'text-success' : '' ?>"><?= pbE($langkah) ?></p>
                                    <div class="rounded-circle <?= $tercapai ? 'bg-success' : 'bg-dark' ?> mx-auto" style="width: 24px; height: 24px;"></div>
                                    <p class="mt-2 text-muted font-18">&nbsp;</p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <p class="mb-1 font-18"><strong>Atas nama:</strong> <?= pbE($pesanan['nama_pembeli']) ?></p>
                    <p class="mb-1 font-18"><strong>Alamat:</strong> <?= pbE($pesanan['alamat_pengiriman']) ?></p>
                    <p class="mb-3 font-18"><strong>Jam kirim:</strong> <?= pbE(substr((string) $pesanan['jam_pengiriman'], 0, 5)) ?> WIB</p>
                    <?php if (trim((string) ($pesanan['catatan'] ?? '')) !== '') : ?>
                        <p class="mb-3 font-18"><strong>Catatan:</strong> <?= pbE($pesanan['catatan']) ?></p>
                    <?php endif; ?>

                    <?php foreach ($pesanan['items'] as $item) : ?>
                        <div class="row">
                            <div class="col-8 text-start">
                                <p class="fw-light font-18 mb-0"><?= pbE($item['nama_menu']) ?> x<?= (int) $item['jumlah'] ?></p>
                            </div>
                            <div class="col-4 text-end">
                                <p class="fw-light font-18"><?= pbRupiah($item['subtotal']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if ($ongkir > 0) : ?>
                        <div class="row">
                            <div class="col-8 text-start"><p class="fw-light font-18 mb-0">Ongkir</p></div>
                            <div class="col-4 text-end"><p class="fw-light font-18"><?= pbRupiah($ongkir) ?></p></div>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-6 text-start fw-bold font-18">TOTAL</div>
                        <div class="col-6 text-end fw-bold font-18"><?= pbRupiah($total) ?></div>
                    </div>
                    <div class="row">
                        <div class="col-6 text-start font-18">Pembayaran</div>
                        <div class="col-6 text-end font-18">
                            <?= pbE($pesanan['status_pembayaran']) ?><?= $pesanan['status_pembayaran'] === 'DP' ? ' (' . pbRupiah($dp) . ')' : '' ?>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4 tidak-dicetak">
                        <button class="btn bg-red text-white font-18 fw-bold btn-cetak-nota" type="button">Cetak Nota</button>

                        <?php if ($bisaDiubah) : ?>
                            <div class="row g-2">
                                <div class="col-6">
                                    <a class="btn btn-outline-secondary text-dark w-100 font-18" href="pesanan-ubah.php?id=<?= $id ?>">Ubah Pesanan</a>
                                </div>
                                <div class="col-6">
                                    <form method="post" action="pesanan-saya.php"
                                          onsubmit="return confirm('Batalkan pesanan <?= pbE(pbKode($id)) ?>? Pesanan yang dibatalkan tidak bisa dikembalikan.');">
                                        <input type="hidden" name="aksi" value="hapus">
                                        <input type="hidden" name="id_pesanan" value="<?= $id ?>">
                                        <button class="btn btn-outline-danger w-100 font-18" type="submit">Batalkan Pesanan</button>
                                    </form>
                                </div>
                            </div>
                        <?php else : ?>
                            <small class="text-muted text-center">Pesanan yang sudah dikonfirmasi tidak bisa diubah atau dibatalkan dari sini. Hubungi admin bila perlu perubahan.</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // cetak nota: hanya kartu pesanan yang dipilih yang dicetak
    document.querySelectorAll('.btn-cetak-nota').forEach(function (tombol) {
        tombol.addEventListener('click', function () {
            document.querySelectorAll('.kartu-pesanan').forEach(function (kartu) {
                kartu.classList.remove('sedang-dicetak');
            });
            tombol.closest('.kartu-pesanan').classList.add('sedang-dicetak');
            window.print();
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
