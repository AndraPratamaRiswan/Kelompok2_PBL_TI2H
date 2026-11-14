<?php
// Keranjang berbasis SESSION PHP (tidak memakai database dan tidak memakai localStorage).
// Halaman ini hanya rangkuman pesanan: menu dipilih dan ditambahkan dari index.php, di sini
// jumlahnya bisa diubah/dihapus, lalu tombol "Pesan via WhatsApp" mengarahkan pembeli ke
// WhatsApp dengan pesan pesanan yang sudah terisi. Wajib login.
session_start();
ob_start(); // koneksi.php mencetak komentar HTML, ini mencegah error "headers already sent" saat redirect

$halaman = 'keranjang';
date_default_timezone_set('Asia/Jakarta');

// belum login: tidak boleh membuka keranjang, arahkan ke halaman masuk
if (!isset($_SESSION['id_pembeli'])) {
    header('Location: login.php?status=wajib&next=keranjang.php');
    exit;
}

require_once __DIR__ . '/includes/koneksi.php';
require_once __DIR__ . '/includes/queries/pesanan.php';   // pbMenuByIds()
require_once __DIR__ . '/includes/pesanan_helper.php';     // pbE(), pbRupiah(), pbGambarMenu(), pbTanggalMinimal()

// Nomor WhatsApp admin, format internasional tanpa tanda + (ganti dengan nomor asli)
$nomorWA = '6281227029499';

if (!isset($_SESSION['keranjang']) || !is_array($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}
if (!isset($_SESSION['data_pesan']) || !is_array($_SESSION['data_pesan'])) {
    $_SESSION['data_pesan'] = ['nama' => '', 'tanggal' => '', 'jam' => '', 'alamat' => '', 'catatan' => ''];
}

$tanggalMin = pbTanggalMinimal();
$error = '';

function krField(string $kunci, int $maks): string
{
    $nilai = isset($_POST[$kunci]) && is_scalar($_POST[$kunci]) ? trim((string) $_POST[$kunci]) : '';
    return mb_substr($nilai, 0, $maks);
}

// Samakan nama dan harga di keranjang dengan data menu terbaru. Mengembalikan [keranjang, pesanError].
function krSinkronMenu(PDO $pdo, array $keranjang): array
{
    $menuMap = pbMenuByIds($pdo, array_keys($keranjang));
    foreach ($keranjang as $id => $item) {
        if (!isset($menuMap[$id]) || !filter_var($menuMap[$id]['is_tersedia'], FILTER_VALIDATE_BOOLEAN)) {
            return [$keranjang, '"' . $item['nama'] . '" sudah tidak tersedia. Hapus dari keranjang lalu coba lagi.'];
        }
        $keranjang[$id]['nama']  = $menuMap[$id]['nama_menu'];
        $keranjang[$id]['harga'] = (float) $menuMap[$id]['harga_menu'];
    }
    return [$keranjang, ''];
}

function krPesanWhatsapp(array $data, array $keranjang): string
{
    $pesan  = "Halo SIKAT Catering, saya ingin memesan:\n\n";
    $pesan .= "*Data Pemesan:*\n- Nama: {$data['nama']}\n- Waktu Pengiriman: {$data['tanggal']} (Jam {$data['jam']})\n- Alamat: {$data['alamat']}\n";
    if ($data['catatan'] !== '') {
        $pesan .= "- Catatan: {$data['catatan']}\n";
    }

    $pesan .= "\n*Detail Pesanan:*\n";
    $total = 0.0;
    foreach ($keranjang as $item) {
        $subtotal = $item['harga'] * $item['kuantitas'];
        $total += $subtotal;
        $pesan .= "- {$item['kuantitas']}x {$item['nama']} (" . pbRupiah($subtotal) . ")\n";
    }
    $pesan .= "\n*Total Bayar: " . pbRupiah($total) . "*";
    return $pesan;
}

// ---------------- proses tombol (semua tombol mengirim POST) ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // isian form disimpan di session supaya tidak hilang tiap kali tombol keranjang ditekan
    $_SESSION['data_pesan'] = [
        'nama'    => krField('nama', 100),
        'tanggal' => krField('tanggal', 10),
        'jam'     => krField('jam', 8),
        'alamat'  => krField('alamat', 255),
        'catatan' => krField('catatan', 255),
    ];
    $aksi   = isset($_POST['aksi']) && is_string($_POST['aksi']) ? $_POST['aksi'] : 'simpan';
    $anchor = '#keranjang';

    try {
        if (preg_match('/^(plus|minus|hapus)_(\d+)$/', $aksi, $cocok)) {
            $jenis = $cocok[1];
            $id    = (int) $cocok[2];

            if (isset($_SESSION['keranjang'][$id])) {
                if ($jenis === 'plus') {
                    $_SESSION['keranjang'][$id]['kuantitas'] = min(10000, $_SESSION['keranjang'][$id]['kuantitas'] + 1);
                } elseif ($jenis === 'minus') {
                    $_SESSION['keranjang'][$id]['kuantitas'] = max(PB_MIN_BOX, $_SESSION['keranjang'][$id]['kuantitas'] - 1);
                } else { // hapus
                    unset($_SESSION['keranjang'][$id]);
                }
            }
            header('Location: keranjang.php' . $anchor);
            exit;
        }

        if ($aksi === 'kosongkan') {
            $_SESSION['keranjang'] = [];
            header('Location: keranjang.php');
            exit;
        }

        if ($aksi === 'whatsapp') {
            $data = $_SESSION['data_pesan'];

            if (empty($_SESSION['keranjang'])) {
                $error = 'Keranjang kosong! Pilih menu dulu.';
            } elseif ($data['nama'] === '' || $data['tanggal'] === '' || $data['jam'] === '' || $data['alamat'] === '') {
                $error = 'Lengkapi Nama, Tanggal, Jam Pengiriman, dan Alamat!';
            } else {
                $tanggal = DateTime::createFromFormat('Y-m-d', $data['tanggal']);
                if (!$tanggal || $tanggal->format('Y-m-d') !== $data['tanggal']) {
                    $error = 'Tanggal pengiriman tidak valid.';
                } elseif ($data['tanggal'] < $tanggalMin) {
                    $error = 'Pemesanan minimal H-3! Silakan periksa kembali tanggal acara Anda.';
                } elseif (!preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $data['jam'])) {
                    $error = 'Jam pengiriman tidak valid.';
                }
            }

            if ($error === '') {
                foreach ($_SESSION['keranjang'] as $itemCek) {
                    if ($itemCek['kuantitas'] < PB_MIN_BOX) {
                        $error = 'Pemesanan minimal ' . PB_MIN_BOX . ' box per menu. Tambah jumlah "' . $itemCek['nama'] . '" dulu.';
                        break;
                    }
                }
            }

            if ($error === '') {
                [$keranjangBaru, $error] = krSinkronMenu($pdo, $_SESSION['keranjang']);
                if ($error === '') {
                    $_SESSION['keranjang'] = $keranjangBaru;
                    $url = 'https://wa.me/' . $nomorWA . '?text=' . rawurlencode(krPesanWhatsapp($data, $keranjangBaru));
                    header('Location: ' . $url); // arahkan ke WhatsApp
                    exit;
                }
            }
        } else {
            // aksi "simpan" (Enter di kolom isian): isian sudah tersimpan, kembali ke halaman
            header('Location: keranjang.php');
            exit;
        }
    } catch (Throwable $e) {
        error_log('Keranjang gagal: ' . $e->getMessage());
        $error = 'Terjadi kesalahan. Silakan coba lagi.';
    }
}

$keranjang = $_SESSION['keranjang'];
$data      = $_SESSION['data_pesan'];
$totalHarga = 0.0;
$totalItem  = 0;
foreach ($keranjang as $item) {
    $totalHarga += $item['harga'] * $item['kuantitas'];
    $totalItem  += $item['kuantitas'];
}

include 'includes/header.php';
?>
<div class="container py-4">

<form id="form-keranjang" method="post" action="keranjang.php" autocomplete="off">
    <!-- tombol bawaan saat Enter ditekan di kolom isian, supaya tidak memicu tombol +/- di keranjang -->
    <button type="submit" name="aksi" value="simpan" class="d-none" tabindex="-1" aria-hidden="true" formnovalidate></button>

<div class="row" id="keranjang">

    <div class="col-md-8">

    <div class="bg-red rounded-3 p-3 teks-jumbotron-judul">
        <div class="row align-content-between fw-medium" style="font-size: 18px;">
            <div class="col-4 text-start ">Menu</div>
            <div class="col-4 text-center">Kuantitas</div>
            <div class="col-4 text-end ">Harga</div>
        </div>
    </div>

    <div id="keranjang-container" class="mt-3">
        <?php if (empty($keranjang)) : ?>
            <div class="bg-white rounded-3 p-5 border shadow-sm text-center">
                <h5>Keranjangmu masih kosong nih.</h5>
                <a href="index.php" class="btn btn-success mt-3">Pilih Menu Dulu</a>
            </div>
        <?php endif; ?>

        <?php foreach ($keranjang as $id => $item) : ?>
            <?php $subtotal = $item['harga'] * $item['kuantitas']; ?>
            <div class="bg-white rounded-3 p-3 border shadow-sm mb-3">
                <div class="row justify-content-center align-items-center">
                    <div class="col-md-2">
                        <img src="<?= pbE(pbGambarMenu($item['gambar'], $item['nama'])) ?>" class="img-fluid rounded" alt="<?= pbE($item['nama']) ?>">
                    </div>
                    <div class="col-md-3">
                        <h4 style="font-size: 16px;" class="fw-medium"><?= pbE($item['nama']) ?></h4>
                        <p style="font-size: 10px;" class="fw-light"><?= pbE($item['deskripsi'] !== '' ? $item['deskripsi'] : 'Menu spesial') ?></p>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex justify-content-center">
                            <div class="input-group input-group-sm shadow-sm">
                                <button class="btn btn-outline-secondary border-secondary-subtle" type="submit" name="aksi" value="minus_<?= (int) $id ?>" formnovalidate>-</button>
                                <input type="text" class="form-control text-center border-secondary-subtle" value="<?= (int) $item['kuantitas'] ?>" readonly>
                                <button class="btn btn-outline-secondary border-secondary-subtle" type="submit" name="aksi" value="plus_<?= (int) $id ?>" formnovalidate>+</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <p style="font-size: 16px;" class="fw-semibold"><?= pbRupiah($subtotal) ?></p>
                        <button type="submit" name="aksi" value="hapus_<?= (int) $id ?>" formnovalidate
                                class="btn btn-link text-danger text-decoration-none p-0" style="font-size: 14px;">
                            <i class="bi bi-trash-fill"></i> Hapus
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (!empty($keranjang)) : ?>
            <button type="submit" name="aksi" value="kosongkan" formnovalidate
                    class="btn btn-outline-danger btn-sm"
                    onclick="return confirm('Kosongkan seluruh isi keranjang?');">Kosongkan Keranjang</button>
        <?php endif; ?>
    </div>

    </div>

<div class="col-md-4">

    <div class="bg-white rounded-3 p-4 border shadow-sm">

        <h6 class="fw-medium mb-2" style="font-size: 18px;">Rangkuman Pesanan</h6>
        <small class="text-muted d-block mb-3">Minimal <?= PB_MIN_BOX ?> box untuk setiap menu.</small>

        <?php if ($error !== '') : ?>
            <div class="alert alert-danger py-2 small"><?= pbE($error) ?></div>
        <?php endif; ?>

        <div id="ringkasan-item">
            <?php foreach ($keranjang as $item) : ?>
                <div class="row mb-2">
                    <div class="col-8 fw-light" style="font-size: 16px;"><p><?= pbE($item['nama']) ?> (<?= (int) $item['kuantitas'] ?>)</p></div>
                    <div class="col-4 fw-light text-end" style="font-size:16px;"><p><?= pbRupiah($item['harga'] * $item['kuantitas']) ?></p></div>
                </div>
            <?php endforeach; ?>
        </div>

        <hr class=" border-2 opacity-10">
        <div class="row mt-4 mb-4">
            <div class="col-8 fw-semibold" style="font-size: 16px;">
                <p>Total</p>
            </div>
            <div class="col-4 text-end fw-semibold" style="font-size: 16px;">
                <p id="total-harga"><?= pbRupiah($totalHarga) ?></p>
            </div>
        </div>

        <hr class=" border-2 opacity-10 mt-1">
        <div class="mb-3 fw-semibold">
            <p class="mb-2" style="font-size: 16px;">Atas Nama</p>
            <input id="nama-pemesan" name="nama" maxlength="100" required value="<?= pbE($data['nama']) ?>"
                   class="form-control bg-white rounded-3 p-3 border shadow-sm" type="text" placeholder="Nama pemesan">
        </div>

        <div class="row">
            <div class="col-6 mb-3 fw-semibold">
                <p class="mb-2" style="font-size: 14px;">Tanggal Kirim</p>
                <input id="tanggal-pesan" name="tanggal" type="date" min="<?= pbE($tanggalMin) ?>" required
                       value="<?= pbE($data['tanggal']) ?>" class="form-control bg-white rounded-3 p-3 border shadow-sm">
            </div>
            <div class="col-6 mb-3 fw-semibold">
                <p class="mb-2" style="font-size: 14px;">Jam</p>
                <input id="jam-pesan" name="jam" type="time" required
                       value="<?= pbE($data['jam']) ?>" class="form-control bg-white rounded-3 p-3 border shadow-sm">
            </div>
        </div>
        <small class="text-muted d-block mb-3">Pemesanan minimal H-3 (mulai <?= pbE(pbTanggalIndo($tanggalMin)) ?>).</small>

        <div class="mb-3 fw-semibold">
            <p class="mb-2" style="font-size: 16px;">Masukkan Alamat</p>
            <input id="alamat-pesan" name="alamat" maxlength="255" required value="<?= pbE($data['alamat']) ?>"
                   class="form-control bg-white rounded-3 p-3 border shadow-sm" type="text" placeholder="Alamat pengiriman">
        </div>

        <div class="mb-4 fw-semibold ">
            <p class="mb-2" style="font-size: 16px;">Catatan tambahan opsional</p>
            <input id="catatan-pesan" name="catatan" maxlength="255" value="<?= pbE($data['catatan']) ?>"
                   class="form-control bg-white rounded-3 p-3 border shadow-sm" type="text" placeholder="Contoh: sambal dipisah">
        </div>
        <hr class=" border-2 opacity-10 mt-4    ">

        <div class="row fw-bold mt-4 mb-4 ">
            <button id="btn-whatsapp" type="submit" name="aksi" value="whatsapp" class="btn btn-success">
                <i class="bi bi-whatsapp me-2"></i>PESAN VIA WHATSAPP
            </button>
        </div>

    </div>

</div>
</div>
</form>

<div class="mt-3">
    <a href="index.php" class="btn btn-outline-secondary text-dark">+ Tambah menu lain</a>
</div>

</div>

<?php include 'includes/footer.php'; ?>
