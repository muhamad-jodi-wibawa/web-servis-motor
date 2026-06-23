<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'sa') {
    header("Location: ../login.php");
    exit();
}

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