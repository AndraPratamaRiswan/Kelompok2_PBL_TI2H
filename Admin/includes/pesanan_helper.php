<?php
// Fungsi bantu untuk CRUD pesanan di Admin. Hanya berisi fungsi, tidak ada echo.
// Semua nama fungsi diawali "psn" supaya tidak bentrok dengan fungsi yang sudah ada.

function psnE($nilai): string
{
    return htmlspecialchars((string) ($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

function psnRupiah($angka): string
{
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

function psnKode(int $idPesanan): string
{
    return 'SKKT-' . str_pad((string) $idPesanan, 3, '0', STR_PAD_LEFT);
}

function psnDaftarStatusPesanan(): array
{
    return ['Belum Dikonfirmasi', 'Dikonfirmasi', 'Diproses', 'Dikirim', 'Selesai'];
}

function psnDaftarStatusBayar(): array
{
    return ['Belum Bayar', 'DP', 'Lunas'];
}

// Pilihan status di form tambah/ubah pesanan. "Belum Dikonfirmasi" dan "Belum Bayar"
// sengaja tidak ada: itu status awal pesanan dari pembeli, admin tinggal memilih
// status berikutnya. Daftar lengkap di atas tetap dipakai untuk filter dan tampilan.
function psnStatusPesananForm(): array
{
    return ['Dikonfirmasi', 'Diproses', 'Dikirim', 'Selesai'];
}

function psnStatusBayarForm(): array
{
    return ['DP', 'Lunas'];
}

function psnKelasBayar(string $statusBayar): string
{
    if ($statusBayar === 'Lunas') {
        return 'status-pill--paid';
    }
    if ($statusBayar === 'DP') {
        return 'status-pill--dp';
    }
    return '';
}

function psnTanggalIndo(?string $tanggal): string
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

// Satu baris menu (dropdown + jumlah). Markup sama dengan form tambah/edit pesanan
// yang sudah ada, jadi assets/js/pesanan.js dan CSS lama langsung bisa dipakai.
function psnBarisMenu(array $daftarMenu, $idDipilih = '', $jumlah = 1): string
{
    $opsi = '<option value="">Pilih Menu</option>';
    foreach ($daftarMenu as $menu) {
        $label = $menu['nama_menu'] . ' — ' . psnRupiah($menu['harga_menu']);
        if (!filter_var($menu['is_tersedia'], FILTER_VALIDATE_BOOLEAN)) {
            $label .= ' (nonaktif)';
        }
        $dipilih = ((string) $idDipilih === (string) $menu['id_menu']) ? ' selected' : '';
        $opsi .= '<option value="' . (int) $menu['id_menu'] . '"' . $dipilih . '>' . psnE($label) . '</option>';
    }

    $jumlah = is_numeric($jumlah) ? max(1, (int) $jumlah) : 1;

    return '<div class="order-item-row">'
        . '<label class="order-menu-field">Menu'
        . '<select name="menu_id[]" required>' . $opsi . '</select>'
        . '</label>'
        . '<div class="order-qty-field"><span>Jumlah (box)</span>'
        . '<div class="qty-control">'
        . '<button type="button" class="qty-button" data-qty-change="-1">−</button>'
        . '<input type="number" name="jumlah[]" min="1" value="' . $jumlah . '" required>'
        . '<button type="button" class="qty-button" data-qty-change="1">+</button>'
        . '</div></div>'
        . '<button type="button" class="remove-order-item">Hapus</button>'
        . '</div>';
}

/**
 * Validasi input form tambah/ubah pesanan.
 *
 * @param array       $in           biasanya $_POST
 * @param array       $daftarMenu   hasil psnMenuUntukForm()
 * @param string      $tanggalMin   tanggal kirim paling awal (Y-m-d)
 * @param bool        $periksaNomor false untuk halaman ubah (nomor HP tidak diubah)
 * @return array ['data' => nilai form (untuk isi ulang), 'error' => string,
 *                'id_menu' => int[], 'jumlah' => int[], 'ongkir_num' => float, 'dp_num' => float]
 */
function psnValidasiForm(array $in, array $daftarMenu, string $tanggalMin, bool $periksaNomor = true): array
{
    $errors = [];
    $teks = function ($kunci) use ($in) {
        return isset($in[$kunci]) && is_scalar($in[$kunci]) ? trim((string) $in[$kunci]) : '';
    };

    $data = [
        'nomor'             => preg_replace('/\D+/', '', $teks('nomor')),
        'nama'              => $teks('nama'),
        'alamat'            => $teks('alamat'),
        'status_pesanan'    => $teks('status_pesanan'),
        'status_pembayaran' => $teks('status_pembayaran'),
        'tanggal'           => $teks('tanggal'),
        'jam'               => $teks('jam'),
        'ongkir'            => $teks('ongkir'),
        'jumlah_dp'         => $teks('jumlah_dp'),
        'catatan'           => $teks('catatan'),
        'baris'             => [],
    ];

    // harga tiap menu dari database, dipakai untuk cek DP
    $hargaMenu = [];
    foreach ($daftarMenu as $menu) {
        $hargaMenu[(int) $menu['id_menu']] = (float) $menu['harga_menu'];
    }

    $idPost     = isset($in['menu_id']) && is_array($in['menu_id']) ? $in['menu_id'] : [];
    $jumlahPost = isset($in['jumlah']) && is_array($in['jumlah']) ? $in['jumlah'] : [];

    // Satu menu hanya boleh satu baris di detail_pesanan (primary key), jadi baris
    // dengan menu yang sama digabung dan jumlahnya dijumlahkan.
    $gabung = [];
    foreach ($idPost as $i => $idMentah) {
        $idMentah     = is_scalar($idMentah) ? (string) $idMentah : '';
        $jumlahMentah = isset($jumlahPost[$i]) && is_scalar($jumlahPost[$i]) ? (string) $jumlahPost[$i] : '1';
        $data['baris'][] = ['id' => $idMentah, 'jumlah' => $jumlahMentah];

        $id  = filter_var($idMentah, FILTER_VALIDATE_INT);
        $qty = filter_var($jumlahMentah, FILTER_VALIDATE_INT);

        if ($id === false || !isset($hargaMenu[$id])) {
            $errors[] = 'Pilih menu pada setiap baris menu.';
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

    if ($periksaNomor && !preg_match('/^[0-9]{9,15}$/', $data['nomor'])) {
        $errors[] = 'Nomor HP harus berupa angka 9 sampai 15 digit.';
    }

    $panjangNama = mb_strlen($data['nama']);
    if ($panjangNama < 1 || $panjangNama > 50) {
        $errors[] = 'Nama pembeli wajib diisi (maksimal 50 karakter).';
    }

    $panjangAlamat = mb_strlen($data['alamat']);
    if ($panjangAlamat < 1 || $panjangAlamat > 255) {
        $errors[] = 'Alamat pengiriman wajib diisi (maksimal 255 karakter).';
    }

    if (mb_strlen($data['catatan']) > 255) {
        $errors[] = 'Catatan maksimal 255 karakter.';
    }

    if (!in_array($data['status_pesanan'], psnStatusPesananForm(), true)) {
        $errors[] = 'Pilih status pesanan.';
    }
    if (!in_array($data['status_pembayaran'], psnStatusBayarForm(), true)) {
        $errors[] = 'Pilih status pembayaran.';
    }

    $tanggal = DateTime::createFromFormat('Y-m-d', $data['tanggal']);
    if (!$tanggal || $tanggal->format('Y-m-d') !== $data['tanggal']) {
        $errors[] = 'Tanggal kirim tidak valid.';
    } elseif ($data['tanggal'] < $tanggalMin) {
        $errors[] = 'Tanggal kirim tidak boleh sebelum ' . psnTanggalIndo($tanggalMin) . '.';
    }

    if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $data['jam'])) {
        $errors[] = 'Jam kirim tidak valid.';
    }

    $ongkir = 0.0;
    if ($data['ongkir'] !== '') {
        if (!is_numeric($data['ongkir']) || (float) $data['ongkir'] < 0 || (float) $data['ongkir'] > 1000000000) {
            $errors[] = 'Ongkir harus berupa angka 0 atau lebih.';
        } else {
            $ongkir = (float) $data['ongkir'];
        }
    }

    // DP hanya dipakai kalau status pembayarannya DP, selain itu disimpan 0
    $dp = 0.0;
    if ($data['status_pembayaran'] === 'DP') {
        if (!is_numeric($data['jumlah_dp']) || (float) $data['jumlah_dp'] <= 0 || (float) $data['jumlah_dp'] > 1000000000) {
            $errors[] = 'Jumlah DP wajib diisi dan harus lebih dari 0.';
        } else {
            $dp = (float) $data['jumlah_dp'];
            $subtotal = 0.0;
            foreach ($gabung as $id => $qty) {
                $subtotal += $hargaMenu[$id] * $qty;
            }
            if ($dp > $subtotal + $ongkir) {
                $errors[] = 'Jumlah DP tidak boleh melebihi total pesanan (' . psnRupiah($subtotal + $ongkir) . ').';
            }
        }
    }

    return [
        'data'       => $data,
        'error'      => $errors[0] ?? '',
        'id_menu'    => array_keys($gabung),
        'jumlah'     => array_values($gabung),
        'ongkir_num' => $ongkir,
        'dp_num'     => $dp,
    ];
}
