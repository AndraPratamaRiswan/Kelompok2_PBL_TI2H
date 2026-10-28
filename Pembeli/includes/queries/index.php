<?php
// File ini hanya berisi fungsi. Tidak ada echo dan tidak ada HTML.

function ambilKatalogMenu(PDO $pdo, string $q = '', string $kategori = ''): array
{
    $sql    = "SELECT * FROM v_tampil_menu_catalog WHERE 1=1";
    $params = [];

    if ($q !== '') {
        $sql .= " AND nama_menu ILIKE :q";
        $params[':q'] = "%$q%";
    }
    if ($kategori !== '') {
        $sql .= " AND :kategori = ANY(STRING_TO_ARRAY(daftar_kategori, ','))";
        $params[':kategori'] = $kategori;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}