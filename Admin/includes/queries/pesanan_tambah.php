<?php
// Sesuai catatan di Backend_baca_ini_pls.md: kalau nomor HP sudah terdaftar pakai
// id_pembeli yang ada, kalau belum buat akun pembeli dulu, baru pesanan dibuat.
// Akun baru diberi kata sandi acak (kolom password_hash wajib diisi).

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

function psnCariAtauBuatPembeli(PDO $pdo, string $noHp): int
{
    $cari = $pdo->prepare('SELECT id_pembeli FROM pembeli WHERE no_hp = :no_hp');
    $cari->execute([':no_hp' => $noHp]);
    $id = $cari->fetchColumn();
    if ($id !== false) {
        return (int) $id;
    }

    $hash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    $buat = $pdo->prepare(
        'INSERT INTO pembeli (password_hash, no_hp) VALUES (:hash, :no_hp)
         ON CONFLICT (no_hp) DO NOTHING
         RETURNING id_pembeli'
    );
    $buat->execute([':hash' => $hash, ':no_hp' => $noHp]);
    $id = $buat->fetchColumn();

    if ($id === false) {
        // keduluan dibuat proses lain, ambil saja yang sudah ada
        $cari->execute([':no_hp' => $noHp]);
        $id = $cari->fetchColumn();
    }
    if ($id === false) {
        throw new RuntimeException('Akun pembeli gagal dibuat.');
    }
    return (int) $id;
}
function tambahPesananAdmin(
    PDO $pdo,
    int $idAdmin,
    string $noHp,
    string $namaPembeli,
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

    $sql = 'CALL sp_tambah_pesanan_admin(
                :id_admin, :no_hp, :nama,
                CAST(:id_menu AS integer[]), CAST(:jumlah AS integer[]),
                :alamat, :status_pesanan, :status_pembayaran,
                CAST(:tanggal AS date), CAST(:jam AS time),
                :catatan, :dp, :ongkir
            )';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_admin'          => $idAdmin,
        ':no_hp'             => $noHp,
        ':nama'              => $namaPembeli,
        ':id_menu'           => '{' . implode(',', array_map('intval', $idMenu)) . '}',
        ':jumlah'            => '{' . implode(',', array_map('intval', $jumlah)) . '}',
        ':alamat'            => $alamat,
        ':status_pesanan'    => $statusPesanan,
        ':status_pembayaran' => $statusPembayaran,
        ':tanggal'           => $tanggalPengiriman,
        ':jam'               => $jamPengiriman,
        ':catatan'           => $catatan,
        ':dp'                => $jumlahDp,
        ':ongkir'            => $biayaOngkir,
    ]);
}

