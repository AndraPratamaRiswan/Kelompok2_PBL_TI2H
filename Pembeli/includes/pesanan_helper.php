<?php
// Fungsi bantu CRUD pesanan sisi Pembeli. Hanya fungsi, tidak ada echo.
// Semua nama fungsi diawali "pb" supaya tidak bentrok dengan kode yang sudah ada.

function pbE($nilai): string
{
    return htmlspecialchars((string) ($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

function pbRupiah($angka): string
{
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

function pbKode(int $idPesanan): string
{
    return 'SKKT-' . str_pad((string) $idPesanan, 3, '0', STR_PAD_LEFT);
}

function pbTanggalIndo(?string $tanggal): string
{
    if (!$tanggal) {
        return '-';
    }
    $hari  = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $bulan = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $waktu = strtotime($tanggal);
    if ($waktu === false) {
        return (string) $tanggal;
    }
    return $hari[(int) date('w', $waktu)] . ', ' . (int) date('j', $waktu) . ' ' . $bulan[(int) date('n', $waktu)] . ' ' . date('Y', $waktu);
}

// Tanggal format panjang ("12 November 2026"), sama dengan daftar pesanan di Admin
function pbTanggalPanjang(?string $tanggal): string
{
    if (!$tanggal) {
        return '-';
    }
    $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
              'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $waktu = strtotime($tanggal);
    if ($waktu === false) {
        return (string) $tanggal;
    }
    return (int) date('j', $waktu) . ' ' . $bulan[(int) date('n', $waktu)] . ' ' . date('Y', $waktu);
}

// Kelas warna label pembayaran (Lunas hijau, DP kuning), sama dengan di Admin
function pbKelasBayar(string $statusBayar): string
{
    if ($statusBayar === 'Lunas') {
        return 'status-pill--paid';
    }
    if ($statusBayar === 'DP') {
        return 'status-pill--dp';
    }
    return '';
}

// Pemesanan minimal H-3, sama dengan aturan di keranjang.php
function pbTanggalMinimal(): string
{
    return date('Y-m-d', strtotime('+3 days'));
}

// Gambar menu diambil dari folder Admin/assets/img (sama seperti katalog Admin)
function pbGambarMenu(?string $gambarUrl, string $namaMenu = ''): string
{
    $tersedia = ['nasikuning.jpg', 'ayamgeprek.jpg', 'nasirendang.jpg', 'ayambakar.jpg',
                 'nasitumpeng.jpg', 'ayamgoreng.jpg', 'gulekambing.jpg', 'nasiuduk.jpg'];
    $namaFile = $gambarUrl ? basename(parse_url($gambarUrl, PHP_URL_PATH) ?: $gambarUrl) : '';
    if (in_array($namaFile, $tersedia, true)) {
        return '../Admin/assets/img/' . $namaFile;
    }

    $nama = strtolower($namaMenu);
    $peta = [
        'geprek'  => 'ayamgeprek.jpg',
        'rendang' => 'nasirendang.jpg',
        'bakar'   => 'ayambakar.jpg',
        'tumpeng' => 'nasitumpeng.jpg',
        'goreng'  => 'ayamgoreng.jpg',
        'gule'    => 'gulekambing.jpg',
        'kambing' => 'gulekambing.jpg',
        'uduk'    => 'nasiuduk.jpg',
    ];
    foreach ($peta as $kata => $file) {
        if (strpos($nama, $kata) !== false) {
            return '../Admin/assets/img/' . $file;
        }
    }
    return '../Admin/assets/img/nasikuning.jpg';
}

// Ambil daftar id menu (unik) dari menu_id[] di POST
function pbIdMenuDariPost(array $in): array
{
    $hasil = [];
    $idPost = isset($in['menu_id']) && is_array($in['menu_id']) ? $in['menu_id'] : [];
    foreach ($idPost as $idMentah) {
        $id = is_scalar($idMentah) ? filter_var((string) $idMentah, FILTER_VALIDATE_INT) : false;
        if ($id !== false && $id > 0) {
            $hasil[$id] = $id;
        }
    }
    return array_values($hasil);
}

/**
 * Validasi form pesanan pembeli (checkout dan ubah pesanan).
 *
 * @param array       $menuMap          id_menu => baris menu dari database
 * @param string      $tanggalMin       tanggal kirim paling awal (Y-m-d)
 * @param string|null $tanggalLama      tanggal kirim yang sudah tersimpan (boleh dipertahankan)
 * @param array       $idBolehNonaktif  id menu nonaktif yang sudah ada di pesanan
 * @return array ['data' => nilai form, 'error' => string, 'id_menu' => int[], 'jumlah' => int[]]
 */
function pbValidasiPesanan(array $in, array $menuMap, string $tanggalMin, ?string $tanggalLama = null, array $idBolehNonaktif = []): array
{
    $errors = [];
    $teks = function ($kunci) use ($in) {
        return isset($in[$kunci]) && is_scalar($in[$kunci]) ? trim((string) $in[$kunci]) : '';
    };

    $data = [
        'nama'    => $teks('nama'),
        'tanggal' => $teks('tanggal'),
        'jam'     => $teks('jam'),
        'alamat'  => $teks('alamat'),
        'catatan' => $teks('catatan'),
        'baris'   => [],
    ];

    $idPost     = isset($in['menu_id']) && is_array($in['menu_id']) ? $in['menu_id'] : [];
    $jumlahPost = isset($in['jumlah']) && is_array($in['jumlah']) ? $in['jumlah'] : [];

    // menu yang sama digabung (detail_pesanan tidak boleh punya dua baris menu yang sama)
    $gabung = [];
    foreach ($idPost as $i => $idMentah) {
        $idMentah     = is_scalar($idMentah) ? (string) $idMentah : '';
        $jumlahMentah = isset($jumlahPost[$i]) && is_scalar($jumlahPost[$i]) ? (string) $jumlahPost[$i] : '1';
        $data['baris'][] = ['id' => $idMentah, 'jumlah' => $jumlahMentah];

        $id  = filter_var($idMentah, FILTER_VALIDATE_INT);
        $qty = filter_var($jumlahMentah, FILTER_VALIDATE_INT);

        $menuAda = $id !== false && isset($menuMap[$id]);
        $menuBisa = $menuAda && (
            filter_var($menuMap[$id]['is_tersedia'], FILTER_VALIDATE_BOOLEAN)
            || in_array($id, $idBolehNonaktif, true)
        );

        if (!$menuBisa) {
            $errors[] = 'Ada menu yang sudah tidak tersedia. Periksa kembali pesananmu.';
            continue;
        }
        if ($qty === false || $qty < 1 || $qty > 10000) {
            $errors[] = 'Jumlah box harus berupa angka 1 sampai 10.000.';
            continue;
        }
        $gabung[$id] = ($gabung[$id] ?? 0) + $qty;
    }
    if (empty($gabung) && empty($errors)) {
        $errors[] = 'Pilih minimal satu menu.';
    }

    $panjangNama = mb_strlen($data['nama']);
    if ($panjangNama < 1 || $panjangNama > 50) {
        $errors[] = 'Nama pemesan wajib diisi (maksimal 50 karakter).';
    }

    $panjangAlamat = mb_strlen($data['alamat']);
    if ($panjangAlamat < 1 || $panjangAlamat > 255) {
        $errors[] = 'Alamat pengiriman wajib diisi (maksimal 255 karakter).';
    }

    if (mb_strlen($data['catatan']) > 255) {
        $errors[] = 'Catatan maksimal 255 karakter.';
    }

    $tanggal = DateTime::createFromFormat('Y-m-d', $data['tanggal']);
    if (!$tanggal || $tanggal->format('Y-m-d') !== $data['tanggal']) {
        $errors[] = 'Tanggal pengiriman tidak valid.';
    } elseif ($data['tanggal'] < $tanggalMin && $data['tanggal'] !== $tanggalLama) {
        $errors[] = 'Pemesanan minimal H-3. Pilih tanggal pengiriman mulai ' . pbTanggalIndo($tanggalMin) . '.';
    }

    if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $data['jam'])) {
        $errors[] = 'Jam pengiriman tidak valid.';
    }

    return [
        'data'    => $data,
        'error'   => $errors[0] ?? '',
        'id_menu' => array_keys($gabung),
        'jumlah'  => array_values($gabung),
    ];
}


// ---------- KERANJANG (disimpan di $_SESSION['keranjang'], dipakai index.php dan keranjang.php) ----------

// Tambah menu ke keranjang. Nama dan harga selalu diambil dari database, bukan dari browser.
// Mengembalikan [jenis alert bootstrap, pesan] untuk ditampilkan ke pembeli.
function pbTambahKeKeranjang(PDO $pdo, int $idMenu, int $jumlah): array
{
    if (!isset($_SESSION['keranjang']) || !is_array($_SESSION['keranjang'])) {
        $_SESSION['keranjang'] = [];
    }

    $jumlah = max(1, min($jumlah, 1000));

    $menu = pbMenuByIds($pdo, [$idMenu])[$idMenu] ?? null;
    if (!$menu || !filter_var($menu['is_tersedia'], FILTER_VALIDATE_BOOLEAN)) {
        return ['danger', 'Menu tersebut sudah tidak tersedia.'];
    }

    $sudahAda = $_SESSION['keranjang'][$idMenu]['kuantitas'] ?? 0;
    $_SESSION['keranjang'][$idMenu] = [
        'id'        => $idMenu,
        'nama'      => $menu['nama_menu'],
        'harga'     => (float) $menu['harga_menu'],
        'deskripsi' => (string) ($menu['deskripsi'] ?? ''),
        'gambar'    => (string) ($menu['gambar_url'] ?? ''),
        'kuantitas' => min(10000, $sudahAda + $jumlah),
    ];
    return ['success', $jumlah . ' × ' . $menu['nama_menu'] . ' masuk keranjang.'];
}
