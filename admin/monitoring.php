<?php
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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Monitoring Pelanggan - ServisKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { 
            /* Background: Nuansa Bengkel yang lebih terang agar data tabel tetap terbaca jelas */
            background: linear-gradient(rgba(244, 247, 246, 0.85), rgba(244, 247, 246, 0.95)), 
                        url('https://images.unsplash.com/photo-1517524206127-48bbd363f3d7?q=80&w=2000&auto=format&fit=crop');
            background-size: cover;
            background-attachment: fixed;
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
        }

        .navbar {
            background: rgba(33, 37, 41, 0.9) !important;
            backdrop-filter: blur(10px);
        }

        /* Styling Accordion agar terlihat 'Glassy' */
        .accordion-item {
            background: rgba(255, 255, 255, 0.7) !important;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 15px !important;
            margin-bottom: 12px;
            overflow: hidden;
        }

        .accordion-button {
            background: rgba(255, 255, 255, 0.5);
            font-weight: 600;
        }

        .accordion-button:not(.collapsed) {
            background-color: #dc3545 !important; /* Warna Merah Racing saat dibuka */
            color: white !important;
        }

        .accordion-button:not(.collapsed)::after {
            filter: brightness(0) invert(1);
        }

        .vehicle-card {
            background: white;
            border-left: 5px solid #212529;
            border-radius: 10px;
            margin-bottom: 20px;
            transition: 0.3s;
        }

        .vehicle-card:hover {
            transform: scale(1.01);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }

        .search-input {
            border-radius: 30px;
            padding-left: 40px;
            border: 2px solid #dc3545;
            background: rgba(255, 255, 255, 0.9);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark mb-4 shadow">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard_sa.php"><i class="bi bi-gear-wide-connected text-danger"></i> Admin ServisKu</a>
        <a href="dashboard_sa.php" class="btn btn-outline-light btn-sm rounded-pill px-3 italic">Dashboard</a>
    </div>
</nav>

<div class="container pb-5">
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h2 class="fw-bold text-dark"><i class="bi bi-person-lines-fill"></i> Monitoring Data</h2>
            <p class="text-secondary small">Pantau performa dan riwayat kendaraan pelanggan secara real-time.</p>
        </div>
        <div class="col-md-6 text-end position-relative">
            <i class="bi bi-search position-absolute" style="left: 25px; top: 12px; z-index: 10; color: #dc3545;"></i>
            <input type="text" id="cariPelanggan" class="form-control search-input shadow-sm" placeholder="Cari Nama atau No Plat...">
        </div>
    </div>

    <div class="accordion" id="accordionMonitoring">
        <?php
        $q_user = mysqli_query($conn, "SELECT DISTINCT u.id_user, u.nama 
                                      FROM users u 
                                      JOIN kendaraan k ON u.id_user = k.id_user 
                                      ORDER BY u.nama ASC");

        while($user = mysqli_fetch_assoc($q_user)):
            $id_u = $user['id_user'];
            $target_id = "collapseUser" . $id_u;
        ?>
            <div class="accordion-item user-item" data-nama="<?= strtolower($user['nama']) ?>">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $target_id ?>">
                        <i class="bi bi-person-badge me-2"></i> <?= strtoupper($user['nama']) ?>
                    </button>
                </h2>
                <div id="<?= $target_id ?>" class="accordion-collapse collapse" data-bs-parent="#accordionMonitoring">
                    <div class="accordion-body bg-light bg-opacity-50">
                        
                        <?php
                        $q_ken = mysqli_query($conn, "SELECT * FROM kendaraan WHERE id_user = '$id_u'");
                        while($ken = mysqli_fetch_assoc($q_ken)):
                            $id_k = $ken['id_kendaraan'];
                        ?>
                            <div class="vehicle-card shadow-sm p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <span class="badge bg-danger mb-1 shadow-sm"><?= strtoupper($ken['plat_nomor']) ?></span>
                                        <h5 class="fw-bold mb-0"><?= $ken['tipe_kendaraan'] ?></h5>
                                    </div>
                                    <div class="btn-group shadow-sm rounded">
                                        <a href="input_servis.php?id_k=<?= $id_k ?>" class="btn btn-sm btn-dark">
                                            <i class="bi bi-plus-lg"></i> Servis
                                        </a>
                                        <a href="hapus_kendaraan.php?id=<?= $id_k ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-sm table-hover border-top">
                                        <thead class="small text-muted">
                                            <tr>
                                                <th>TANGGAL</th>
                                                <th>ODO (KM)</th>
                                                <th>KETERANGAN</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $q_servis = mysqli_query($conn, "SELECT * FROM riwayat_servis WHERE id_kendaraan = '$id_k' ORDER BY tgl_servis DESC");
                                            while($s = mysqli_fetch_assoc($q_servis)):
                                            ?>
                                                <tr class="small">
                                                    <td><?= date('d/m/Y', strtotime($s['tgl_servis'])) ?></td>
                                                    <td class="fw-bold text-danger"><?= number_format($s['km_sekarang']) ?></td>
                                                    <td class="text-secondary"><?= $s['keterangan'] ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
document.getElementById('cariPelanggan').addEventListener('input', function() {
    let keyword = this.value.toLowerCase();
    document.querySelectorAll('.user-item').forEach(function(item) {
        let content = item.innerText.toLowerCase();
        item.style.display = content.includes(keyword) ? 'block' : 'none';
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
