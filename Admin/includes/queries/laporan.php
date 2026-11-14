<?php
// File ini hanya berisi fungsi. Tidak ada echo dan tidak ada HTML.
// Laporan transaksi = pesanan berstatus "Selesai", dikelompokkan per bulan
// berdasarkan tanggal_dipesan (sama dengan view v_cetak_laporan_admin).

// Daftar bulan ('YYYY-MM') yang punya pesanan selesai, terbaru dulu.
function lapPeriodeTersedia(PDO $pdo): array
{
    $sql = "SELECT DISTINCT TO_CHAR(tanggal_dipesan, 'YYYY-MM') AS periode
            FROM pesanan
            WHERE status_pesanan = 'Selesai'
            ORDER BY periode DESC";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);
}

// Satu baris per menu per pesanan selesai pada bulan $periode ('YYYY-MM').
function lapTransaksi(PDO $pdo, string $periode): array
{
    $awal = DateTimeImmutable::createFromFormat('!Y-m-d', $periode . '-01');
    $akhir = $awal->modify('+1 month');

    $sql = "SELECT p.id_pesanan, p.tanggal_dipesan, m.nama_menu,
                   dp.jumlah, dp.harga_satuan, dp.subtotal
            FROM pesanan p
            JOIN detail_pesanan dp ON dp.id_pesanan = p.id_pesanan
            JOIN menu m ON m.id_menu = dp.id_menu
            WHERE p.status_pesanan = 'Selesai'
              AND p.tanggal_dipesan >= CAST(:awal AS date)
              AND p.tanggal_dipesan <  CAST(:akhir AS date)
            ORDER BY p.tanggal_dipesan, p.id_pesanan, m.nama_menu";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':awal' => $awal->format('Y-m-d'), ':akhir' => $akhir->format('Y-m-d')]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
