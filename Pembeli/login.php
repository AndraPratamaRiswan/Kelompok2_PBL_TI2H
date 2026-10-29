<?php
session_start();
require_once 'includes/koneksi.php';
require_once 'includes/queries/login.php';

// Kalau sudah login, langsung ke halaman utama
if (isset($_SESSION['id_pembeli'])) {
    header('Location: index.php');
    exit();
}

$error = '';
$no_hp = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $no_hp    = preg_replace('/[\s\-]/', '', trim($_POST['no_hp'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($no_hp === '' || $password === '') {
        $error = 'Semua kolom wajib diisi!';
    } else {
        try {
            // Mengembalikan data pembeli jika cocok, atau false jika tidak
            $pembeli = loginAkun($pdo, $no_hp, $password);

            if ($pembeli) {
                // Login sah -> simpan penanda login di session
                session_regenerate_id(true);
                $_SESSION['id_pembeli'] = $pembeli['id_pembeli'];
                $_SESSION['no_hp']      = $pembeli['no_hp'];

                header('Location: index.php');
                exit();
            }

            // Pesan sengaja sama untuk "nomor tidak ada" dan "sandi salah"
            $error = 'Nomor HP atau kata sandi salah, atau akun belum terdaftar.';
        } catch (PDOException $e) {
            error_log('Login gagal: ' . $e->getMessage());
            $error = 'Terjadi kesalahan sistem. Coba lagi nanti.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Pembeli - SIKAT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f1f5f9; }
        .card-auth { max-width: 420px; border-radius: 12px; overflow: hidden; border: none; }
        .header-maroon { background-color: #800000; color: white; padding: 24px 20px; }
        
        /* Warna Merah Maroon untuk Tombol & Hover */
        .btn-maroon { background-color: #800000; color: white; font-weight: 600; }
        .btn-maroon:hover { background-color: #600000; color: white; }
        
        /* Warna Maroon untuk teks Link */
        .text-maroon { color: #800000; }
        .text-maroon:hover { color: #600000; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 p-3">

    <div class="card card-auth shadow w-100 bg-white">
        <!-- Header Maroon -->
        <div class="header-maroon text-center">
            <h4 class="mb-1 fw-bold">Masuk ke Akun Anda</h4>
            <small class="text-white-50 fs-7">Silahkan masuk menggunakan akun yang telah terdaftar</small>
        </div>

        <!-- Form Body -->
        <div class="p-4">
            <?php if (($_GET['status'] ?? '') === 'registered') : ?>
                <div class="alert alert-success py-2 small">Pendaftaran berhasil! Silakan masuk.</div>
            <?php endif; ?>
            <?php if ($error !== '') : ?>
                <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label class="form-label small text-muted mb-1">Nomor Handphone</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-phone"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" name="no_hp" value="<?= htmlspecialchars($no_hp); ?>" placeholder="Masukkan Nomor Handphone" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small text-muted mb-1">Kata Sandi</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control border-start-0 ps-0" name="password" placeholder="Masukkan Kata Sandi" required>
                    </div>
                </div>

                <!-- Tombol Merah Masuk  -->
                <button type="submit" class="btn btn-maroon w-100 py-2 mb-3">Masuk</button>
            </form>

            <hr class="my-3 text-muted">

            <div class="text-center small">
                <span class="text-muted">Belum memiliki akun?</span> 
                <a href="register.php" class="text-decoration-none fw-semibold text-maroon">Daftar Sekarang</a>
            </div>
        </div>
    </div>

</body>
</html>