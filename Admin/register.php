<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Admin - SIKAT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #e2e8f0; }
        .card-register { max-width: 750px; border-radius: 12px; overflow: hidden; border: none; }
        .sidebar-navy { background-color: #1e293b; color: white; padding: 40px 30px; }
        .btn-gray { background-color: #64748b; color: white; font-weight: 600; }
        .btn-gray:hover { background-color: #475569; color: white; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 p-3">

    <div class="card card-register shadow w-100 bg-white">
        <div class="row g-0">
            <!-- Left Banner Navy -->
            <div class="col-md-5 sidebar-navy d-flex flex-column justify-content-center text-center">
                <h3 class="fw-bold mb-3">Selamat Datang!</h3>
                <p class="small text-white-50 m-0">Sistem Informasi Katering (SIKAT) Panel Akses Pengelola.</p>
            </div>

            <!-- Right Form Section -->
            <div class="col-md-7 p-4 p-md-5">
                <div class="text-center mb-4">
                    <h4 class="fw-bold mb-1">Daftar Akun Anda</h4>
                    <small class="text-muted">Silahkan daftar menggunakan data diri anda</small>
                </div>

                <form action="login.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Nomor Handphone</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-phone"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0" name="nomor_hp" placeholder="Masukkan Nomor Handphone" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small text-muted mb-1">Kata Sandi</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control border-start-0 ps-0" name="password" placeholder="Masukkan Kata Sandi" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-gray w-100 py-2 mb-3">Daftar</button>
                </form>

                <hr class="my-3 text-muted">

                <div class="text-center small">
                    <span class="text-muted">Sudah memiliki akun?</span> 
                    <a href="login.php" class="text-decoration-none fw-semibold text-dark">Masuk Sekarang</a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>