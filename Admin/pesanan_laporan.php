<?php
// Cetak laporan transaksi: data asli dari database (pesanan berstatus "Selesai"),
// bisa dipilih per bulan, dan bisa diunduh sebagai PDF.
require_once __DIR__ . '/includes/koneksi.php';
require_once __DIR__ . '/includes/queries/laporan.php';
require_once __DIR__ . '/includes/pesanan_helper.php';

date_default_timezone_set('Asia/Jakarta');

function laporanNamaBulan(string $periode): string
{
    $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
              'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    [$tahun, $bln] = explode('-', $periode);
    return $bulan[(int) $bln] . ' ' . $tahun;
}

// ---- periode yang dipilih (format YYYY-MM) ----
$error = '';
$daftarPeriode = [];
try {
    $daftarPeriode = lapPeriodeTersedia($pdo);
} catch (PDOException $e) {
    $error = 'Data laporan gagal dibaca. Detail: ' . $e->getMessage();
}

// bulan ini selalu ada di pilihan, walaupun belum ada pesanan selesai
$bulanIni = date('Y-m');
if (!in_array($bulanIni, $daftarPeriode, true)) {
    $daftarPeriode[] = $bulanIni;
    rsort($daftarPeriode);
}

$periode = (string) ($_GET['periode'] ?? '');
if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periode)) {
    // belum memilih: pakai bulan terbaru yang punya data, kalau tidak ada pakai bulan ini
    $periode = $daftarPeriode[0] ?? $bulanIni;
    foreach ($daftarPeriode as $kandidat) {
        if ($kandidat <= $bulanIni) {
            $periode = $kandidat;
            break;
        }
    }
}
$periodeLabel = laporanNamaBulan($periode);

// ---- data transaksi ----
$transaksi = [];
if ($error === '') {
    try {
        foreach (lapTransaksi($pdo, $periode) as $r) {
            $transaksi[] = [
                'id_pesanan' => psnKode((int) $r['id_pesanan']),
                'tanggal'    => $r['tanggal_dipesan'],
                'menu'       => $r['nama_menu'],
                'qty'        => (int) $r['jumlah'],
                'harga'      => (float) $r['harga_satuan'],
                'total'      => (float) $r['subtotal'],
            ];
        }
    } catch (PDOException $e) {
        $error = 'Data laporan gagal dibaca. Detail: ' . $e->getMessage();
    }
}

// hitung total box, pendapatan, ama jumlah jenis menu
$totalQty = 0;
$totalPendapatan = 0.0;
$daftarJenisMenu = [];

foreach ($transaksi as $row) {
    $totalQty += $row['qty'];
    $totalPendapatan += $row['total'];
    $daftarJenisMenu[$row['menu']] = true;
}

$jumlahJenisMenu = count($daftarJenisMenu);

function laporanRupiah($nilai): string
{
    return 'Rp' . number_format((float) $nilai, 0, ',', '.');
}

function laporanTanggal($tanggal): string
{
    return date('d/m/Y', strtotime($tanggal));
}

// fungsi untuk menyiapkan teks pdf
function laporanPdfEscape(string $text): string
{
    $encoded = iconv('UTF-8', 'Windows-1252//TRANSLIT', $text);
    if ($encoded !== false) {
        $text = $encoded;
    }

    return str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', '', ' '], $text);
}

function laporanPdfText(float $x, float $y, int $size, string $text, bool $bold = false, array $color = [0.16, 0.23, 0.31]): string
{
    $font = $bold ? '/F2' : '/F1';
    return sprintf(
        "%.3f %.3f %.3f rg BT %s %d Tf 1 0 0 1 %.2f %.2f Tm (%s) Tj ET\n",
        $color[0], $color[1], $color[2], $font, $size, $x, $y, laporanPdfEscape($text)
    );
}

function laporanPdfRect(float $x, float $y, float $w, float $h, array $color): string
{
    return sprintf("%.3f %.3f %.3f rg %.2f %.2f %.2f %.2f re f\n", $color[0], $color[1], $color[2], $x, $y, $w, $h);
}

// header tabel PDF: kolom ID Pesanan di paling kiri. $y = batas bawah kotak header.
function laporanPdfHeaderTabel(float $y): string
{
    $putih = [1, 1, 1];
    $s  = laporanPdfRect(40, $y, 762, 30, [0.25, 0.37, 0.49]);
    $s .= laporanPdfText(50, $y + 11, 9, 'ID Pesanan', false, $putih);
    $s .= laporanPdfText(125, $y + 11, 10, 'Tanggal', false, $putih);
    $s .= laporanPdfText(230, $y + 11, 10, 'Menu', false, $putih);
    $s .= laporanPdfText(500, $y + 11, 10, 'Jumlah', false, $putih);
    $s .= laporanPdfText(580, $y + 11, 9, 'Harga Satuan', false, $putih);
    $s .= laporanPdfText(720, $y + 11, 10, 'Total', false, $putih);
    return $s;
}

// PDF bisa lebih dari satu halaman: data asli jumlah barisnya tidak tetap.
function laporanBuatPdf(string $periodeLabel, array $baris, int $totalQty, int $jumlahJenisMenu, float $totalPendapatan): string
{
    $halaman = [];       // isi (stream) tiap halaman
    $batasBawah = 60;    // baris baru dibuat di halaman berikutnya kalau y di bawah ini

    $stream = '';
    $stream .= laporanPdfText(40, 548, 23, 'Cetak Laporan Transaksi', true, [0.08, 0.12, 0.18]);
    $stream .= laporanPdfText(40, 523, 11, 'SIKAT - Catering Nasi Kotak Malang by Tya');
    $stream .= laporanPdfText(40, 506, 10, 'Jl. Raya Malang No. 123 | WA: 0812-3768-xxxx');
    $stream .= laporanPdfText(40, 480, 11, 'Periode: ' . $periodeLabel, true);

    // card ringkasan
    $stream .= laporanPdfRect(40, 407, 240, 55, [0.94, 0.96, 0.98]);
    $stream .= laporanPdfRect(301, 407, 240, 55, [0.94, 0.96, 0.98]);
    $stream .= laporanPdfRect(562, 407, 240, 55, [0.12, 0.19, 0.27]);
    $stream .= laporanPdfText(53, 442, 10, 'Total Box');
    $stream .= laporanPdfText(53, 420, 16, number_format($totalQty, 0, ',', '.'), true);
    $stream .= laporanPdfText(314, 442, 10, 'Jenis Menu');
    $stream .= laporanPdfText(314, 420, 16, (string) $jumlahJenisMenu, true);
    $stream .= laporanPdfText(575, 442, 10, 'Total Pendapatan', false, [1, 1, 1]);
    $stream .= laporanPdfText(575, 420, 16, laporanRupiah($totalPendapatan), true, [1, 1, 1]);

    $stream .= laporanPdfHeaderTabel(365);

    $y = 335;
    foreach ($baris as $index => $row) {
        if ($y < $batasBawah) {
            $halaman[] = $stream;
            $stream = laporanPdfHeaderTabel(530);
            $y = 500;
        }
        $warnaBaris = ($index % 2 === 0) ? [1, 1, 1] : [0.94, 0.96, 0.98];
        $stream .= laporanPdfRect(40, $y - 8, 762, 28, $warnaBaris);
        $stream .= laporanPdfText(50, $y + 1, 9, $row['id_pesanan']);
        $stream .= laporanPdfText(125, $y + 1, 9, laporanTanggal($row['tanggal']));
        $stream .= laporanPdfText(230, $y + 1, 9, mb_strimwidth($row['menu'], 0, 48, '...'));
        $stream .= laporanPdfText(500, $y + 1, 9, number_format((int) $row['qty'], 0, ',', '.') . ' Box');
        $stream .= laporanPdfText(580, $y + 1, 9, laporanRupiah($row['harga']));
        $stream .= laporanPdfText(720, $y + 1, 9, laporanRupiah($row['total']));
        $y -= 31;
    }

    // baris total
    if ($y < $batasBawah) {
        $halaman[] = $stream;
        $stream = laporanPdfHeaderTabel(530);
        $y = 500;
    }
    $stream .= laporanPdfRect(40, $y - 8, 762, 32, [0.88, 0.92, 0.96]);
    $stream .= laporanPdfText(50, $y + 3, 10, 'TOTAL KESELURUHAN', true);
    $stream .= laporanPdfText(500, $y + 3, 10, number_format($totalQty, 0, ',', '.') . ' Box', true);
    $stream .= laporanPdfText(720, $y + 3, 10, laporanRupiah($totalPendapatan), true);
    $halaman[] = $stream;

    // nomor halaman
    $jumlahHalaman = count($halaman);
    foreach ($halaman as $i => $isi) {
        $halaman[$i] = $isi . laporanPdfText(40, 25, 8, 'Halaman ' . ($i + 1) . ' dari ' . $jumlahHalaman, false, [0.4, 0.45, 0.5]);
    }

    // pembentuk file pdf: objek 1-4 tetap (katalog, daftar halaman, 2 font), lalu tiap halaman = 2 objek
    $idHalaman = [];
    for ($i = 0; $i < $jumlahHalaman; $i++) {
        $idHalaman[] = (5 + 2 * $i) . ' 0 R';
    }
    $objects = [
        '<< /Type /Catalog /Pages 2 0 R >>',
        '<< /Type /Pages /Kids [' . implode(' ', $idHalaman) . '] /Count ' . $jumlahHalaman . ' >>',
        '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
        '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
    ];
    foreach ($halaman as $i => $isi) {
        $idIsi = 6 + 2 * $i;
        $objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents ' . $idIsi . ' 0 R >>';
        $objects[] = '<< /Length ' . strlen($isi) . " >>\nstream\n" . $isi . "\nendstream";
    }

    $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
    $offsets = [0];
    foreach ($objects as $index => $object) {
        $offsets[] = strlen($pdf);
        $pdf .= ($index + 1) . " 0 obj\n" . $object . "\nendobj\n";
    }

    $xrefOffset = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
    for ($i = 1; $i < count($offsets); $i++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
    }
    $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n" . $xrefOffset . "\n%%EOF";

    return $pdf;
}

// pas tombol unduh dipencet, file pdf langsung dibuat dan dikirim ke browser
if (($_GET['unduh'] ?? '') === 'pdf' && $error === '' && !empty($transaksi)) {
    $pdf = laporanBuatPdf($periodeLabel, $transaksi, $totalQty, $jumlahJenisMenu, $totalPendapatan);
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="laporan-transaksi-' . $periode . '.pdf"');
    header('Content-Length: ' . strlen($pdf));
    echo $pdf;
    exit;
}

$pageTitle = 'Cetak Laporan';
$activeNav = 'dashboard';
require __DIR__ . '/includes/header.php';
?>

<section class="page-heading">
    <div>
        <h1>Cetak Laporan Transaksi</h1>
        <p class="page-description">Ringkasan transaksi pesanan yang sudah selesai.</p>
    </div>
    <a class="button button-neutral no-print" href="index.php">Kembali ke Dashboard</a>
</section>

<?php if ($error !== ''): ?>
    <div class="form-message form-message--error"><?= psnE($error) ?></div>
<?php endif; ?>

<section class="report-panel">
    <div class="report-header">
        <h2>Cetak Laporan Transaksi</h2>
        <p>SIKAT - Catering Nasi Kotak Malang by Tya</p>
        <p>Jl. Raya Malang No. 123 | WA: 0812-3768-xxxx</p>
    </div>

    <div class="report-content">
        <!-- pilih periode per bulan, halaman dimuat ulang otomatis -->
        <form method="get" action="pesanan_laporan.php" class="report-filter no-print">
            <label for="periode">Periode</label>
            <select id="periode" name="periode" onchange="this.form.submit()">
                <?php foreach ($daftarPeriode as $opsi): ?>
                    <option value="<?= psnE($opsi) ?>" <?= $opsi === $periode ? 'selected' : '' ?>><?= psnE(laporanNamaBulan($opsi)) ?></option>
                <?php endforeach; ?>
            </select>
            <noscript><button class="button button-primary" type="submit">Tampilkan</button></noscript>
        </form>

        <div class="report-summary">
            <div class="report-summary-card">
                <span>Total Box</span>
                <strong><?= number_format($totalQty, 0, ',', '.') ?></strong>
            </div>
            <div class="report-summary-card">
                <span>Jenis Menu</span>
                <strong><?= $jumlahJenisMenu ?></strong>
            </div>
            <div class="report-summary-card report-summary-card--dark">
                <span>Total Pendapatan</span>
                <strong><?= laporanRupiah($totalPendapatan) ?></strong>
            </div>
        </div>

        <div class="report-table-wrap">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>ID Pesanan</th>
                        <th>Tanggal</th>
                        <th>Menu</th>
                        <th>Jumlah</th>
                        <th>Harga Satuan</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transaksi)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center">Belum ada pesanan selesai pada <?= psnE($periodeLabel) ?>.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($transaksi as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id_pesanan'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars(laporanTanggal($row['tanggal']), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($row['menu'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= number_format($row['qty'], 0, ',', '.') ?> Box</td>
                            <td><?= laporanRupiah($row['harga']) ?></td>
                            <td><?= laporanRupiah($row['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3">Total Keseluruhan</td>
                        <td><?= number_format($totalQty, 0, ',', '.') ?> Box</td>
                        <td></td>
                        <td><?= laporanRupiah($totalPendapatan) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="report-actions no-print">
            <a class="button button-neutral" href="index.php">Batal</a>
            <?php if (!empty($transaksi)): ?>
                <a class="button button-primary" href="pesanan_laporan.php?unduh=pdf&amp;periode=<?= psnE($periode) ?>">Unduh PDF</a>
            <?php else: ?>
                <button class="button button-primary" type="button" disabled title="Tidak ada data untuk diunduh">Unduh PDF</button>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
