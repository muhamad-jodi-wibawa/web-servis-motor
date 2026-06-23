<?php
require_once 'config/koneksi.php';

if (isset($_POST['register'])) {
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password']; // Sebaiknya gunakan password_hash untuk keamanan produksi
    $role     = 'user'; // Default pendaftar adalah user

    // Cek apakah username sudah ada
    $cek_user = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($cek_user) > 0) {
        echo "<script>alert('Username sudah digunakan, cari yang lain!');</script>";
    } else {
        $query = "INSERT INTO users (nama, username, password, role) VALUES ('$nama', '$username', '$password', '$role')";
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Registrasi Berhasil! Silahkan Login.'); window.location='login.php';</script>";
        } else {
            echo "<script>alert('Gagal Registrasi!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Pelanggan - ServisKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            /* Background: Nuansa bengkel/motor yang berbeda dengan login agar tidak membosankan */
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.8)), 
                        url('https://images.unsplash.com/photo-1558981285-6f0c94958bb6?q=80&w=2000&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }

        .reg-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            width: 100%;
            max-width: 450px;
            padding: 35px;
            color: white;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
        }

        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            border-radius: 12px;
            padding: 10px 15px;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-color: #dc3545;
            box-shadow: none;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .btn-reg {
            background: #dc3545;
            border: none;
            padding: 12px;
            border-radius: 12px;
            font-weight: bold;
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-reg:hover {
            background: #a71d2a;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        }

        .logo-icon {
            font-size: 2.5rem;
            color: #dc3545;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="reg-card">
    <div class="text-center mb-4">
        <div class="logo-icon">
            <i class="bi bi-person-plus-fill"></i>
        </div>
        <h4 class="fw-bold mb-0">Join ServisKu</h4>
        <p class="small text-white-50">Daftarkan akun untuk monitoring motor Anda</p>
    </div>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label small opacity-75">Nama Lengkap</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0 border-white border-opacity-25 text-white-50">
                    <i class="bi bi-person"></i>
                </span>
                <input type="text" name="nama" class="form-control border-start-0" placeholder="Masukkan nama" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small opacity-75">Username</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0 border-white border-opacity-25 text-white-50">
                    <i class="bi bi-at"></i>
                </span>
                <input type="text" name="username" class="form-control border-start-0" placeholder="Buat username" required>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label small opacity-75">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0 border-white border-opacity-25 text-white-50">
                    <i class="bi bi-lock"></i>
                </span>
                <input type="password" name="password" class="form-control border-start-0" placeholder="Buat password" required>
            </div>
        </div>

        <button type="submit" name="register" class="btn btn-reg w-100 mb-3 text-white">
            DAFTAR SEKARANG
        </button>
        
        <div class="text-center small">
            <span class="opacity-75">Sudah punya akun?</span> 
            <a href="login.php" class="text-white text-decoration-none fw-bold">Login di sini</a>
        </div>
    </form>
</div>

</body>
</html>