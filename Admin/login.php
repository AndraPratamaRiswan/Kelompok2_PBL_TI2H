<?php
// Login Admin. Akun admin TIDAK didaftarkan lewat web: superadmin menambahkannya
// langsung ke tabel admin di database (lihat langkah2b_admin_dan_klaim_akun.sql).
define('ADMIN_HALAMAN_PUBLIK', true); // halaman ini boleh dibuka tanpa login
require_once __DIR__ . '/includes/koneksi.php';
require_once __DIR__ . '/includes/queries/login.php'; // loginAkun()


$error    = '';
$username = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan kata sandi wajib diisi.';
    } else {
        try {
            $admin = loginAkun($pdo, $username, $password); // data admin, atau false
            if ($admin) {
                session_regenerate_id(true); // cegah session fixation
                $_SESSION['id_admin']   = (int) $admin['id_admin'];
                $_SESSION['nama_admin'] = $admin['nama_lengkap'];
                header('Location: index.php');
                exit;
            }
            $error = 'Username atau kata sandi salah.';
        } catch (PDOException $e) {
            error_log('Login admin gagal: ' . $e->getMessage());
            $error = 'Terjadi kesalahan. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Admin - SIKAT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #e2e8f0; }
        .card-auth { max-width: 420px; border-radius: 12px; overflow: hidden; border: none; }
        .header-navy { background-color: #1e293b; color: white; padding: 24px 20px; }
        .btn-gray { background-color: #64748b; color: white; font-weight: 600; }
        .btn-gray:hover { background-color: #475569; color: white; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 p-3">

    <div class="card card-auth shadow w-100 bg-white">
        <!-- Header Navy -->
        <div class="header-navy text-center">
            <h4 class="mb-1 fw-bold">Masuk ke Akun Anda</h4>
            <small class="text-white-50 fs-7">Silahkan masuk menggunakan akun yang telah terdaftar</small>
        </div>

        <!-- Form Body -->
        <div class="p-4">
            <?php if ($error !== ''): ?>
                <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label class="form-label small text-muted mb-1">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" name="username" value="<?= htmlspecialchars($username) ?>" placeholder="Masukkan Username" autocomplete="username" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small text-muted mb-1">Kata Sandi</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control border-start-0 ps-0" name="password" placeholder="Masukkan Kata Sandi" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-gray w-100 py-2 mb-3">Masuk</button>
            </form>
        </div>
    </div>

</body>
</html>