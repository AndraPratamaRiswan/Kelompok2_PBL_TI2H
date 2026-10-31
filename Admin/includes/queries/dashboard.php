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

