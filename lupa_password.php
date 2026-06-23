<?php
require_once 'config/koneksi.php';

$pesan = "";
$tipe_pesan = "";

if (isset($_POST['cek_akun'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    
    $query = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    if (mysqli_num_rows($query) > 0) {
        $token = bin2hex(random_bytes(16));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        mysqli_query($conn, "UPDATE users SET reset_token = '$token', token_expiry = '$expiry' WHERE username = '$username'");
        
        // Simulasi pengiriman link
        header("Location: reset_password.php?token=$token");
        exit();
    } else {
        $pesan = "Username tidak ditemukan dalam sistem!";
        $tipe_pesan = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pulihkan Akun - ServisKu</title>
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
            border-color: #ff4d4d;
            box-shadow: none;
        }
        .btn-reset {
            background: linear-gradient(45deg, #ff4d4d, #b30000);
            border: none;
            border-radius: 10px;
            font-weight: bold;
            transition: 0.3s;
        }
        .btn-reset:hover {
            transform: scale(1.02);
            background: linear-gradient(45deg, #b30000, #ff4d4d);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="glass-card text-center">
                <i class="bi bi-shield-lock-fill text-danger" style="font-size: 4rem;"></i>
                <h2 class="fw-bold mt-3">Lupa Password?</h2>
                <p class="text-white-50 mb-4">Masukkan username Anda untuk mengatur ulang kata sandi.</p>

                <?php if($pesan): ?>
                    <div class="alert alert-<?= $tipe_pesan ?> bg-danger text-white border-0 small py-2">
                        <?= $pesan ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-4 text-start">
                        <label class="form-label small ms-1">Username Akun</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 text-white-50">
                                <i class="bi bi-person-circle"></i>
                            </span>
                            <input type="text" name="username" class="form-control border-start-0" placeholder="Contoh: sigit_dev" required>
                        </div>
                    </div>
                    
                    <button type="submit" name="cek_akun" class="btn btn-reset btn-lg w-100 text-white shadow">
                        PROSES PEMULIHAN
                    </button>
                    
                    <div class="mt-4">
                        <a href="login.php" class="text-white-50 text-decoration-none small">
                            <i class="bi bi-arrow-left"></i> Kembali ke Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>