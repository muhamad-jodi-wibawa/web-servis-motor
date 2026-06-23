<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'sa') {
    header("Location: ../login.php");
    exit();
}

$total_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM users WHERE role = 'user'"))['t'];
$total_ken  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM kendaraan"))['t'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SA Dashboard - ServisKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        /* Background Nuansa Bengkel */
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
                        url('https://images.unsplash.com/photo-1486262715619-67b85e0b08d3?q=80&w=2000&auto=format&fit=crop');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .card-stats { 
            border: none; 
            border-radius: 15px; 
            transition: transform 0.3s;
            backdrop-filter: blur(5px); /* Efek kaca transparan */
        }
        
        .card-stats:hover { transform: translateY(-5px); }
        
        /* Warna Gradasi yang lebih bold/industrial */
        .bg-gradient-primary { background: linear-gradient(45deg, #2c3e50, #000000); }
        .bg-gradient-success { background: linear-gradient(45deg, #1e5128, #4e944f); }
        
        .menu-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
        }

        .navbar {
            background-color: rgba(33, 37, 41, 0.9) !important;
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark mb-4 shadow">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#"><i class="bi bi-gear-fill"></i> Admin ServisKu</a>
        <div class="d-flex align-items-center ps-3">
            <span class="navbar-text text-white me-3 small">SA: <strong><?= $_SESSION['nama'] ?></strong></span>
            <a href="../logout.php" class="btn btn-outline-danger btn-sm">Keluar</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card card-stats bg-gradient-primary text-white shadow-lg">
                <div class="card-body d-flex justify-content-between align-items-center p-4">
                    <div>
                        <h6 class="text-uppercase small fw-bold opacity-75">Total Pelanggan</h6>
                        <h2 class="fw-bold mb-0"><?= $total_user ?></h2>
                    </div>
                    <i class="bi bi-people-fill fs-1 opacity-25"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-stats bg-gradient-success text-white shadow-lg">
                <div class="card-body d-flex justify-content-between align-items-center p-4">
                    <div>
                        <h6 class="text-uppercase small fw-bold opacity-75">Kendaraan Terdaftar</h6>
                        <h2 class="fw-bold mb-0"><?= $total_ken ?></h2>
                    </div>
                    <i class="bi bi-tools fs-1 opacity-25"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card menu-card border-0 shadow-lg">
        <div class="card-body p-5">
            <div class="text-center mb-5">
                <h4 class="fw-bold text-dark">Panel Kontrol Bengkel</h4>
                <div class="mx-auto bg-primary" style="height: 3px; width: 60px;"></div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <a href="tambah_kendaraan.php" class="btn btn-dark w-100 py-4 shadow fw-bold border-0">
                        <i class="bi bi-plus-circle fs-3 d-block mb-2"></i> Tambah Kendaraan
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="input_servis.php" class="btn btn-success w-100 py-4 shadow fw-bold border-0">
                        <i class="bi bi-clipboard-check fs-3 d-block mb-2"></i> Input Servis Baru
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="monitoring.php" class="btn btn-primary w-100 py-4 shadow fw-bold border-0">
                        <i class="bi bi-display fs-3 d-block mb-2"></i> Pemantauan Data
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-5">
        <p class="text-white-50 small">&copy; 2026 ServisKu System - Workshop Management</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>