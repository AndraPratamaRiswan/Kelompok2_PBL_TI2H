<?php
// Logout Admin: kosongkan sesi, hapus cookie sesi, kembali ke halaman login.
define('ADMIN_HALAMAN_PUBLIK', true);
require_once __DIR__ . '/includes/auth.php';

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

header('Location: login.php');
exit;
