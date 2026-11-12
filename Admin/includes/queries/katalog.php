<?php
function ambilKatalogMenu(PDO $pdo, string $q = '', int $idKategori = 0): array
{
    $sql = "SELECT * FROM v_tampil_menu_catalog v";
    $where = [];
    $params = [];

    if ($q !== '') {
        // dikurung supaya OR di sini tidak bercampur dengan filter kategori di bawahnya
        $where[] = "(v.nama_menu ILIKE :nama
                  OR COALESCE(v.deskripsi, '') ILIKE :deskripsi
                  OR v.daftar_kategori ILIKE :kategori_teks)";
        $params[':nama']           = '%' . $q . '%';
        $params[':deskripsi']      = '%' . $q . '%';
        $params[':kategori_teks']  = '%' . $q . '%';
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