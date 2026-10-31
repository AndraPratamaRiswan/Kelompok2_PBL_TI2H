<?php
function editMenu(
    PDO $pdo,
    int $idMenu,
    array $idKategori,
    string $nama,
    float $harga,
    ?string $deskripsi,
    string $gambarUrl
): void {
    $kategoriPg = '{' . implode(',', array_map('intval', $idKategori)) . '}';

    $sql = 'CALL sp_edit_menu(:id, CAST(:kategori AS integer[]), :nama, :harga, :deskripsi, :gambar)';

    $params = [
        ':id'        => $idMenu,
        ':kategori'  => $kategoriPg,
        ':nama'      => $nama,
        ':harga'     => $harga,
        ':deskripsi' => $deskripsi,
        ':gambar'    => $gambarUrl,
    ];

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
}

function ambilMenuById(PDO $pdo, int $idMenu): array|false
{
    $sql = 'SELECT id_menu, nama_menu, deskripsi, harga_menu, gambar_url, is_tersedia
            FROM menu
            WHERE id_menu = :id';

    $params = [':id' => $idMenu];

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch();
}

function ambilIdKategoriMenu(PDO $pdo, int $idMenu): int
{
    $sql = 'SELECT id_kategori
            FROM kategori_menu
            WHERE id_menu = :id
            ORDER BY id_kategori
            LIMIT 1';

    $params = [':id' => $idMenu];

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int) ($stmt->fetchColumn() ?: 0);
}