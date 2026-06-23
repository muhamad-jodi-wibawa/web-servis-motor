<?php
session_start();

// Pengecekan jalur ganda otomatis untuk menjinakkan lingkungan Vercel
if (file_exists(__DIR__ . '/config/koneksi.php')) {
    require_once __DIR__ . '/config/koneksi.php';
} else if (file_exists(__DIR__ . '/../config/koneksi.php')) {
    require_once __DIR__ . '/../config/koneksi.php';
} else if (isset($_SERVER['DOCUMENT_ROOT']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/config/koneksi.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/config/koneksi.php';
} else {
    require_once dirname(__DIR__) . '/config/koneksi.php';
}

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Eksekusi query dengan pelacak error bawaan cloud
    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'") or die("Gagal query: " . mysqli_error($conn));
    $data = mysqli_fetch_assoc($query);

    if (mysqli_num_rows($query) > 0) {
        $_SESSION['id_user'] = $data['id_user'];
        $_SESSION['nama']    = $data['nama'];
        $_SESSION['role']    = $data['role'];

        // Backup menggunakan Cookie agar login tetap bertahan di lingkungan serverless
        setcookie('user_role', $data['role'], time() + 3600, "/");
        setcookie('user_nama', $data['nama'], time() + 3600, "/");

        // Menggunakan pengalihan JavaScript (jauh lebih stabil di Vercel daripada header PHP)
        if ($data['role'] == 'sa') {
            echo "<script>window.location.href='admin/dashboard_sa.php';</script>";
        } else {
            echo "<script>window.location.href='user/dashboard.php';</script>";
        }
        exit();
    } else {
        echo "<script>alert('Username atau Password Salah!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ServisKu System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.8)), 
                        url('https://images.unsplash.com/photo-1530046339160-ce3e530c7d2f?q=80&w=2000&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            width: 100%;
            max-width: 400px;
            padding: 40px;
            color: white;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            border-radius: 10px;
            padding: 12px;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border-color: #0d6efd;
            box-shadow: none;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .btn-login {
            background: #0d6efd;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-login:hover {
            background: #0b5ed7;
            transform: translateY(-2px);
        }

        .logo-icon {
            font-size: 3rem;
            margin-bottom: 10px;
            color: #0d6efd;
        }
    </style>
</head>
<body>

<div class="login-card text-center">
    <div class="logo-icon">
        <i class="bi bi-gear-wide-connected"></i>
    </div>
    <h3 class="fw-bold mb-1">ServisKu</h3>
    <p class="small text-white-50 mb-4">Workshop Management System</p>

    <form method="POST">
        <div class="mb-3 text-start">
            <label class="form-label small opacity-75">Username</label>
            <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
        </div>
        <div class="mb-4 text-start">
            <label class="form-label small opacity-75">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
        </div>
        <button type="submit" name="login" class="btn btn-login w-100 mb-3 text-white">
            MASUK KE SISTEM
        </button>
        <div class="mt-2 small text-white-50">
            Belum punya akun? <a href="registrasi.php" class="text-white text-decoration-none fw-bold">Daftar Sekarang</a>
        </div>
        <div class="mt-2 small text-white-50">
            <a href="lupa_password.php" class="text-white small">Lupa password?</a>
        </div>
    </form>
</div>

</body>
</html>
