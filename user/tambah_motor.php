<?php
session_start();

// Validasi ganda menggunakan Session dan Cookie khusus lingkungan Vercel
$role = isset($_SESSION['role']) ? $_SESSION['role'] : (isset($_COOKIE['user_role']) ? $_COOKIE['user_role'] : '');
$nama_user = isset($_SESSION['nama']) ? $_SESSION['nama'] : (isset($_COOKIE['user_nama']) ? $_COOKIE['user_nama'] : 'User');

if ($role !== 'user' && $role !== 'sa') {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location.href='../login.php';</script>";
    exit();
}

// Pengecekan jalur koneksi database otomatis khusus Vercel
if (file_exists(__DIR__ . '/../config/koneksi.php')) {
    require_once __DIR__ . '/../config/koneksi.php';
} else if (file_exists(__DIR__ . '/../../config/koneksi.php')) {
    require_once __DIR__ . '/../../config/koneksi.php';
} else if (isset($_SERVER['DOCUMENT_ROOT']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/config/koneksi.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/config/koneksi.php';
} else {
    require_once dirname(__DIR__, 2) . '/config/koneksi.php';
}

if (isset($_POST['daftar'])) {
    // KUNCI PERBAIKAN: Ambil ID User dari Session, jika kosong ambil dari Cookie cadangan
    $id_user = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : (isset($_COOKIE['user_id']) ? $_COOKIE['user_id'] : ''); 
    
    $plat_nomor = mysqli_real_escape_string($conn, strtoupper($_POST['plat_nomor']));
    $tipe_kendaraan = mysqli_real_escape_string($conn, $_POST['tipe_kendaraan']);

    if (!empty($id_user)) {
        $query = "INSERT INTO kendaraan (id_user, plat_nomor, tipe_kendaraan) VALUES ('$id_user', '$plat_nomor', '$tipe_kendaraan')";
        
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Motor Berhasil Didaftarkan!'); window.location.href='dashboard.php';</script>";
            exit();
        } else {
            echo "<script>alert('Gagal Mendaftarkan Motor ke Database!');</script>";
        }
    } else {
        echo "<script>alert('Sesi Anda tidak valid / ID tidak terbaca. Silakan login ulang.'); window.location.href='../login.php';</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Motor Baru - ServisKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.9)), 
                        url('https://images.unsplash.com/photo-1558981403-c5f91cbba527?q=80&w=2000');
            background-size: cover;
            height: 100vh;
            display: flex;
            align-items: center;
            color: white;
            font-family: 'Segoe UI', Roboto, sans-serif;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 30px;
        }
        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
        }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-color: #dc3545;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="glass-card shadow-lg">
                <h4 class="fw-bold mb-4 text-center"><i class="bi bi-bicycle text-danger"></i> Daftarkan Motor Anda</h4>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small opacity-75">Nomor Plat</label>
                        <input type="text" name="plat_nomor" class="form-control" placeholder="Contoh: B 1234 ABC" required style="text-transform: uppercase;">
                    </div>
                    <div class="mb-4">
                        <label class="form-label small opacity-75">Tipe / Model Motor</label>
                        <input type="text" name="tipe_kendaraan" class="form-control" placeholder="Contoh: Honda Beat Street" required>
                    </div>
                    <button type="submit" name="daftar" class="btn btn-danger w-100 fw-bold py-2 mb-3">DAFTARKAN SEKARANG</button>
                    <div class="text-center">
                        <a href="dashboard.php" class="text-white-50 small text-decoration-none"><i class="bi bi-arrow-left"></i> Kembali ke Dashboard</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
