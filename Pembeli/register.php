<?php
session_start();
require_once 'includes/koneksi.php';
require_once 'includes/queries/register.php';

// Kalau sudah login, tidak perlu daftar lagi
if (isset($_SESSION['id_pembeli'])) {
    header('Location: index.php');
    exit();
}

$error = '';
$no_hp = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // name di form HTML = "no_hp" 
    $no_hp    = preg_replace('/[\s\-]/', '', trim($_POST['no_hp'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($no_hp === '' || $password === '') {
        $error = 'Semua kolom wajib diisi!';
    } elseif (!preg_match('/^[0-9]{9,15}$/', $no_hp)) {
        // sama dengan CHECK constraint di tabel pembeli
        $error = 'Nomor HP harus berupa angka 9-15 digit.';
    } elseif (strlen($password) < 6) {
        $error = 'Kata sandi minimal 6 karakter.';
    } else {
        try {
            if (registerAkun($pdo, $no_hp, $password)) {
                // Berhasil daftar -> pindah ke halaman login
                header('Location: login.php?status=registered');
                exit();
            }
            $error = 'Nomor HP sudah terdaftar!';
        } catch (PDOException $e) {
            // Detail error asli masuk ke log PHP (bukan ke layar pengguna)
            error_log('Register gagal: ' . $e->getMessage());
            $error = 'Gagal mendaftar. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pembeli - SIKAT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f1f5f9; }
        .card-register { max-width: 750px; border-radius: 12px; overflow: hidden; border: none; }
        .sidebar-maroon { background-color: #800000; color: white; padding: 40px 30px; }
        
        /* Warna Merah Maroon untuk Tombol & Hover */
        .btn-maroon { background-color: #800000; color: white; font-weight: 600; }
        .btn-maroon:hover { background-color: #600000; color: white; }
        
        /* Warna Maroon untuk Teks Link */
        .text-maroon { color: #800000; }
        .text-maroon:hover { color: #600000; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 p-3">

    <div class="card card-register shadow w-100 bg-white">
        <div class="row g-0">
            <!-- Left Banner Maroon -->
            <div class="col-md-5 sidebar-maroon d-flex flex-column justify-content-center text-center">
                <h3 class="fw-bold mb-3">Selamat Datang!</h3>
                <p class="small text-white-50 m-0">Masuk untuk melanjutkan transaksi anda.</p>
            </div>

            <!-- Right Form Section -->
            <div class="col-md-7 p-4 p-md-5">
                <div class="text-center mb-4">
                    <h4 class="fw-bold mb-1">Daftar Akun Anda</h4>
                    <small class="text-muted">Silahkan daftar dengan data diri anda</small>
                </div>

                <?php if ($error !== '') : ?>
                    <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form action="register.php" method="POST">

    <!-- Input Nomor HP -->
    <div class="mb-3">
        <label class="form-label small text-muted mb-1">Nomor Handphone</label>
        <div class="input-group">
            <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-phone"></i></span>
            <input type="text" class="form-control border-start-0 ps-0" name="no_hp" value="<?= htmlspecialchars($no_hp); ?>" placeholder="Masukkan Nomor Handphone" required>
        </div>
    </div>

    <!-- Input Kata Sandi -->
    <div class="mb-4">
        <label class="form-label small text-muted mb-1">Kata Sandi</label>
        <div class="input-group">
            <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control border-start-0 ps-0" name="password" placeholder="Masukkan Kata Sandi" required>
        </div>
    </div>

                    <!-- Tombol Merah Maroon -->
                    <button type="submit" class="btn btn-maroon w-100 py-2 mb-3">Daftar</button>
                </form>

                <hr class="my-3 text-muted">

                <div class="text-center small">
                    <span class="text-muted">Sudah memiliki akun?</span> 
                    <a href="login.php" class="text-decoration-none fw-semibold text-maroon">Masuk Sekarang</a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>