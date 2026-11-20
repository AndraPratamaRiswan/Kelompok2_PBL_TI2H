<?php
// Sesi dan penjagaan login Admin.
//
// File ini dipanggil dari includes/koneksi.php, jadi SEMUA halaman Admin yang
// memakai koneksi otomatis wajib login (default: tertutup). Halaman yang boleh
// dibuka tanpa login (hanya login.php dan logout.php) menandai dirinya sendiri
// SEBELUM me-require koneksi.php atau auth.php:
//
//     define('ADMIN_HALAMAN_PUBLIK', true);

if (session_status() === PHP_SESSION_NONE) {
    // Nama sesi dibedakan dari sesi Pembeli (PHPSESSID) supaya login Admin dan
    // login Pembeli di browser yang sama tidak saling menimpa atau saling logout.
    session_name('SIKAT_ADMIN');
    session_set_cookie_params([
        'lifetime' => 0,        // hilang saat browser ditutup
        'path'     => '/',
        'httponly' => true,     // tidak bisa dibaca JavaScript
        'samesite' => 'Lax',    // cookie tidak ikut terkirim pada POST dari situs lain
    ]);
    session_start();
}

function adminSudahLogin(): bool
{
    return !empty($_SESSION['id_admin']);
}

// id_admin yang sedang login. Dipakai untuk kolom id_admin di menu dan pesanan.
function adminId(): int
{
    return (int) ($_SESSION['id_admin'] ?? 0);
}

if (!defined('ADMIN_HALAMAN_PUBLIK') && !adminSudahLogin()) {
    header('Location: login.php');
    exit;
}
