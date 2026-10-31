<?php
function dataPesananPembeli(PDO $pdo, string $no_hp): array
{
    $sql = "SELECT * FROM v_data_pesanan WHERE no_hp = :no_hp";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([":no_hp" => $no_hp]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}