<?php
// File ini hanya berisi fungsi. Tidak ada echo dan tidak ada HTML.
// Tambah & ubah pesanan memakai fungsi lama tambahPesanan() dan editPesanan()
// (includes/queries/tambah_pesanan.php dan edit_pesanan.php), jadi di sini hanya
// ada fungsi baca, hapus, dan pendukungnya.

// ---------- PESANAN ----------

// $belumSelesai = true hanya mengambil pesanan yang statusnya belum "Selesai" (dipakai dashboard)
function psnDaftar(PDO $pdo, string $q = '', string $status = '', bool $belumSelesai = false): array
{
    $sql = "SELECT p.id_pesanan, p.nama_pembeli, pb.no_hp,
                   p.tanggal_dipesan, p.tanggal_pengiriman,
                   p.status_pesanan, p.status_pembayaran, p.total_harga,
                   COALESCE(
                       STRING_AGG(m.nama_menu || ' (' || CAST(dp.jumlah AS text) || ' box)', ', ' ORDER BY m.nama_menu),
                       '-'
                   ) AS ringkasan_menu
            FROM pesanan p
            JOIN pembeli pb ON pb.id_pembeli = p.id_pembeli
            LEFT JOIN detail_pesanan dp ON dp.id_pesanan = p.id_pesanan
            LEFT JOIN menu m ON m.id_menu = dp.id_menu
            WHERE 1 = 1";
    $params = [];

    if ($q !== '') {
        // nama parameter dibuat beda-beda karena PDO native prepare tidak boleh memakai nama yang sama dua kali
        $sql .= " AND (p.nama_pembeli ILIKE :q1
                    OR pb.no_hp ILIKE :q2
                    OR ('SKKT-' || LPAD(CAST(p.id_pesanan AS text), 3, '0')) ILIKE :q3)";
        $params[':q1'] = '%' . $q . '%';
        $params[':q2'] = '%' . $q . '%';
        $params[':q3'] = '%' . $q . '%';
    }

    if ($status !== '') {
        $sql .= " AND p.status_pesanan = :status";
        $params[':status'] = $status;
    }

    if ($belumSelesai) {
        $sql .= " AND p.status_pesanan <> 'Selesai'";
    }

    $sql .= " GROUP BY p.id_pesanan, pb.no_hp
              ORDER BY p.created_at DESC, p.id_pesanan DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function psnAmbil(PDO $pdo, int $idPesanan): array|false
{
    $sql = "SELECT p.*, pb.no_hp
            FROM pesanan p
            JOIN pembeli pb ON pb.id_pembeli = p.id_pembeli
            WHERE p.id_pesanan = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $idPesanan]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function psnItems(PDO $pdo, int $idPesanan): array
{
    $sql = "SELECT dp.id_menu, m.nama_menu, dp.jumlah, dp.harga_satuan, dp.subtotal
            FROM detail_pesanan dp
            JOIN menu m ON m.id_menu = dp.id_menu
            WHERE dp.id_pesanan = :id
            ORDER BY m.nama_menu";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $idPesanan]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// detail_pesanan ikut terhapus otomatis (ON DELETE CASCADE)
function psnHapus(PDO $pdo, int $idPesanan): bool
{
    $stmt = $pdo->prepare('DELETE FROM pesanan WHERE id_pesanan = :id');
    $stmt->execute([':id' => $idPesanan]);
    return $stmt->rowCount() > 0;
}

// Daftar menu untuk dropdown. Menu nonaktif yang sudah ada di pesanan
// (lewat $idTerpakai) tetap ikut supaya pesanan lama bisa diedit.
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

function psnIdAdminPertama(PDO $pdo): ?int
{
    $id = $pdo->query('SELECT id_admin FROM admin ORDER BY id_admin LIMIT 1')->fetchColumn();
    return $id === false ? null : (int) $id;
}

// Sesuai catatan di Backend_baca_ini_pls.md: kalau nomor HP sudah terdaftar pakai
// id_pembeli yang ada, kalau belum buat akun pembeli dulu, baru pesanan dibuat.
// Akun baru diberi kata sandi acak (kolom password_hash wajib diisi).
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

// ---------- MENU (melengkapi tambah_menu.php dan edit_menu.php yang sudah ada) ----------

// Mengembalikan 'dihapus', 'dipakai' (sudah ada di riwayat pesanan), atau 'tidak_ditemukan'
function mnuHapus(PDO $pdo, int $idMenu): string
{
    $cek = $pdo->prepare('SELECT EXISTS (SELECT 1 FROM detail_pesanan WHERE id_menu = :id)');
    $cek->execute([':id' => $idMenu]);
    if (filter_var($cek->fetchColumn(), FILTER_VALIDATE_BOOLEAN)) {
        return 'dipakai';
    }

    try {
        // kategori_menu ikut terhapus otomatis (ON DELETE CASCADE)
        $stmt = $pdo->prepare('DELETE FROM menu WHERE id_menu = :id');
        $stmt->execute([':id' => $idMenu]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23503') { // foreign key: ternyata dipakai pesanan
            return 'dipakai';
        }
        throw $e;
    }

    return $stmt->rowCount() > 0 ? 'dihapus' : 'tidak_ditemukan';
}

function mnuUbahStatus(PDO $pdo, int $idMenu, bool $tersedia): bool
{
    $stmt = $pdo->prepare(
        'UPDATE menu SET is_tersedia = CAST(:tersedia AS boolean), updated_at = CURRENT_TIMESTAMP
         WHERE id_menu = :id'
    );
    $stmt->execute([
        ':tersedia' => $tersedia ? 'true' : 'false',
        ':id'       => $idMenu,
    ]);
    return $stmt->rowCount() > 0;
}
