<?php
function tambahPesanan(
    PDO $pdo,
    int $idAdmin,
    string $namaPembeli,
    ?int $idPembeli,
    array $idMenu,
    array $jumlah,
    string $alamat,
    string $statusPesanan,
    string $statusPembayaran,
    ?string $tanggalPengiriman,
    ?string $jamPengiriman,
    ?string $catatan,
    float $jumlahDp,
    float $biayaOngkir
): void {
    if (count($idMenu) !== count($jumlah)) {
        throw new InvalidArgumentException('Jumlah id_menu dan jumlah tidak sama.');
    }

    $menuPg   = '{' . implode(',', array_map('intval', $idMenu)) . '}';
    $jumlahPg = '{' . implode(',', array_map('intval', $jumlah)) . '}';

    $sql = 'CALL sp_tambah_pesanan(
                :id_admin, :nama, :id_pembeli,
                CAST(:id_menu AS integer[]), CAST(:jumlah AS integer[]),
                :alamat, :status_pesanan, :status_pembayaran,
                CAST(:tanggal AS date), CAST(:jam AS time),
                :catatan, :dp, :ongkir
            )';

    $params = [
        ':id_admin'          => $idAdmin,
        ':nama'              => $namaPembeli,
        ':id_pembeli'        => $idPembeli,
        ':id_menu'           => $menuPg,
        ':jumlah'            => $jumlahPg,
        ':alamat'            => $alamat,
        ':status_pesanan'    => $statusPesanan,
        ':status_pembayaran' => $statusPembayaran,
        ':tanggal'           => $tanggalPengiriman,
        ':jam'               => $jamPengiriman,
        ':catatan'           => $catatan,
        ':dp'                => $jumlahDp,
        ':ongkir'            => $biayaOngkir,
    ];

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
}