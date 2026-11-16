<?php
// Upload gambar menu. File disimpan di Admin/assets/img/upload/ dan yang dicatat di
// kolom menu.gambar_url hanya 'upload/<nama file>'. Fungsi di sini dipakai oleh
// tambah_menu.php, edit_menu.php, dan penampil gambar (katalog admin + halaman pembeli).

const GAMBAR_UPLOAD_FOLDER   = 'upload';
const GAMBAR_UPLOAD_MAKS     = 2 * 1024 * 1024; // 2 MB
const GAMBAR_UPLOAD_TIPE     = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
];

function gambarUploadDirFisik(): string
{
    return __DIR__ . '/../assets/img/' . GAMBAR_UPLOAD_FOLDER;
}

// Kalau $gambarUrl adalah file hasil upload, kembalikan nama filenya saja (tanpa folder).
// Selain itu null. Pola dikunci ketat supaya tidak bisa dipakai untuk membaca file lain.
function namaFileGambarUpload(?string $gambarUrl): ?string
{
    if ($gambarUrl !== null && preg_match('#(?:^|/)' . GAMBAR_UPLOAD_FOLDER . '/(menu_[0-9]{14}_[a-f0-9]{8}\.(?:jpg|png|webp))$#', $gambarUrl, $m)) {
        return $m[1];
    }
    return null;
}

// Hapus file upload lama (aman dipanggil untuk gambar bawaan: tidak melakukan apa-apa).
function hapusGambarUpload(?string $gambarUrl): void
{
    $nama = namaFileGambarUpload($gambarUrl);
    if ($nama !== null) {
        $path = gambarUploadDirFisik() . '/' . $nama;
        if (is_file($path)) {
            @unlink($path);
        }
    }
}

// True kalau request POST melebihi post_max_size: PHP lalu mengosongkan $_POST dan $_FILES.
function uploadMelebihiBatasServer(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST'
        && empty($_POST) && empty($_FILES)
        && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0;
}

// Proses file dari <input type="file" name="..."> .
// Hasil: ['path' => 'upload/xxx.jpg' | null, 'error' => '' | pesan]
//  - tidak ada file dipilih  -> path null, error kosong (bukan kesalahan)
//  - file tidak valid        -> path null, error berisi pesan
function simpanGambarMenuUpload(?array $file): array
{
    if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['path' => null, 'error' => ''];
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ['path' => null, 'error' => 'Ukuran gambar maksimal 2 MB.'];
        default:
            return ['path' => null, 'error' => 'Gambar gagal diunggah. Coba pilih file lain.'];
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        return ['path' => null, 'error' => 'Gambar gagal diunggah. Coba pilih file lain.'];
    }
    if ($file['size'] > GAMBAR_UPLOAD_MAKS) {
        return ['path' => null, 'error' => 'Ukuran gambar maksimal 2 MB.'];
    }

    // jenis file dicek dari isi file (bukan dari nama/ekstensi yang dikirim browser)
    $info = @getimagesize($file['tmp_name']);
    $mime = $info['mime'] ?? '';
    if (!isset(GAMBAR_UPLOAD_TIPE[$mime])) {
        return ['path' => null, 'error' => 'Format gambar harus JPG, PNG, atau WebP.'];
    }

    $dir = gambarUploadDirFisik();
    if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
        return ['path' => null, 'error' => 'Folder upload tidak bisa dibuat. Periksa izin folder Admin/assets/img.'];
    }

    $nama = 'menu_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . GAMBAR_UPLOAD_TIPE[$mime];
    if (!@move_uploaded_file($file['tmp_name'], $dir . '/' . $nama)) {
        return ['path' => null, 'error' => 'Gambar gagal disimpan. Periksa izin folder Admin/assets/img/upload.'];
    }

    return ['path' => GAMBAR_UPLOAD_FOLDER . '/' . $nama, 'error' => ''];
}
