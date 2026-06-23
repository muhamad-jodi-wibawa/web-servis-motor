<?php
session_start();

// Validasi ganda menggunakan Session dan Cookie khusus lingkungan Vercel
$role = isset($_SESSION['role']) ? $_SESSION['role'] : (isset($_COOKIE['user_role']) ? $_COOKIE['user_role'] : '');
$nama_user = isset($_SESSION['nama']) ? $_SESSION['nama'] : (isset($_COOKIE['user_nama']) ? $_COOKIE['user_nama'] : 'Admin');

if ($role !== 'sa') {
    echo "<script>alert('Sesi habis atau Akses ditolak! Silakan login kembali.'); window.location.href='../login.php';</script>";
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

// Menangkap ID Kendaraan jika diklik dari halaman monitoring
$id_k_pilihan = isset($_GET['id_k']) ? $_GET['id_k'] : '';

if (isset($_POST['simpan'])) {
    $id_kendaraan = $_POST['id_kendaraan'];
    $tgl_servis   = $_POST['tgl_servis'];
    $km_sekarang  = $_POST['km_sekarang'];
    $keterangan   = mysqli_real_escape_string($conn, $_POST['keterangan']);

    $query = "INSERT INTO riwayat_servis (id_kendaraan, tgl_servis, km_sekarang, keterangan) 
              VALUES ('$id_kendaraan', '$tgl_servis', '$km_sekarang', '$keterangan')";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Data Servis Berhasil Dicatat!'); window.location='monitoring.php';</script>";
        exit();
    } else {
        echo "<script>alert('Gagal mencatat servis!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Input Servis - ServisKu Motor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.85)), 
                        url('https://images.unsplash.com/photo-1558981403-c5f91cbba527?q=80&w=2000&auto=format&fit=crop');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            min-height: 100vh;
            display: flex;
            align-items: center;
            color: white;
            padding: 40px 0;
            font-family: 'Segoe UI', Roboto, sans-serif;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.6);
        }

        .form-label { font-weight: 600; font-size: 0.85rem; color: #ffc107; text-transform: uppercase; }

        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 12px;
            padding: 12px;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-color: #dc3545;
            box-shadow: 0 0 15px rgba(220, 53, 69, 0.3);
        }

        .form-select option { background: #1a1a1a; color: white; }

        .btn-submit {
            background: linear-gradient(45deg, #dc3545, #8b0000);
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-weight: bold;
            letter-spacing: 1px;
            transition: 0.3s;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(220, 53, 69, 0.4);
            background: linear-gradient(45deg, #ff4d5a, #dc3545);
        }

        .info-box {
            background: rgba(220, 53, 69, 0.1);
            border-left: 4px solid #dc3545;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
    </style>
</head>
<body>

<div class="container w-100">
    <div class="row justify-content-center w-100 m-0">
        <div class="col-lg-6">
            <div class="glass-card shadow-lg">
                <div class="text-center mb-4">
                    <div class="mb-2">
                        <i class="bi bi-clipboard2-check-fill text-danger fs-1"></i>
                    </div>
                    <h3 class="fw-bold">Input Riwayat Servis</h3>
                    <p class="text-white-50 small">Catat pemeliharaan rutin untuk kalkulasi prediksi</p>
                </div>

                <div class="info-box">
                    <small class="d-block text-white-50"><i class="bi bi-info-circle me-1"></i> Tips:</small>
                    <span class="small">Pastikan angka <strong>Odometer (KM)</strong> yang dimasukkan sesuai dengan yang tertera di panel instrumen motor.</span>
                </div>

                <form method="POST">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Pilih Motor Pelanggan</label>
                            <select name="id_kendaraan" class="form-select" required>
                                <option value="">-- Cari Plat Nomor / Tipe --</option>
                                <?php
                                $q_ken = mysqli_query($conn, "SELECT k.*, u.nama FROM kendaraan k JOIN users u ON k.id_user = u.id_user ORDER BY u.nama ASC");
                                while($k = mysqli_fetch_assoc($q_ken)) {
                                    $selected = ($id_k_pilihan == $k['id_kendaraan']) ? 'selected' : '';
                                    echo "<option value='".$k['id_kendaraan']."' $selected>".strtoupper($k['plat_nomor'])." - ".$k['tipe_kendaraan']." (".$k['nama'].")</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Servis</label>
                            <input type="date" name="tgl_servis" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kilometer (ODO)</label>
                            <div class="input-group">
                                <input type="number" name="km_sekarang" class="form-control" placeholder="Contoh: 15000" required>
                                <span class="input-group-text bg-dark border-secondary text-white small">KM</span>
                            </div>
                        </div>

                        <div class="col-md-12 mb-4">
                            <label class="form-label">Keterangan / Tindakan</label>
                            <textarea name="keterangan" class="form-control" rows="3" placeholder="Contoh: Ganti oli mesin, ganti kampas rem depan, cek CVT." required></textarea>
                        </div>
                    </div>

                    <button type="submit" name="simpan" class="btn btn-submit w-100 text-white shadow mb-4">
                        <i class="bi bi-cloud-arrow-up-fill me-2"></i> SIMPAN RIWAYAT SERVIS
                    </button>
                    <div class="text-center border-top pt-3 border-white border-opacity-10">
                        <a href="dashboard_sa.php" class="text-white-50 text-decoration-none small">
                            <i class="bi bi-chevron-left"></i> Kembali ke Dashboard
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
