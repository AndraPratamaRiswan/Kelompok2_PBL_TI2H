<?php
function psnDaftar(PDO $pdo, string $q = '', string $status = '', bool $belumSelesai = false): array
{
    $sql = "SELECT p.id_pesanan, p.nama_pembeli, pb.no_hp,
                   p.tanggal_dipesan, p.tanggal_pengiriman,
                   p.status_pesanan, p.status_pembayaran, p.total_harga,
                   COALESCE(
                       STRING_AGG(m.nama_menu || ' (' || CAST(dp.jumlah AS text) || ' box)', ', ' ORDER BY m.nama_menu),
                       '-'
                   ) AS ringkasan_menu
            FROM pesanan p
            JOIN pembeli pb ON pb.id_pembeli = p.id_pembeli
            LEFT JOIN detail_pesanan dp ON dp.id_pesanan = p.id_pesanan
            LEFT JOIN menu m ON m.id_menu = dp.id_menu
            WHERE 1 = 1";
    $params = [];

    if ($q !== '') {
        // nama parameter dibuat beda-beda karena PDO native prepare tidak boleh memakai nama yang sama dua kali
        $sql .= " AND (p.nama_pembeli ILIKE :q1
                    OR pb.no_hp ILIKE :q2
                    OR ('SKKT-' || LPAD(CAST(p.id_pesanan AS text), 3, '0')) ILIKE :q3)";
        $params[':q1'] = '%' . $q . '%';
        $params[':q2'] = '%' . $q . '%';
        $params[':q3'] = '%' . $q . '%';
    }

    if ($status !== '') {
        $sql .= " AND p.status_pesanan = :status";
        $params[':status'] = $status;
    }

    if ($belumSelesai) {
        $sql .= " AND p.status_pesanan <> 'Selesai'";
    }

    $sql .= " GROUP BY p.id_pesanan, pb.no_hp
              ORDER BY p.created_at DESC, p.id_pesanan DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}