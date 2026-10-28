<?php
function ambilKatalogMenu(PDO $pdo, string $q = ''): array
{
    $sql = "
        SELECT
            m.id_menu,
            m.nama_menu,
            m.deskripsi,
            m.harga_menu,
            m.gambar_url,
            m.is_tersedia,
            COALESCE(STRING_AGG(DISTINCT k.nama_kategori, ', '), 'Belum ada kategori') AS daftar_kategori
        FROM menu m
        LEFT JOIN kategori_menu km ON km.id_menu = m.id_menu
        LEFT JOIN kategori k ON k.id_kategori = km.id_kategori
    ";

    $params = [];
    if ($q !== '') {
        $sql .= " WHERE m.nama_menu ILIKE :nama OR COALESCE(m.deskripsi, '') ILIKE :deskripsi ";
        $params[':nama'] = '%' . $q . '%';
        $params[':deskripsi'] = '%' . $q . '%';
    }

    $sql .= " GROUP BY m.id_menu ORDER BY m.id_menu DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
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