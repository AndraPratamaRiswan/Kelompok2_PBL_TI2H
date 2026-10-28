<!-- CATATAN, PER ANAK DATABASENYA BEDA, ISI SENDIRI YAH <3 -->
<?php
$host = "localhost";
$port = "5432";
$db   = "sikat_db";
$user = "postgres";
$pass = "postgres";

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}