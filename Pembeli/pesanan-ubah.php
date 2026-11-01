<?php
// UPDATE pesanan milik pembeli. Hanya boleh selama status masih "Belum Dikonfirmasi".
// Status, DP, dan ongkir tidak bisa diubah pembeli (itu urusan admin).
session_start();
ob_start();

$halaman = 'cek-pesanan';
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['id_pembeli'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/includes/koneksi.php';
require_once __DIR__ . '/includes/queries/pesanan.php';
require_once __DIR__ . '/includes/pesanan_helper.php';

$idPembeli = (int) $_SESSION['id_pembeli'];
$idPesanan = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idPesanan || $idPesanan < 1) {
    header('Location: pesanan-saya.php');
    exit;
}

// satu baris menu: dropdown + jumlah + tombol hapus baris
function pbBarisMenuForm(array $opsiMenu, $idDipilih = '', $jumlah = 1): string
{
    $opsi = '<option value="">Pilih menu</option>';
    foreach ($opsiMenu as $menu) {
        $dipilih = ((string) $idDipilih === (string) $menu['id']) ? ' selected' : '';
        $opsi .= '<option value="' . (int) $menu['id'] . '"' . $dipilih . '>'
            . pbE($menu['nama'] . ' — ' . pbRupiah($menu['harga'])) . '</option>';
    }
    $jumlah = is_numeric($jumlah) ? max(1, (int) $jumlah) : 1;

    return '<div class="row g-2 align-items-center baris-menu mb-2">'
        . '<div class="col-7"><select name="menu_id[]" class="form-select" required>' . $opsi . '</select></div>'
        . '<div class="col-3"><input type="number" name="jumlah[]" class="form-control" min="1" value="' . $jumlah . '" required></div>'
        . '<div class="col-2"><button type="button" class="btn btn-outline-danger w-100 btn-hapus-baris" aria-label="Hapus baris"><i class="bi bi-trash-fill"></i></button></div>'
        . '</div>';
}

$error        = '';
$pesanan      = false;
$items        = [];
$opsiMenu     = [];
$tidakBisaUbah = false;

try {
    $pesanan = pbPesananById($pdo, $idPesanan, $idPembeli);
    if (!$pesanan) {
        header('Location: pesanan-saya.php?status=tidak_bisa');
        exit;
    }
    $tidakBisaUbah = !pbBisaDiubah($pesanan);

    $items = pbItemPesanan($pdo, [$idPesanan])[$idPesanan] ?? [];
    foreach (pbMenuTersedia($pdo) as $menu) {
        $opsiMenu[(int) $menu['id_menu']] = [
            'id' => (int) $menu['id_menu'], 'nama' => $menu['nama_menu'], 'harga' => $menu['harga_menu'],
        ];
    }
    // menu yang sudah ada di pesanan tetap bisa dipilih walau sekarang nonaktif
    foreach ($items as $item) {
        $idItem = (int) $item['id_menu'];
        if (!isset($opsiMenu[$idItem])) {
            $opsiMenu[$idItem] = [
                'id' => $idItem, 'nama' => $item['nama_menu'] . ' (nonaktif)', 'harga' => $item['harga_satuan'],
            ];
        }
    }
} catch (PDOException $e) {
    error_log('Baca pesanan gagal: ' . $e->getMessage());
    $error = 'Data pesanan belum bisa dibaca. Coba lagi nanti.';
}

$tanggalMin  = pbTanggalMinimal();
$tanggalLama = $pesanan ? (string) $pesanan['tanggal_pengiriman'] : null;
$idLama      = array_map(function ($item) { return (int) $item['id_menu']; }, $items);

// nilai awal form diambil dari database
$data = [
    'nama'    => $pesanan ? (string) $pesanan['nama_pembeli'] : '',
    'tanggal' => $tanggalLama ?? '',
    'jam'     => $pesanan ? substr((string) $pesanan['jam_pengiriman'], 0, 5) : '',
    'alamat'  => $pesanan ? (string) $pesanan['alamat_pengiriman'] : '',
    'catatan' => $pesanan ? (string) ($pesanan['catatan'] ?? '') : '',
    'baris'   => [],
];
foreach ($items as $item) {
    $data['baris'][] = ['id' => (string) $item['id_menu'], 'jumlah' => (string) $item['jumlah']];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pesanan && !$tidakBisaUbah && $error === '') {
    try {
        $menuMap = pbMenuByIds($pdo, pbIdMenuDariPost($_POST));
        $hasil   = pbValidasiPesanan($_POST, $menuMap, $tanggalMin, $tanggalLama, $idLama);
        $data    = $hasil['data'];
        $error   = $hasil['error'];

        if ($error === '') {
            // cek ulang status di dalam transaksi, jaga-jaga admin baru saja mengonfirmasi
            $pdo->beginTransaction();
            $terkunci = pbPesananById($pdo, $idPesanan, $idPembeli, true);

            if (!$terkunci || !pbBisaDiubah($terkunci)) {
                $pdo->rollBack();
                header('Location: pesanan-saya.php?status=tidak_bisa');
                exit;
            }

            pbUbahPesanan(
                $pdo,
                $idPesanan,
                $terkunci,
                $data['nama'],
                $hasil['id_menu'],
                $hasil['jumlah'],
                $data['alamat'],
                $data['tanggal'],
                $data['jam'],
                $data['catatan'] !== '' ? $data['catatan'] : null
            );
            $pdo->commit();

            header('Location: pesanan-saya.php?status=diubah');
            exit;
        }
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Ubah pesanan gagal: ' . $e->getMessage());
        $error = 'Perubahan pesanan belum berhasil disimpan. Silakan coba lagi.';
    }
}

$barisMenu = !empty($data['baris']) ? $data['baris'] : [['id' => '', 'jumlah' => 1]];
// tanggal lama boleh dipertahankan walau sudah lewat batas H-3
$minTanggalInput = ($tanggalLama !== null && $tanggalLama < $tanggalMin) ? $tanggalLama : $tanggalMin;

include 'includes/header.php';
?>
<div class="container py-4">
    <div class="col-lg-8 mx-auto">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h2 class="fw-bold mb-1 isi-red">Ubah Pesanan</h2>
                <p class="text-muted mb-0"><?= $pesanan ? pbE(pbKode($idPesanan)) : '' ?></p>
            </div>
            <a href="pesanan-saya.php" class="btn btn-outline-secondary text-dark"
               onclick="return confirm('Keluar dari halaman ini? Perubahan yang belum disimpan akan hilang.');">Kembali</a>
        </div>

        <?php if ($error !== '') : ?>
            <div class="alert alert-danger"><?= pbE($error) ?></div>
        <?php endif; ?>

        <?php if ($tidakBisaUbah) : ?>
            <div class="alert alert-warning">
                Pesanan ini sudah dikonfirmasi admin, jadi tidak bisa diubah dari sini. Hubungi admin bila perlu perubahan.
            </div>
        <?php elseif ($pesanan) : ?>
            <form method="post" action="pesanan-ubah.php?id=<?= (int) $idPesanan ?>" class="bg-white rounded-3 p-4 border shadow-sm">
                <div class="mb-3 fw-semibold">
                    <label class="mb-2" for="nama">Atas Nama</label>
                    <input id="nama" name="nama" type="text" maxlength="50" required
                           class="form-control rounded-3 p-3 border shadow-sm" value="<?= pbE($data['nama']) ?>">
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold">Menu yang dipesan</span>
                        <button type="button" id="tambah-baris" class="btn btn-sm btn-outline-success">+ Tambah Menu</button>
                    </div>
                    <div class="row g-2 text-muted small mb-1">
                        <div class="col-7">Menu</div><div class="col-3">Jumlah (box)</div><div class="col-2"></div>
                    </div>
                    <div id="daftar-baris">
                        <?php foreach ($barisMenu as $baris) : ?>
                            <?= pbBarisMenuForm($opsiMenu, $baris['id'], $baris['jumlah']) ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6 mb-3 fw-semibold">
                        <label class="mb-2" for="tanggal" style="font-size: 14px;">Tanggal Kirim</label>
                        <input id="tanggal" name="tanggal" type="date" min="<?= pbE($minTanggalInput) ?>" required
                               class="form-control rounded-3 p-3 border shadow-sm" value="<?= pbE($data['tanggal']) ?>">
                    </div>
                    <div class="col-6 mb-3 fw-semibold">
                        <label class="mb-2" for="jam" style="font-size: 14px;">Jam</label>
                        <input id="jam" name="jam" type="time" required
                               class="form-control rounded-3 p-3 border shadow-sm" value="<?= pbE($data['jam']) ?>">
                    </div>
                </div>
                <small class="text-muted d-block mb-3">Tanggal baru minimal H-3 (mulai <?= pbE(pbTanggalIndo($tanggalMin)) ?>).</small>

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

                <div class="d-flex gap-2">
                    <button type="submit" class="btn bg-red text-white fw-bold px-4">Simpan Perubahan</button>
                    <a href="pesanan-saya.php" class="btn btn-outline-secondary text-dark"
                       onclick="return confirm('Batalkan perubahan? Data yang kamu ubah tidak akan disimpan.');">Batal</a>
                </div>
                <small class="text-muted d-block mt-2">Harga dihitung ulang dari daftar harga terbaru saat disimpan.</small>
            </form>

            <template id="template-baris">
                <?= pbBarisMenuForm($opsiMenu) ?>
            </template>

            <script>
            document.addEventListener('DOMContentLoaded', function () {
                var daftar = document.getElementById('daftar-baris');
                var templateBaris = document.getElementById('template-baris');

                document.getElementById('tambah-baris').addEventListener('click', function () {
                    daftar.appendChild(templateBaris.content.cloneNode(true));
                });

                daftar.addEventListener('click', function (event) {
                    var tombol = event.target.closest('.btn-hapus-baris');
                    if (!tombol) return;
                    // minimal tersisa satu baris menu
                    if (daftar.querySelectorAll('.baris-menu').length > 1) {
                        tombol.closest('.baris-menu').remove();
                    }
                });
            });
            </script>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
