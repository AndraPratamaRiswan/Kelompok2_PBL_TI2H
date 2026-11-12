<?php
// Keluar: hapus semua data session (termasuk isi keranjang), lalu kembali ke halaman menu.
session_start();
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $c = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $c['path'], $c['domain'], $c['secure'], $c['httponly']);
}
session_destroy();
header('Location: index.php');
exit;
