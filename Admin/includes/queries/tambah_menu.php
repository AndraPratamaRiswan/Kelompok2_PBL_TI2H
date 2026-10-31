<?php
function tambahMenu(
    PDO $pdo,
    int $idAdmin,
    array $idKategori,
    string $nama,
    float $harga,
    ?string $deskripsi,
    string $gambarUrl
): void {
    $kategoriPg = '{' . implode(',', array_map('intval', $idKategori)) . '}';

    $sql = 'CALL sp_tambah_menu(:id_admin, CAST(:kategori AS integer[]), :nama, :harga, :deskripsi, :gambar)';

    $params = [
        ':id_admin'  => $idAdmin,
        ':kategori'  => $kategoriPg,
        ':nama'      => $nama,
        ':harga'     => $harga,
        ':deskripsi' => $deskripsi,
        ':gambar'    => $gambarUrl,
    ];

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
}