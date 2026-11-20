
<?php
function psnAmbil(PDO $pdo, int $idPesanan): array|false
{
    $sql = "SELECT p.*, pb.no_hp
            FROM pesanan p
            JOIN pembeli pb ON pb.id_pembeli = p.id_pembeli
            WHERE p.id_pesanan = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $idPesanan]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function psnItems(PDO $pdo, int $idPesanan): array
{
    $sql = "SELECT dp.id_menu, m.nama_menu, dp.jumlah, dp.harga_satuan, dp.subtotal
            FROM detail_pesanan dp
            JOIN menu m ON m.id_menu = dp.id_menu
            WHERE dp.id_pesanan = :id
            ORDER BY m.nama_menu";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $idPesanan]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}