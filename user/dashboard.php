<?php
session_start();
// Gunakan jalur relatif yang lebih aman untuk Vercel
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../algoritma.php'; 

// Validasi Session & Cookie
$id_user = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : (isset($_COOKIE['user_id']) ? $_COOKIE['user_id'] : '');
$role    = isset($_SESSION['role']) ? $_SESSION['role'] : (isset($_COOKIE['user_role']) ? $_COOKIE['user_role'] : '');

if (empty($id_user) || $role != 'user') { 
    echo "<script>alert('Sesi berakhir. Silakan login kembali.'); window.location.href='../login.php';</script>";
    exit();
}

// Ambil list kendaraan user
$q_list = mysqli_query($conn, "SELECT id_kendaraan, plat_nomor, tipe_kendaraan FROM kendaraan WHERE id_user = '$id_user'");
$jumlah_kendaraan = mysqli_num_rows($q_list);

$id_k_aktif = isset($_GET['id_k']) ? $_GET['id_k'] : null;

// Jika belum pilih kendaraan, ambil ID dari data pertama tanpa merusak pointer query
if (!$id_k_aktif && $jumlah_kendaraan > 0) {
    // Kita gunakan data_seek untuk reset pointer jika perlu
    mysqli_data_seek($q_list, 0);
    $row_first = mysqli_fetch_assoc($q_list);
    $id_k_aktif = $row_first['id_kendaraan'];
    // Reset kembali ke nol agar dropdown bisa menampilkan semua data
    mysqli_data_seek($q_list, 0);
}

$has_vehicle = false;
$analisis = null;
$data_ken = null;

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard ServisKu Motor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.75), rgba(0, 0, 0, 0.9)), url('https://images.unsplash.com/photo-1558981403-c5f91cbba527?q=80&w=2000');
            background-size: cover; background-attachment: fixed; color: #fff; font-family: 'Segoe UI', sans-serif;
        }
        .navbar { background: rgba(0,0,0,0.8) !important; backdrop-filter: blur(10px); border-bottom: 2px solid #dc3545; }
        .glass-card { background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); border-radius: 15px; border: 1px solid rgba(255,255,255,0.1); }
        .prediction-box { background: linear-gradient(135deg, #dc3545, #8b0000); border-radius: 20px; padding: 30px; }
        .form-select-custom { background: #dc3545; color: white; border: none; font-weight: bold; border-radius: 30px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#"><i class="bi bi-speedometer2 text-danger"></i> SERVISKU</a>
        <div class="d-flex align-items-center">
            <span class="me-3 small">Rider: <strong><?= htmlspecialchars($_SESSION['nama'] ?? 'User') ?></strong></span>
            <a href="../logout.php" class="btn btn-danger btn-sm rounded-pill px-3">Logout</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <div class="glass-card p-3">
                <form action="" method="GET">
                    <select name="id_k" class="form-select form-select-custom" onchange="this.form.submit()">
                        <?php if ($jumlah_kendaraan == 0): ?>
                            <option>Belum Ada Motor</option>
                        <?php else: 
                            mysqli_data_seek($q_list, 0); 
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
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="tambah_motor.php" class="btn btn-outline-light rounded-pill"><i class="bi bi-plus-circle me-1"></i> Daftarkan Motor Baru</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="prediction-box shadow-lg">
                <h6 class="text-uppercase fw-bold opacity-75">Jadwal Servis Berikutnya</h6>
                <div class="display-4 fw-bold my-3">
                    <?php echo ($has_vehicle && is_array($analisis)) ? $analisis['tgl_prediksi'] : "-- / -- / --"; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-7">
            <div class="glass-card h-100 p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-clock-history text-danger"></i> Log Servis</h5>
                <div class="table-responsive">
                    <table class="table table-hover text-white">
                        <thead><tr><th>Tgl</th><th>KM</th><th>Ket</th></tr></thead>
                        <tbody>
                            <?php
                            if ($has_vehicle) {
                                $q_riwayat = mysqli_query($conn, "SELECT * FROM riwayat_servis WHERE id_kendaraan = '$id_k_aktif' ORDER BY tgl_servis DESC");
                                while ($row = mysqli_fetch_assoc($q_riwayat)) {
                                    echo "<tr><td>".date('d/m/Y', strtotime($row['tgl_servis']))."</td><td>".number_format(cleanNumber($row['km_sekarang']))."</td><td>".htmlspecialchars($row['keterangan'])."</td></tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3' class='text-center'>Pilih kendaraan terlebih dahulu</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
