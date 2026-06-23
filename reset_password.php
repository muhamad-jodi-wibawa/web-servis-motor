<?php
require_once 'config/koneksi.php';

$pesan = "";
$tipe_pesan = "";
$valid_token = false;

// 1. Validasi Token dari URL
if (isset($_GET['token'])) {
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    
    // Cek apakah token ada dan belum expired (berlaku 1 jam)
    $query = mysqli_query($conn, "SELECT * FROM users WHERE reset_token = '$token' AND token_expiry > NOW()");
    $user = mysqli_fetch_assoc($query);

    if ($user) {
        $valid_token = true;
    } else {
        $pesan = "Token tidak valid atau sudah kadaluarsa!";
        $tipe_pesan = "danger";
    }
}

// 2. Proses Update Password
if (isset($_POST['update_password'])) {
    $password_baru = $_POST['password_baru'];
    $konfirmasi = $_POST['konfirmasi_password'];
    $id_user = $user['id_user'];

    if ($password_baru !== $konfirmasi) {
        $pesan = "Konfirmasi password tidak cocok!";
        $tipe_pesan = "warning";
    } else {
        // Hashing password baru
        $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
        
        // Update database: ganti password dan hapus token agar tidak bisa dipakai lagi
        $update = mysqli_query($conn, "UPDATE users SET 
                                      password = '$password_hash', 
                                      reset_token = NULL, 
                                      token_expiry = NULL 
                                      WHERE id_user = '$id_user'");
        
        if ($update) {
            echo "<script>alert('Password berhasil diperbarui!'); window.location='login.php';</script>";
            exit();
        } else {
            $pesan = "Gagal memperbarui database!";
            $tipe_pesan = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Setel Ulang Password - ServisKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8)), 
                        url('https://images.unsplash.com/photo-1486262715619-67b85e0b08d3?q=80&w=2000');
            background-size: cover;
            height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 25px;
            padding: 40px;
            color: white;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.5);
        }
        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            border-radius: 10px;
        }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border-color: #2ecc71;
            box-shadow: none;
        }
        .btn-update {
            background: linear-gradient(45deg, #2ecc71, #27ae60);
            border: none;
            border-radius: 10px;
            font-weight: bold;
            transition: 0.3s;
        }
        .btn-update:hover {
            transform: scale(1.02);
            filter: brightness(1.1);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="glass-card text-center">
                <i class="bi bi-key-fill text-success" style="font-size: 4rem;"></i>
                <h2 class="fw-bold mt-3">Reset Password</h2>
                
                <?php if ($valid_token): ?>
                    <p class="text-white-50 mb-4">Halo <strong><?= $user['nama'] ?></strong>, masukkan password baru Anda.</p>

                    <?php if($pesan): ?>
                        <div class="alert alert-<?= $tipe_pesan ?> py-2 small"><?= $pesan ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3 text-start">
                            <label class="form-label small ms-1">Password Baru</label>
                            <input type="password" name="password_baru" class="form-control" placeholder="Minimal 5 karakter" required minlength="5">
                        </div>
                        <div class="mb-4 text-start">
                            <label class="form-label small ms-1">Konfirmasi Password</label>
                            <input type="password" name="konfirmasi_password" class="form-control" placeholder="Ulangi password" required>
                        </div>
                        
                        <button type="submit" name="update_password" class="btn btn-update btn-lg w-100 text-white shadow">
                            SIMPAN PASSWORD BARU
                        </button>
                    </form>

                <?php else: ?>
                    <div class="alert alert-danger mt-3"><?= $pesan ?></div>
                    <a href="lupa_password.php" class="btn btn-outline-light w-100 mt-2 rounded-3">Minta Token Baru</a>
                <?php endif; ?>

                <div class="mt-4">
                    <a href="login.php" class="text-white-50 text-decoration-none small">Kembali ke Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>