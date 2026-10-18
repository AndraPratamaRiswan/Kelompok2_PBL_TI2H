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
            </nav>
        </header>
    <!-- fitur 1, pembuka area konten -->
    <main class="main-content">