<?php
// Judul dan menu aktif bisa diganti dari halaman yang memanggil header ini.
$pageTitle = $pageTitle ?? 'Dashboard';
$activeNav = $activeNav ?? 'dashboard';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?> | SIKAT Admin</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<!-- Navbar -->
<header class="topbar">
    <a class="brand" href="index.php">
        <span class="brand__name">SIKAT</span>
        <span class="brand__subtitle">Panel Admin</span>
    </a>

    <nav class="topnav" aria-label="Navigasi utama">
        <a class="topnav__link <?= $activeNav === 'dashboard' ? 'is-active' : '' ?>" href="index.php">Dashboard</a>
        <a class="topnav__link <?= $activeNav === 'katalog' ? 'is-active' : '' ?>" href="katalog.php">Katalog Menu</a>
        <!-- Ikon profil -->
        <span class="profile-icon" aria-label="Profil admin">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="8" r="3.5"></circle>
                <path d="M5 20c.5-3.6 3.1-5.5 7-5.5s6.5 1.9 7 5.5"></path>
            </svg>
        </span>
    </nav>
</header>

<main class="main-content">
