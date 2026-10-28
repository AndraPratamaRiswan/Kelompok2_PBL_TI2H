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
    $sql .= " AND EXISTS (
                SELECT *
                FROM menu_kategori mk
                JOIN kategori k ON k.id_kategori = mk.id_kategori
                WHERE mk.id_menu = v.id_menu
                  AND k.nama_kategori = :kategori
             )";
    $params[':kategori'] = $kategori;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}