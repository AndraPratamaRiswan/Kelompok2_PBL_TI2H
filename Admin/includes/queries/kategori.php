<?php
// Return: ['id' => int|null, 'nama' => string, 'error' => string]
function tambahKategoriBaru(PDO $pdo, string $nama): array
{
    $nama = trim(preg_replace('/\s+/', ' ', $nama));

    if ($nama === '') {
        return ['id' => null, 'nama' => '', 'error' => 'Nama kategori wajib diisi.'];
    }
    if (mb_strlen($nama) > 50) {
        return ['id' => null, 'nama' => $nama, 'error' => 'Nama kategori maksimal 50 karakter.'];
    }

    // cek tanpa membedakan huruf besar/kecil ("makanan jepang" dianggap sama dengan "Makanan Jepang")
    $cek = $pdo->prepare('SELECT 1 FROM kategori WHERE LOWER(nama_kategori) = LOWER(:nama)');
    $cek->execute([':nama' => $nama]);
    if ($cek->fetchColumn()) {
        return ['id' => null, 'nama' => $nama, 'error' => 'Kategori "' . $nama . '" sudah ada.'];
    }

    $stmt = $pdo->prepare('INSERT INTO kategori (nama_kategori) VALUES (:nama) RETURNING id_kategori');
    $stmt->execute([':nama' => $nama]);

    return ['id' => (int) $stmt->fetchColumn(), 'nama' => $nama, 'error' => ''];
}