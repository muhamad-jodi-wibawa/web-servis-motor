<?php
session_start();

// Validasi ganda menggunakan Session dan Cookie khusus lingkungan Vercel
$role = isset($_SESSION['role']) ? $_SESSION['role'] : (isset($_COOKIE['user_role']) ? $_COOKIE['user_role'] : '');
$nama_user = isset($_SESSION['nama']) ? $_SESSION['nama'] : (isset($_COOKIE['user_nama']) ? $_COOKIE['user_nama'] : 'Rider');

if ($role !== 'user' && $role !== 'sa') { 
    // Jika tidak valid, tendang kembali ke login menggunakan JavaScript yang aman bagi Vercel
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

// Pengecekan jalur file algoritma otomatis khusus Vercel
if (file_exists(__DIR__ . '/../algoritma.php')) {
    require_once __DIR__ . '/../algoritma.php';
} else if (file_exists(__DIR__ . '/../../algoritma.php')) {
    require_once __DIR__ . '/../../algoritma.php';
} else {
    require_once dirname(__DIR__, 2) . '/algoritma.php';
}

// Gunakan ID User cadangan jika session serverless terhapus
$id_user = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : '';

// Ambil list kendaraan user
$q_list = mysqli_query($conn, "SELECT id_kendaraan, plat_nomor, tipe_kendaraan FROM kendaraan WHERE id_user = '$id_user'");
$jumlah_kendaraan = mysqli_num_rows($q_list);

$id_k_aktif = isset($_GET['id_k']) ? $_GET['id_k'] : null;

// Jika belum pilih kendaraan, pilih yang pertama secara otomatis
if (!$id_k_aktif && $jumlah_kendaraan > 0) {
    $row_first = mysqli_fetch_assoc($q_list);
    $id_k_aktif = $row_first['id_kendaraan'];
    mysqli_data_seek($q_list, 0); 
}

$has_vehicle = false;
$analisis = null;
if ($id_k_aktif) {
    // Super Admin ('sa') juga diberikan izin melihat data ini jika sedang melakukan simulasi/viewing
    $q_detail = mysqli_query($conn, "SELECT * FROM kendaraan WHERE id_kendaraan = '$id_k_aktif'" . ($role === 'user' ? " AND id_user = '$id_user'" : ""));
    $data_ken = mysqli_fetch_assoc($q_detail);
    if ($data_ken) {
        $has_vehicle = true;
        $analisis = hitungDetailPrediksi($id_k_aktif, $conn);
    }
}

// FUNGSI HELPER
if (!function_exists('cleanNumber')) {
    function cleanNumber($val) {
        return (float)str_replace(',', '', $val);
    }
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
            background: linear-gradient(rgba(0, 0, 0, 0.75), rgba(0, 0, 0, 0.9)), 
                        url('https://images.unsplash.com/photo-1558981403-c5f91cbba527?q=80&w=2000&auto=format&fit=crop');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            color: #f8f9fa;
            font-family: 'Segoe UI', Roboto, sans-serif;
        }

        .navbar {
            background: rgba(20, 20, 20, 0.8) !important;
            backdrop-filter: blur(15px);
            border-bottom: 2px solid #dc3545;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
        }

        .prediction-box {
            background: linear-gradient(135deg, #dc3545, #8b0000);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(220, 53, 69, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .stat-badge {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 10px;
            border-radius: 10px;
        }

        .table { color: #fff; }
        .table thead { background: rgba(220, 53, 69, 0.2); border-bottom: 0; }
        .table-hover tbody tr:hover { background: rgba(255, 255, 255, 0.05); }
        
        .form-select-custom {
            background: #dc3545;
            color: white;
            border: none;
            font-weight: bold;
            border-radius: 30px;
        }

        .btn-add-motor {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 1px dashed rgba(255, 255, 255, 0.4);
            transition: 0.3s;
        }
        .btn-add-motor:hover {
            background: #dc3545;
            border-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark mb-4 sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#"><i class="bi bi-speedometer2 text-danger"></i> SERVISKU <span class="fw-light">MOTOR</span></a>
        <div class="d-flex align-items-center">
            <span class="me-3 small d-none d-md-inline">Rider: <strong><?= htmlspecialchars($nama_user) ?></strong></span>
            <a href="../logout.php" class="btn btn-danger btn-sm rounded-pill px-3">Logout</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6 mb-3 mb-md-0">
            <div class="glass-card p-3 d-flex align-items-center">
                <span class="small fw-bold text-uppercase opacity-75 me-3"><i class="bi bi-bicycle me-2"></i> Unit</span>
                <form action="" method="GET" class="flex-grow-1">
                    <select name="id_k" class="form-select form-select-custom shadow-sm" onchange="this.form.submit()">
                        <?php if ($jumlah_kendaraan == 0): ?>
                            <option value="">Belum Ada Motor</option>
                        <?php else: ?>
                            <?php while($list = mysqli_fetch_assoc($q_list)): ?>
                                <option value="<?= $list['id_kendaraan'] ?>" <?= ($id_k_aktif == $list['id_kendaraan']) ? 'selected' : '' ?>>
                                    <?= strtoupper($list['plat_nomor']) ?> - <?= $list['tipe_kendaraan'] ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </form>
            </div>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="tambah_motor.php" class="btn btn-add-motor rounded-pill px-4">
                <i class="bi bi-plus-circle-fill me-2"></i> Daftarkan Motor Baru
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="prediction-box text-center shadow-lg">
                <h6 class="text-uppercase fw-bold opacity-75 mb-3">Jadwal Servis Berikutnya</h6>
                <div class="display-3 fw-bold mb-2">
                    <?php 
                        if ($has_vehicle) {
                            echo is_array($analisis) ? $analisis['tgl_prediksi'] : "<small class='fs-4'>$analisis</small>";
                        } else { echo "-- / -- / --"; }
                    ?>
                </div>
                <p class="small opacity-75 mb-4">Pastikan kondisi mesin tetap prima</p>
                
                <?php if (is_array($analisis)): ?>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="stat-badge">
                            <small class="d-block opacity-50">Mobilitas</small>
                            <span class="fw-bold"><?= $analisis['b'] ?> KM/Hari</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-badge">
                            <small class="d-block opacity-50">Target Servis</small>
                            <span class="fw-bold"><?= number_format(cleanNumber($analisis['target_km'])) ?> KM</span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="glass-card p-4 mt-4">
                <h6 class="fw-bold mb-3 text-danger"><i class="bi bi-shield-check me-2"></i> Status Kendaraan</h6>
                <p class="small mb-0 opacity-75 text-white">
                    <?php if(!$has_vehicle): ?>
                        Silakan daftarkan motor Anda terlebih dahulu untuk memulai monitoring.
                    <?php else: ?>
                        Data motor <strong><?= strtoupper($data_ken['plat_nomor']) ?></strong> terpantau aktif. Sistem akan memperbarui prediksi setiap kali admin menginput riwayat servis baru.
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="glass-card h-100 overflow-hidden">
                <div class="p-4 border-bottom border-white border-opacity-10">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-danger"></i> Log Servis Berkala</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="small text-uppercase">
                            <tr>
                                <th class="ps-4">Tgl Layanan</th>
                                <th>Odometer (KM)</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            <?php
                            if ($has_vehicle) {
                                $q_riwayat = mysqli_query($conn, "SELECT * FROM riwayat_servis WHERE id_kendaraan = '$id_k_aktif' ORDER BY tgl_servis DESC");
                                if (mysqli_num_rows($q_riwayat) > 0) {
                                    while ($row = mysqli_fetch_assoc($q_riwayat)) {
                                        $km_safe = cleanNumber($row['km_sekarang']);
                                        echo "<tr>
                                                <td class='ps-4'>".date('d/m/Y', strtotime($row['tgl_servis']))."</td>
                                                <td class='fw-bold text-danger'>".number_format($km_safe)." KM</td>
                                                <td class='small opacity-75'>".$row['keterangan']."</td>
                                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='3' class='text-center py-5 opacity-50'>Belum ada data servis dari admin.</td></tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3' class='text-center py-5 opacity-50'>Pilih kendaraan untuk melihat riwayat.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
