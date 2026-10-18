<?php
// fitur 1, nilai default judul dan navigasi
$pageTitle = $pageTitle ?? 'Panel Admin';
$activeNav = $activeNav ?? '';
?>

<!-- fitur 1, html dasar dan stylesheet -->
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1f2f43">
    <title><?= htmlspecialchars($pageTitle) ?> | SIKAT Admin</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-frame">
    <!-- fitur 1, navigasi utama -->
    <header class="topbar">
            <a class="brand" href="index.php" aria-label="SIKAT Panel Admin">
                <span class="brand__name">SIKAT</span>
                <span class="brand__subtitle">Panel Admin</span>
            </a>

            <nav class="topnav" aria-label="Navigasi utama">
                <a class="topnav__link <?= $activeNav === 'dashboard' ? 'is-active' : '' ?>" href="index.php">Dashboard</a>
                <a class="topnav__link" href="#" data-toast="Halaman Kelola Menu akan dibuat pada file terpisah.">Katalog Menu</a>
                <!-- fitur 6, pop up profil admin (demo) -->
                <div class="profile-wrap">
                <button class="profile-button" id="profileButton" type="button" aria-label="Buka menu akun" aria-expanded="false">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c.5-3.6 3.1-5.5 7-5.5s6.5 1.9 7 5.5"/></svg>
                </button>
                <div class="profile-menu" id="profileMenu" hidden>
                    <strong>Admin SIKAT</strong>
                    <span>Panel pengelolaan</span>
                    <button type="button" data-toast="Fitur akun belum diaktifkan.">Pengaturan akun</button>
                </div>
            </div>
            </nav>
        </header>
    <!-- fitur 1, pembuka area konten -->
    <main class="main-content">