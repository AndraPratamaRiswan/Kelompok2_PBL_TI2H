<?php
// Daftar akun pembeli. Keputusannya diambil oleh fn_daftar_pembeli di database:
//   'dibuat'          nomor belum ada            -> akun baru dibuat
//   'diklaim'         nomor sudah pernah memesan lewat admin (akun belum punya
//                     kata sandi) -> kata sandi dipasang, riwayat pesanan tetap
//   'sudah_terdaftar' nomor sudah punya akun aktif -> ditolak
//
// Mengembalikan true kalau berhasil (dibuat atau diklaim), false kalau nomor
// sudah punya akun aktif. Jadi register.php tidak perlu diubah.
// Kata sandi di-hash di PHP; yang dikirim ke database hanya hash-nya.
function registerAkun(PDO $pdo, string $no_hp, string $password): bool
{
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare('SELECT fn_daftar_pembeli(:no_hp, :hash)');
    $stmt->execute([
        ':no_hp' => $no_hp,
        ':hash'  => $password_hash,
    ]);

    return in_array($stmt->fetchColumn(), ['dibuat', 'diklaim'], true);
}
