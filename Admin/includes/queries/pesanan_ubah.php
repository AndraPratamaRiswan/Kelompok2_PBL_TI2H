<?php
function psnMenuUntukForm(PDO $pdo, array $idTerpakai = []): array
{
    $sql = 'SELECT id_menu, nama_menu, harga_menu, is_tersedia FROM menu WHERE is_tersedia = TRUE';
    $params = [];

    if (!empty($idTerpakai)) {
        $sql .= ' OR id_menu = ANY(CAST(:ids AS integer[]))';
        $params[':ids'] = '{' . implode(',', array_map('intval', $idTerpakai)) . '}';
    }

    $sql .= ' ORDER BY nama_menu';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}