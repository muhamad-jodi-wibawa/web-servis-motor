<?php
session_start();

// Validasi ganda (Session & Cookie) agar stabil di lingkungan serverless Vercel
$role = isset($_SESSION['role']) ? $_SESSION['role'] : (isset($_COOKIE['user_role']) ? $_COOKIE['user_role'] : '');
$nama_user = isset($_SESSION['nama']) ? $_SESSION['nama'] : (isset($_COOKIE['user_nama']) ? $_COOKIE['user_nama'] : 'Rider');

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

// Pengecekan jalur file algoritma
if (file_exists(__DIR__ . '/../algoritma.php')) {
    require_once __DIR__ . '/../algoritma.php';
} else if (file_exists(__DIR__ . '/../../algoritma.php')) {
    require_once __DIR__ . '/../../algoritma.php';
} else {
    require_once dirname(__DIR__, 2) . '/algoritma.php';
}

// Ambil ID User dari Session atau Cookie cadangan
$id_user = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : (isset($_COOKIE['user_id']) ? $_COOKIE['user_id'] : '');

// 1. Ambil list kendaraan user (Query utama)
$q_list = mysqli_query($conn, "SELECT id_kendaraan, plat_nomor, tipe_kendaraan FROM kendaraan WHERE id_user = '$id_user'");
$jumlah_kendaraan = mysqli_num_rows($q_list);

$id_k_aktif = isset($_GET['id_k']) ? $_GET['id_k'] : null;

// 2. Jika belum ada id_k, ambil ID kendaraan pertama jika ada
if (!$id_k_aktif && $jumlah_kendaraan > 0) {
    // Kita ambil baris pertama dari hasil query $q_list tadi tanpa merusak pointer-nya secara drastis
    $list_array = [];
    mysqli_data_seek($q_list, 0);
    while($row = mysqli_fetch_assoc($q_list)) { $list_array[] = $row; }
    $id_k_aktif = $list_array[0]['id_kendaraan'];
    // Reset pointer agar dropdown tetap bisa dirender
    mysqli_data_seek($q_list, 0);
}

$has_vehicle = false;
$analisis = null;
if ($id_k_aktif) {
    $q_detail = mysqli_query($conn, "SELECT * FROM kendaraan WHERE id_kendaraan = '$id_k_aktif' AND id_user = '$id_user'");
    $data_ken = mysqli_fetch_assoc($q_detail);
    if ($data_ken) {
        $has_vehicle = true;
        $analisis = hitungDetailPrediksi($id_k_aktif, $conn);
    }
}

function cleanNumber($val) {
    return (float)str_replace(',', '', $val);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard ServisKu Motor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.75), rgba(0, 0, 0, 0.9)), url('https://images.unsplash.com/photo-1558981403-c5f91cbba527?q=80&w=2000&auto=format&fit=crop');
            background-size: cover; background-attachment: fixed; background-position: center; color: #f8f9fa; font-family: 'Segoe UI', Roboto, sans-serif;
        }
        .navbar { background: rgba(20, 20, 20, 0.8) !important; backdrop-filter: blur(15px); border-bottom: 2px solid #dc3545; }
        .glass-card { background: rgba(255, 255, 255, 0.07); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 15px; }
        .prediction-box { background: linear-gradient(135deg, #dc3545, #8b0000); border-radius: 20px; padding: 40px; box-shadow: 0 10px 30px rgba(220, 53, 69, 0.4); border: 1px solid rgba(255, 255, 255, 0.2); }
        .stat-badge { background: rgba(0, 0, 0, 0.3); border: 1px solid rgba(255, 255, 255, 0.2); padding: 10px; border-radius: 10px; }
        .table { color: #fff; }
        .table thead { background: rgba(220, 53, 69, 0.2); }
        .form-select-custom { background: #dc3545; color: white; border: none; font-weight: bold; border-radius: 30px; }
        .btn-add-motor { background: rgba(255, 255, 255, 0.1); color: white; border: 1px dashed rgba(255, 255, 255, 0.4); }
        .btn-add-motor:hover { background: #dc3545; border-color: #dc3545; color: white; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark mb-4 sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#"><i class="bi bi-speedometer2 text-danger"></i> SERVISKU</a>
        <div class="d-flex align-items-center">
            <span class="me-3 small">Rider: <strong><?= htmlspecialchars($nama_user) ?></strong></span>
            <a href="../logout.php" class="btn btn-danger btn-sm rounded-pill px-3">Logout</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6 mb-3">
            <div class="glass-card p-3 d-flex align-items-center">
                <span class="small fw-bold text-uppercase opacity-75 me-3">Unit</span>
                <form action="" method="GET" class="flex-grow-1">
                    <select name="id_k" class="form-select form-select-custom shadow-sm" onchange="this.form.submit()">
                        <?php if ($jumlah_kendaraan == 0): ?>
                            <option value="">Belum Ada Motor</option>
                        <?php else: 
                            mysqli_data_seek($q_list, 0); // Pastikan pointer di awal
                            while($list = mysqli_fetch_assoc($q_list)): ?>
                                <option value="<?= $list['id_kendaraan'] ?>" <?= ($id_k_aktif == $list['id_kendaraan']) ? 'selected' : '' ?>>
                                    <?= strtoupper($list['plat_nomor']) ?> - <?= htmlspecialchars($list['tipe_kendaraan']) ?>
                                </option>
                            <?php endwhile; 
                        endif; ?>
                    </select>
                </form>
            </div>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="tambah_motor.php" class="btn btn-add-motor rounded-pill px-4"><i class="bi bi-plus-circle-fill me-2"></i> Daftarkan Motor Baru</a>
        </div>
    </div>

    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
