<?php
require_once __DIR__ . '/includes/koneksi.php';   // otomatis wajib login
require_once __DIR__ . '/includes/queries/kategori.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Metode tidak diizinkan.']);
    exit;
}

try {
    $hasil = tambahKategoriBaru($pdo, (string) ($_POST['nama_kategori'] ?? ''));
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Kategori belum berhasil disimpan.']);
    exit;
}

if ($hasil['error'] !== '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => $hasil['error']]);
    exit;
}

echo json_encode(['ok' => true, 'id' => $hasil['id'], 'nama' => $hasil['nama']]);