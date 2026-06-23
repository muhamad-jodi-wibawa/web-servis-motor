<?php
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

// ... Sisa kode query bawaan monitoring.php Anda ke bawah jangan dihapus ...
?>

if (isset($_GET['id'])) {
    // Gunakan mysqli_real_escape_string untuk keamanan
    $id_kendaraan = mysqli_real_escape_string($conn, $_GET['id']);

    // 1. Hapus riwayat servis (Foreign Key)
    mysqli_query($conn, "DELETE FROM riwayat_servis WHERE id_kendaraan = '$id_kendaraan'");

    // 2. Hapus data kendaraan
    $hapus = mysqli_query($conn, "DELETE FROM kendaraan WHERE id_kendaraan = '$id_kendaraan'");

    if ($hapus) {
        // Gunakan header location agar browser langsung merefresh query di dashboard_sa.php
        header("Location: dashboard_sa.php?status=sukses");
        exit();
    } else {
        header("Location: dashboard_sa.php?status=gagal");
        exit();
    }
} else {
    header("Location: dashboard_sa.php");
}
?>
