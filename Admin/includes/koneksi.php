<?php
$host = 'localhost';
$port = '5432';
$db   = 'sikat_db';
$user = 'postgres';
$pass = 'postgres';

try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$db",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Koneksi PostgreSQL Admin gagal. Periksa pengaturan di Admin/includes/koneksi.php. Detail: ' . htmlspecialchars($e->getMessage()));
}