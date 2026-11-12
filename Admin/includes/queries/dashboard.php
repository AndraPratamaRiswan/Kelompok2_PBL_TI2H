<?php
function tampilDaftarPesanan(PDO $pdo){
    $sql = "SELECT id_pesanan, 
    'Pesanan '|| nama_pembeli as nama_pemesan, 
    tanggal_dipesan, 
    tanggal_pengiriman, 
    status_pesanan, 
    status_pembayaran FROM pesanan
    ORDER BY tanggal_dipesan DESC";

    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);

}

function tampilMenuTerlaris(PDO $pdo, string $tanggalAwal, string $tanggalAkhir){
    $sql = "SELECT * FROM fn_menu_terlaris(:tanggalAwal, :tanggalAkhir)";
    $params = [];
    $params[':tanggalAwal'] = $tanggalAwal;
    $params[':tanggalAkhir'] = $tanggalAkhir;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC); 
}

function grafikTotalPendapatan(PDO $pdo, string $batasAwal, string $batasAkhir, string $periode = 'day'): array
{
    $periodeValid = ['day', 'week', 'month'];
    if (!in_array($periode, $periodeValid, true)) {
        $periode = 'day';
    }

    $sql = "SELECT CAST(d AS DATE) AS periode,
                   COALESCE(SUM(p.total_harga), 0) AS pendapatan
            FROM generate_series(
                     CAST(DATE_TRUNC('$periode', CAST(:awal AS DATE)) AS DATE),
                     CAST(:akhir AS DATE),
                     INTERVAL '1 $periode'
                 ) AS d
            LEFT JOIN pesanan p
                   ON CAST(DATE_TRUNC('$periode', p.tanggal_dipesan) AS DATE) = CAST(d AS DATE)
            GROUP BY d
            ORDER BY d";

    $params = [];
    $params[':awal']  = $batasAwal;
    $params[':akhir'] = $batasAkhir;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// ---------- DATA DASHBOARD (semua dari database) ----------

// Kartu statistik: total pesanan, pesanan diproses, pesanan selesai, total pendapatan.
// Pendapatan = uang yang sudah masuk: pesanan Lunas dihitung total_harga, pesanan DP
// dihitung jumlah_dp (sama dengan "Sudah Dibayar" di halaman detail pesanan).
function dashStatistik(PDO $pdo): array
{
    $sql = "SELECT COUNT(*) AS total_pesanan,
                   COUNT(*) FILTER (WHERE status_pesanan = 'Diproses') AS pesanan_diproses,
                   COUNT(*) FILTER (WHERE status_pesanan = 'Selesai') AS pesanan_selesai,
                   COALESCE(SUM(CASE status_pembayaran
                                    WHEN 'Lunas' THEN total_harga
                                    WHEN 'DP' THEN jumlah_dp
                                    ELSE 0
                                END), 0) AS total_pendapatan
            FROM pesanan";
    return $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
}

// Jumlah pesanan masuk per hari (berdasarkan tanggal_dipesan) untuk satu minggu, Senin-Minggu.
// Hasil: array 7 elemen ['hari' => 'Sen', 'tanggal' => 'Y-m-d', 'jumlah' => int, 'tinggi' => 0-100]
function dashTrenMingguan(PDO $pdo, ?DateTimeInterface $acuan = null): array
{
    $hariIni = $acuan ? DateTimeImmutable::createFromInterface($acuan) : new DateTimeImmutable('today');
    $senin   = $hariIni->modify('-' . ((int) $hariIni->format('N') - 1) . ' days');
    $minggu  = $senin->modify('+6 days');

    $stmt = $pdo->prepare(
        'SELECT tanggal_dipesan, COUNT(*) AS jumlah
         FROM pesanan
         WHERE tanggal_dipesan BETWEEN CAST(:awal AS date) AND CAST(:akhir AS date)
         GROUP BY tanggal_dipesan'
    );
    $stmt->execute([':awal' => $senin->format('Y-m-d'), ':akhir' => $minggu->format('Y-m-d')]);
    $perTanggal = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $baris) {
        $perTanggal[$baris['tanggal_dipesan']] = (int) $baris['jumlah'];
    }

    $namaHari = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Ming'];
    $hasil = [];
    $maks  = 0;
    for ($i = 0; $i < 7; $i++) {
        $tanggal = $senin->modify("+$i days")->format('Y-m-d');
        $jumlah  = $perTanggal[$tanggal] ?? 0;
        $maks    = max($maks, $jumlah);
        $hasil[] = ['hari' => $namaHari[$i], 'tanggal' => $tanggal, 'jumlah' => $jumlah, 'tinggi' => 0];
    }
    foreach ($hasil as $i => $baris) {
        $hasil[$i]['tinggi'] = $maks > 0 ? (int) round($baris['jumlah'] / $maks * 100) : 0;
    }
    return $hasil;
}

// Menu paling banyak dipesan (total box dari detail_pesanan). 'persentase' relatif terhadap menu terlaris.
function dashMenuTerlaris(PDO $pdo, int $batas = 5): array
{
    $stmt = $pdo->prepare(
        'SELECT m.nama_menu, SUM(dp.jumlah) AS total_box
         FROM detail_pesanan dp
         JOIN menu m ON m.id_menu = dp.id_menu
         GROUP BY m.id_menu, m.nama_menu
         ORDER BY total_box DESC, m.nama_menu
         LIMIT :batas'
    );
    $stmt->bindValue(':batas', $batas, PDO::PARAM_INT);
    $stmt->execute();
    $baris = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $maks = 0;
    foreach ($baris as $b) {
        $maks = max($maks, (int) $b['total_box']);
    }
    $hasil = [];
    foreach ($baris as $b) {
        $hasil[] = [
            'nama'       => $b['nama_menu'],
            'total_box'  => (int) $b['total_box'],
            'persentase' => $maks > 0 ? (int) round($b['total_box'] / $maks * 100) : 0,
        ];
    }
    return $hasil;
}
