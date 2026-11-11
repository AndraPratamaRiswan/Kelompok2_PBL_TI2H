<?php
date_default_timezone_set('Asia/Jakarta');

$periodeLabel = 'September 2026';

$transaksiDummy = [
    ['tanggal' => '2026-09-01', 'menu' => 'Nasi Ayam Bakar Sedap', 'qty' => 50, 'harga' => 25000],
    ['tanggal' => '2026-09-01', 'menu' => 'Nasi Ayam Geprek Jos', 'qty' => 50, 'harga' => 25000],
    ['tanggal' => '2026-09-02', 'menu' => 'Nasi Rendang Nyam', 'qty' => 50, 'harga' => 25000],
    ['tanggal' => '2026-09-02', 'menu' => 'Nasi Kuning', 'qty' => 50, 'harga' => 25000],
];

// hitung total box, pendapatan, ama jumlah jenis menu
$totalQty = 0;
$totalPendapatan = 0;
$daftarJenisMenu = [];

foreach ($transaksiDummy as &$row) {
    $row['total'] = $row['qty'] * $row['harga'];
    $totalQty += $row['qty'];
    $totalPendapatan += $row['total'];
    $daftarJenisMenu[$row['menu']] = true;
}
unset($row);

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

function laporanBuatPdf(string $periodeLabel, array $baris, int $totalQty, int $jumlahJenisMenu, float $totalPendapatan): string
{
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

    // header table
    $stream .= laporanPdfRect(40, 365, 762, 30, [0.25, 0.37, 0.49]);
    $stream .= laporanPdfText(52, 376, 10, 'Tanggal', false, [1, 1, 1]);
    $stream .= laporanPdfText(175, 376, 10, 'Menu', false, [1, 1, 1]);
    $stream .= laporanPdfText(475, 376, 10, 'Qty', false, [1, 1, 1]);
    $stream .= laporanPdfText(550, 376, 10, 'Harga Satuan', false, [1, 1, 1]);
    $stream .= laporanPdfText(705, 376, 10, 'Total', false, [1, 1, 1]);

    $y = 335;
    foreach ($baris as $index => $row) {
        $warnaBaris = ($index % 2 === 0) ? [1, 1, 1] : [0.94, 0.96, 0.98];
        $stream .= laporanPdfRect(40, $y - 8, 762, 28, $warnaBaris);
        $stream .= laporanPdfText(52, $y + 1, 9, laporanTanggal($row['tanggal']));
        $stream .= laporanPdfText(175, $y + 1, 9, $row['menu']);
        $stream .= laporanPdfText(475, $y + 1, 9, $row['qty'] . ' Box');
        $stream .= laporanPdfText(550, $y + 1, 9, laporanRupiah($row['harga']));
        $stream .= laporanPdfText(705, $y + 1, 9, laporanRupiah($row['total']));
        $y -= 31;
    }

    // baris total
    $stream .= laporanPdfRect(40, $y - 8, 762, 32, [0.88, 0.92, 0.96]);
    $stream .= laporanPdfText(52, $y + 3, 10, 'TOTAL KESELURUHAN', true);
    $stream .= laporanPdfText(475, $y + 3, 10, number_format($totalQty, 0, ',', '.') . ' Box', true);
    $stream .= laporanPdfText(705, $y + 3, 10, laporanRupiah($totalPendapatan), true);

    // pembentuk file pdf
    $objects = [
        '<< /Type /Catalog /Pages 2 0 R >>',
        '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
        '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >>',
        '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
        '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
        '<< /Length ' . strlen($stream) . " >>\nstream\n" . $stream . "\nendstream",
    ];

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
if (($_GET['unduh'] ?? '') === 'pdf') {
    $pdf = laporanBuatPdf($periodeLabel, $transaksiDummy, $totalQty, $jumlahJenisMenu, $totalPendapatan);
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="laporan-transaksi-september-2026.pdf"');
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

<section class="report-panel">
    <div class="report-header">
        <h2>Cetak Laporan Transaksi</h2>
        <p>SIKAT - Catering Nasi Kotak Malang by Tya</p>
        <p>Jl. Raya Malang No. 123 | WA: 0812-3768-xxxx</p>
    </div>

    <div class="report-content">
        <!-- dropdown dummy -->
        <div class="report-filter no-print">
            <label for="periode">Periode</label>
            <select id="periode" disabled>
                <option selected>September 2026</option>
                <option>Oktober 2026</option>
                <option>November 2026</option>
            </select>
        </div>

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
                        <th>Tanggal</th>
                        <th>Menu</th>
                        <th>Qty</th>
                        <th>Harga Satuan</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transaksiDummy as $row): ?>
                        <tr>
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
                        <td colspan="2">Total Keseluruhan</td>
                        <td><?= number_format($totalQty, 0, ',', '.') ?> Box</td>
                        <td></td>
                        <td><?= laporanRupiah($totalPendapatan) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="report-actions no-print">
            <a class="button button-neutral" href="index.php">Batal</a>
            <a class="button button-primary" href="pesanan_laporan.php?unduh=pdf">Unduh PDF</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>