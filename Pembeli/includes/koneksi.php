<!-- CATATAN, PER ANAK DATABASENYA BEDA, ISI SENDIRI YAH <3 -->
<?php
$host = "localhost";
$port = "5432";
$db   = "sikat";
$user = "postgres";
$pass = "Halonamakuyayogg123_";

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}