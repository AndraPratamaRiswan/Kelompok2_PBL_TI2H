<?php
// File ini hanya berisi fungsi. Tidak ada echo dan tidak ada HTML.
// Semua fungsi diawali "pb" supaya tidak bentrok dengan queries/index.php dkk.

// ---------- MENU ----------

function pbKategoriMenu(PDO $pdo): array
{
    return $pdo->query('SELECT nama_kategori FROM kategori ORDER BY nama_kategori')->fetchAll(PDO::FETCH_COLUMN);
}

// Hanya menu yang tersedia (is_tersedia = true) yang tampil untuk pembeli
function pbMenuTersedia(PDO $pdo, string $q = '', string $kategori = ''): array
{
    $sql = "SELECT id_menu, nama_menu, deskripsi, harga_menu, gambar_url, daftar_kategori
            FROM v_tampil_menu_catalog
            WHERE is_tersedia = TRUE";
    $params = [];

    if ($q !== '') {
        $sql .= " AND nama_menu ILIKE :q";
        $params[':q'] = '%' . $q . '%';
    }
    if ($kategori !== '') {
        $sql .= " AND (', ' || daftar_kategori || ', ') LIKE :kategori";
        $params[':kategori'] = '%, ' . $kategori . ', %';
    }

    $sql .= " ORDER BY nama_menu";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Hasilnya diindeks dengan id_menu. Harga selalu diambil dari database, bukan dari browser.
function pbMenuByIds(PDO $pdo, array $idMenu): array
{
    if (empty($idMenu)) {
        return [];
    }
    $stmt = $pdo->prepare(
        'SELECT id_menu, nama_menu, deskripsi, harga_menu, gambar_url, is_tersedia
         FROM menu
         WHERE id_menu = ANY(CAST(:ids AS integer[]))
         ORDER BY nama_menu'
    );
    $stmt->execute([':ids' => '{' . implode(',', array_map('intval', $idMenu)) . '}']);

    $hasil = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $baris) {
        $hasil[(int) $baris['id_menu']] = $baris;
    }
    return $hasil;
}

// ---------- PESANAN ----------

function pbIdAdminPertama(PDO $pdo): ?int
{
    $id = $pdo->query('SELECT id_admin FROM admin ORDER BY id_admin LIMIT 1')->fetchColumn();
    return $id === false ? null : (int) $id;
}

// Pesanan dari pembeli masuk dengan status awal "Belum Dikonfirmasi" dan "Belum Bayar".
// DP dan ongkir diisi admin nanti.
function pbTambahPesanan(
    PDO $pdo,
    int $idAdmin,
    int $idPembeli,
    string $namaPembeli,
    array $idMenu,
    array $jumlah,
    string $alamat,
    string $tanggalPengiriman,
    string $jamPengiriman,
    ?string $catatan
): void {
    if (count($idMenu) !== count($jumlah)) {
        throw new InvalidArgumentException('Jumlah id_menu dan jumlah tidak sama.');
    }

    $sql = 'CALL sp_tambah_pesanan(
                :id_admin, :nama, :id_pembeli,
                CAST(:id_menu AS integer[]), CAST(:jumlah AS integer[]),
                :alamat, :status_pesanan, :status_pembayaran,
                CAST(:tanggal AS date), CAST(:jam AS time),
                :catatan, :dp, :ongkir
            )';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_admin'          => $idAdmin,
        ':nama'              => $namaPembeli,
        ':id_pembeli'        => $idPembeli,
        ':id_menu'           => '{' . implode(',', array_map('intval', $idMenu)) . '}',
        ':jumlah'            => '{' . implode(',', array_map('intval', $jumlah)) . '}',
        ':alamat'            => $alamat,
        ':status_pesanan'    => 'Belum Dikonfirmasi',
        ':status_pembayaran' => 'Belum Bayar',
        ':tanggal'           => $tanggalPengiriman,
        ':jam'               => $jamPengiriman,
        ':catatan'           => $catatan,
        ':dp'                => 0,
        ':ongkir'            => 0,
    ]);
}

function pbItemPesanan(PDO $pdo, array $idPesanan): array
{
    if (empty($idPesanan)) {
        return [];
    }
    $stmt = $pdo->prepare(
        'SELECT dp.id_pesanan, dp.id_menu, m.nama_menu, dp.jumlah, dp.harga_satuan, dp.subtotal
         FROM detail_pesanan dp
         JOIN menu m ON m.id_menu = dp.id_menu
         WHERE dp.id_pesanan = ANY(CAST(:ids AS integer[]))
         ORDER BY dp.id_pesanan, m.nama_menu'
    );
    $stmt->execute([':ids' => '{' . implode(',', array_map('intval', $idPesanan)) . '}']);

    $hasil = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $baris) {
        $hasil[(int) $baris['id_pesanan']][] = $baris;
    }
    return $hasil;
}

// READ: semua pesanan milik satu pembeli beserta item menunya
function pbDaftarPesanan(PDO $pdo, int $idPembeli): array
{
    $stmt = $pdo->prepare(
        'SELECT id_pesanan, nama_pembeli, alamat_pengiriman, tanggal_dipesan, tanggal_pengiriman,
                jam_pengiriman, catatan, status_pesanan, status_pembayaran,
                total_harga, biaya_ongkir, jumlah_dp, created_at
         FROM pesanan
         WHERE id_pembeli = :id
         ORDER BY created_at DESC, id_pesanan DESC'
    );
    $stmt->execute([':id' => $idPembeli]);
    $daftar = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($daftar)) {
        return [];
    }

    $items = pbItemPesanan($pdo, array_map(function ($p) { return (int) $p['id_pesanan']; }, $daftar));
    foreach ($daftar as $i => $pesanan) {
        $daftar[$i]['items'] = $items[(int) $pesanan['id_pesanan']] ?? [];
    }
    return $daftar;
}

// Satu pesanan milik pembeli (pesanan orang lain tidak akan ketemu).
// $kunci = true memakai FOR UPDATE, dipakai di dalam transaksi supaya status
// tidak berubah (misal dikonfirmasi admin) di tengah proses ubah/hapus.
function pbPesananById(PDO $pdo, int $idPesanan, int $idPembeli, bool $kunci = false): array|false
{
    $sql = 'SELECT id_pesanan, nama_pembeli, alamat_pengiriman, tanggal_dipesan, tanggal_pengiriman,
                   jam_pengiriman, catatan, status_pesanan, status_pembayaran,
                   total_harga, biaya_ongkir, jumlah_dp
            FROM pesanan
            WHERE id_pesanan = :id AND id_pembeli = :pembeli';
    if ($kunci) {
        $sql .= ' FOR UPDATE';
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $idPesanan, ':pembeli' => $idPembeli]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Pembeli hanya boleh mengubah/membatalkan pesanan yang belum dikonfirmasi admin
function pbBisaDiubah(array $pesanan): bool
{
    return $pesanan['status_pesanan'] === 'Belum Dikonfirmasi';
}

// UPDATE: status, DP, dan ongkir dibiarkan seperti di database (itu urusan admin)
function pbUbahPesanan(
    PDO $pdo,
    int $idPesanan,
    array $pesananLama,
    string $namaPembeli,
    array $idMenu,
    array $jumlah,
    string $alamat,
    string $tanggalPengiriman,
    string $jamPengiriman,
    ?string $catatan
): void {
    if (count($idMenu) !== count($jumlah)) {
        throw new InvalidArgumentException('Jumlah id_menu dan jumlah tidak sama.');
    }

    $sql = 'CALL sp_edit_pesanan(
                :id_pesanan, :nama,
                CAST(:id_menu AS integer[]), CAST(:jumlah AS integer[]),
                :alamat, :status_pesanan, :status_pembayaran,
                CAST(:tanggal AS date), CAST(:jam AS time),
                :catatan, :dp, :ongkir
            )';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_pesanan'        => $idPesanan,
        ':nama'              => $namaPembeli,
        ':id_menu'           => '{' . implode(',', array_map('intval', $idMenu)) . '}',
        ':jumlah'            => '{' . implode(',', array_map('intval', $jumlah)) . '}',
        ':alamat'            => $alamat,
        ':status_pesanan'    => $pesananLama['status_pesanan'],
        ':status_pembayaran' => $pesananLama['status_pembayaran'],
        ':tanggal'           => $tanggalPengiriman,
        ':jam'               => $jamPengiriman,
        ':catatan'           => $catatan,
        ':dp'                => $pesananLama['jumlah_dp'],
        ':ongkir'            => $pesananLama['biaya_ongkir'],
    ]);
}

// DELETE: detail_pesanan ikut terhapus otomatis (ON DELETE CASCADE)
function pbHapusPesanan(PDO $pdo, int $idPesanan, int $idPembeli): bool
{
    $stmt = $pdo->prepare(
        "DELETE FROM pesanan
         WHERE id_pesanan = :id AND id_pembeli = :pembeli AND status_pesanan = 'Belum Dikonfirmasi'"
    );
    $stmt->execute([':id' => $idPesanan, ':pembeli' => $idPembeli]);
    return $stmt->rowCount() > 0;
}
