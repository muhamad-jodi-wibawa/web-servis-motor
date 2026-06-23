<?php
// Matikan error reporting bawaan agar kita bisa handle manual jika internet putus
mysqli_report(MYSQLI_REPORT_OFF); 

$host     = 'mysql-e3947a7-servis123.h.aivencloud.com';
$user     = 'avnadmin';
$password = 'AVNS_kVsI2oeq__BGJzsqmZ1';
$database = 'defaultdb'; 
$port     = 22887;

// Hubungkan ke MySQL Aiven dengan mengaktifkan enkripsi SSL (Wajib untuk Aiven)
$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL); 
$sukses = mysqli_real_connect($conn, $host, $user, $password, $database, $port, NULL, MYSQLI_CLIENT_SSL);

if (!$sukses) {
    die("Koneksi ke Database Cloud Gagal: " . mysqli_connect_error());
}
?>