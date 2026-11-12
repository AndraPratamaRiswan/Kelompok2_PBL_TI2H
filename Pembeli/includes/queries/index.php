<?php
// File ini hanya berisi fungsi. Tidak ada echo dan tidak ada HTML.

// Menu untuk halaman utama pembeli: hanya yang tersedia, bisa dicari lewat nama
// dan difilter lewat nama kategori.
function ambilKatalogMenu(PDO $pdo, string $q = '', string $kategori = ''): array
{
    $sql    = "SELECT * FROM v_tampil_menu_catalog v WHERE v.is_tersedia = TRUE";
    $params = [];

    if ($q !== '') {
        $sql .= " AND v.nama_menu ILIKE :q";
        $params[':q'] = '%' . $q . '%';
    }
    if ($kategori !== '') {
        $sql .= " AND EXISTS (
                    SELECT 1
                    FROM kategori_menu km
                    JOIN kategori k ON k.id_kategori = km.id_kategori
                    WHERE km.id_menu = v.id_menu
                      AND k.nama_kategori = :kategori
                 )";
        $params[':kategori'] = $kategori;
    }

    $sql .= " ORDER BY v.nama_menu";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
