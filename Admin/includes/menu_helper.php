<?php
function eMenu($value)
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

function pilihanGambarMenu()
{
    return [
        'nasikuning.jpg'  => 'Nasi Kuning',
        'ayamgeprek.jpg'  => 'Nasi Ayam Geprek',
        'nasirendang.jpg' => 'Nasi Rendang',
        'ayambakar.jpg'   => 'Nasi Ayam Bakar',
        'nasitumpeng.jpg' => 'Nasi Tumpeng',
        'ayamgoreng.jpg'  => 'Nasi Ayam Goreng',
        'gulekambing.jpg' => 'Gule Kambing',
        'nasiuduk.jpg'    => 'Nasi Uduk',
    ];
}

function fileGambarMenu($gambar, $namaMenu)
{
    $gambar = trim((string) $gambar);
    $nama = strtolower((string) $namaMenu);
    $daftarGambar = array_keys(pilihanGambarMenu());

    if ($gambar !== '') {
        $url = filter_var($gambar, FILTER_VALIDATE_URL);

        if ($url && stripos($gambar, 'example.com') === false) {
            return $gambar;
        }

        // Kalau database menyimpan nama file gambar
        $namaFile = basename(
            parse_url($gambar, PHP_URL_PATH) ?: $gambar
        );

        if (in_array($namaFile, $daftarGambar, true)) {
            return 'assets/img/' . $namaFile;
        }
    }

    // Pilih gambar berdasarkan nama menu 
    if (strpos($nama, 'geprek') !== false) {
        return 'assets/img/ayamgeprek.jpg';
    }
    if (strpos($nama, 'rendang') !== false) {
        return 'assets/img/nasirendang.jpg';
    }
    if (strpos($nama, 'bakar') !== false) {
        return 'assets/img/ayambakar.jpg';
    }
    if (strpos($nama, 'tumpeng') !== false) {
        return 'assets/img/nasitumpeng.jpg';
    }
    if (strpos($nama, 'goreng') !== false) {
        return 'assets/img/ayamgoreng.jpg';
    }
    if (strpos($nama, 'kambing') !== false ||
        strpos($nama, 'gule') !== false) {
        return 'assets/img/gulekambing.jpg';
    }
    if (strpos($nama, 'uduk') !== false) {
        return 'assets/img/nasiuduk.jpg';
    }
    // Gambar default
    return 'assets/img/nasikuning.jpg';
}