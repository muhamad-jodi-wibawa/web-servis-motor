<?php
session_start();

// Validasi ganda menggunakan Session dan Cookie agar tidak terlempar di Vercel
$role = isset($_SESSION['role']) ? $_SESSION['role'] : (isset($_COOKIE['user_role']) ? $_COOKIE['user_role'] : '');
$nama_user = isset($_SESSION['nama']) ? $_SESSION['nama'] : (isset($_COOKIE['user_nama']) ? $_COOKIE['user_nama'] : 'Admin');

if ($role !== 'sa') {
    // Jika bukan super admin, langsung tendang kembali ke login
    echo "<script>alert('Sesi habis atau Akses ditolak! Silakan login kembali.'); window.location.href='../login.php';</script>";
    exit();
}

// Pengecekan jalur koneksi database ganda otomatis untuk Vercel
if (file_exists(__DIR__ . '/../config/koneksi.php')) {
    require_once __DIR__ . '/../config/koneksi.php';
} else if (file_exists(__DIR__ . '/../../config/koneksi.php')) {
    require_once __DIR__ . '/../../config/koneksi.php';
} else if (isset($_SERVER['DOCUMENT_ROOT']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/config/koneksi.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/config/koneksi.php';
} else {
    require_once dirname(__DIR__, 2) . '/config/koneksi.php';
}


if (!isset($_SESSION['role']) || $_SESSION['role'] != 'sa') {
    header("Location: ../login.php");
    exit();
}

if (isset($_POST['simpan'])) {
    $id_user = $_POST['id_user'];
    $plat_nomor = mysqli_real_escape_string($conn, strtoupper($_POST['plat_nomor']));
    $tipe_kendaraan = mysqli_real_escape_string($conn, $_POST['tipe_kendaraan']);

    $query = "INSERT INTO kendaraan (id_user, plat_nomor, tipe_kendaraan) VALUES ('$id_user', '$plat_nomor', '$tipe_kendaraan')";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Kendaraan Berhasil Ditambahkan!'); window.location='monitoring.php';</script>";
    } else {
        echo "<script>alert('Gagal Menambah Data!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Kendaraan - ServisKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.75), rgba(0, 0, 0, 0.85)), 
                        url('https://images.unsplash.com/photo-1599812411674-69deec1741f8?q=80&w=2000&auto=format&fit=crop');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 40px;
            color: white;
            box-shadow: 0 15px 35px rgba(0,0,0,0.6);
        }

        .form-label {
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            border-radius: 12px;
            padding: 10px 15px;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-color: #ffc107; 
            box-shadow: 0 0 10px rgba(255, 193, 7, 0.2);
        }

        .form-select option {
            background: #1a1a1a;
            color: white;
        }

        .btn-save {
            background: linear-gradient(45deg, #dc3545, #921d28);
            border: none;
            padding: 12px;
            border-radius: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.3s;
        }

        .btn-save:hover {
            background: linear-gradient(45deg, #ff4d5a, #dc3545);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
        }

        .btn-back {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            transition: 0.3s;
            font-size: 0.9rem;
        }

        .btn-back:hover {
            color: #ffc107;
        }

        .icon-box {
            width: 70px;
            height: 70px;
            background: rgba(220, 53, 69, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            border: 1px solid rgba(220, 53, 69, 0.5);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="glass-card shadow-lg">
                <div class="text-center mb-4">
                    <div class="icon-box">
                        <i class="bi bi-gear-wide-connected fs-1 text-danger"></i>
                    </div>
                    <h3 class="fw-bold mb-1">Registrasi Motor</h3>
                    <p class="small text-white-50">Input data unit baru ke dalam sistem</p>
                </div>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small text-uppercase opacity-75">Nama Pelanggan</label>
                        <select name="id_user" class="form-select" required>
                            <?php
                            // Ambil User 'PELANGGAN UMUM' terlebih dahulu agar muncul paling atas
                            $q_umum = mysqli_query($conn, "SELECT id_user, nama FROM users WHERE nama LIKE '%UMUM%' LIMIT 1");
                            $umum = mysqli_fetch_assoc($q_umum);
                            
                            if($umum) {
                                echo "<option value='".$umum['id_user']."' selected>-- ".strtoupper($umum['nama'])." (DEFAULT) --</option>";
                            } else {
                                echo "<option value=''>-- Pilih Pemilik --</option>";
                            }

                            // Ambil sisa user lainnya
                            $users = mysqli_query($conn, "SELECT id_user, nama FROM users WHERE role = 'user' AND nama NOT LIKE '%UMUM%' ORDER BY nama ASC");
                            while($u = mysqli_fetch_assoc($users)) {
                                echo "<option value='".$u['id_user']."'>".strtoupper($u['nama'])."</option>";
                            }
                            ?>
                        </select>
                        <div class="form-text text-white-50" style="font-size: 0.7rem;">*Gunakan Pelanggan Umum jika pemilik belum punya akun.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-uppercase opacity-75">Nomor Plat</label>
                        <input type="text" name="plat_nomor" class="form-control" placeholder="Contoh: D 4421 VE" style="text-transform: uppercase;" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small text-uppercase opacity-75">Model / Varian Motor</label>
                        <input type="text" name="tipe_kendaraan" class="form-control" placeholder="Contoh: Yamaha NMAX 155" required>
                    </div>

                    <button type="submit" name="simpan" class="btn btn-save w-100 mb-4 text-white">
                        <i class="bi bi-plus-circle me-2"></i> Simpan ke Database
                    </button>
                    
                    <div class="text-center border-top pt-3 border-white border-opacity-10">
                        <a href="dashboard_sa.php" class="btn-back">
                            <i class="bi bi-arrow-left-circle me-1"></i> Kembali ke Dashboard
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
