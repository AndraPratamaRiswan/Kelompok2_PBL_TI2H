<?php
function ambilKatalogMenu(PDO $pdo, string $q = '', int $idKategori = 0): array
{
    $sql = "SELECT * FROM v_tampil_menu_catalog v";
    $where = [];
    $params = [];

    if ($q !== '') {
        // dikurung supaya OR di sini tidak bercampur dengan filter kategori di bawahnya
        $where[] = "(v.id_menu IN (SELECT menu_id FROM fn_cari_menu(:q))
          OR v.daftar_kategori ILIKE :kategori_teks)";
        $params[':q']             = $q;
        $params[':kategori_teks'] = '%' . $q . '%';
    }

    if ($idKategori > 0) {
        $where[] = "EXISTS (SELECT 1 FROM kategori_menu km
                            WHERE km.id_menu = v.id_menu AND km.id_kategori = :id_kategori)";
        $params[':id_kategori'] = $idKategori;
    }

    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }

    $sql .= " ORDER BY v.id_menu DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Memastikan tiga kategori dasar ada di tabel kategori (dibuat kalau belum ada, tidak pernah menimpa
// atau menduplikasi). Dipanggil dari halaman admin yang memakai daftar kategori, jadi file SQL tidak perlu diubah.
// Dibuat satu per satu secara berurutan supaya id (dan urutan tampil) sesuai urutan di bawah, dan memakai
// INSERT ... SELECT ... WHERE NOT EXISTS supaya nomor serial tidak terbuang tiap halaman dibuka.
function pastikanKategoriDasar(PDO $pdo): void
{
    $kategoriDasar = ['Makanan Oriental', 'Makanan Khas Indonesia', 'Makanan Western'];

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO kategori (nama_kategori)
             SELECT CAST(:nama AS varchar)
             WHERE NOT EXISTS (SELECT 1 FROM kategori WHERE nama_kategori = :nama2)"
        );
        foreach ($kategoriDasar as $nama) {
            $stmt->execute([':nama' => $nama, ':nama2' => $nama]);
        }
    } catch (PDOException $e) {
        error_log('Gagal memastikan kategori dasar: ' . $e->getMessage());
    }
}


function ambilKategori(PDO $pdo): array
{
    return $pdo->query('SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori')->fetchAll();
}

function daftarGambarMenu(): array
{
    return [
        'nasikuning.jpg'  => 'Nasi Kuning (nasikuning.jpg)',
        'ayamgeprek.jpg'  => 'Nasi Ayam Geprek (ayamgeprek.jpg)',
        'nasirendang.jpg'=> 'Nasi Rendang (nasirendang.jpg)',
        'ayambakar.jpg'   => 'Nasi Ayam Bakar (ayambakar.jpg)',
        'nasitumpeng.jpg' => 'Nasi Tumpeng (nasitumpeng.jpg)',
        'ayamgoreng.jpg'  => 'Nasi Ayam Goreng (ayamgoreng.jpg)',
        'gulekambing.jpg' => 'Gule Kambing (gulekambing.jpg)',
        'nasiuduk.jpg'    => 'Nasi Uduk (nasiuduk.jpg)',
    ];
}

function gambarMenuLokal(?string $gambarUrl, string $namaMenu = ''): string
{
    // gambar hasil upload (kalau filenya hilang dari folder, jatuh ke gambar bawaan di bawah)
    require_once __DIR__ . '/../gambar_upload.php';
    $upload = namaFileGambarUpload($gambarUrl);
    if ($upload !== null && is_file(gambarUploadDirFisik() . '/' . $upload)) {
        return '../Admin/assets/img/' . GAMBAR_UPLOAD_FOLDER . '/' . $upload;
    }

    $daftar = daftarGambarMenu();
    $namaFile = $gambarUrl ? basename(parse_url($gambarUrl, PHP_URL_PATH) ?: $gambarUrl) : '';

    if (isset($daftar[$namaFile])) {
        return '../Admin/assets/img/' . $namaFile;
    }

    $nama = strtolower($namaMenu);
    $petaNama = [
        'geprek' => 'ayamgeprek.jpg',
        'rendang' => 'nasirendang.jpg',
        'bakar' => 'ayambakar.jpg',
        'tumpeng' => 'nasitumpeng.jpg',
        'goreng' => 'ayamgoreng.jpg',
        'gule' => 'gulekambing.jpg',
        'uduk' => 'nasiuduk.jpg',
    ];
    foreach ($petaNama as $kata => $file) {
        if (strpos($nama, $kata) !== false) {
            return '../Admin/assets/img/' . $file;
        }
    }
    return '../Admin/assets/img/nasikuning.jpg';
}

function mnuUbahStatus(PDO $pdo, int $idMenu, bool $tersedia): bool
{
    $stmt = $pdo->prepare('SELECT fn_ubah_status_menu(:id, CAST(:tersedia AS boolean))');
    $stmt->execute([':id' => $idMenu, ':tersedia' => $tersedia ? 'true' : 'false']);
    return filter_var($stmt->fetchColumn(), FILTER_VALIDATE_BOOLEAN);
}
