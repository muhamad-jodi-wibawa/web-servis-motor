<?php
include 'config/koneksi.php';
include 'config/kirim_wa.php';

// 1. Ambil tanggal hari ini atau tanggal H-3 (Tergantung selera Anda)
// Contoh: Mencari yang tanggal prediksinya cocok dengan HARI INI
$hari_ini = date('d F Y'); 

// 2. Cari di database kendaraan yang tanggal prediksinya mengandung tanggal hari ini
// (Sesuaikan nama tabel dan kolom sesuai database Anda, ini contoh ilustrasi)
$sql = "SELECT pelanggan.nama, pelanggan.no_hp, kendaraan.nama_motor, prediksi.tgl_prediksi 
        FROM prediksi 
        JOIN kendaraan ON prediksi.id_kendaraan = kendaraan.id_kendaraan
        JOIN pelanggan ON kendaraan.id_pelanggan = pelanggan.id_pelanggan
        WHERE prediksi.tgl_prediksi LIKE '%$hari_ini%'";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $nama_pemilik = $row['nama'];
        $no_wa        = $row['no_hp'];
        $motor        = $row['nama_motor'];
        
        // 3. Susun template pesan pengingat manis
        $pesan = "Halo Kak *".$nama_pemilik."*,\n\n";
        $pesan .= "Ini adalah pengingat otomatis dari Sistem Servis. Berdasarkan riwayat mobilitas Anda, motor *".$motor."* kesayangan Anda sudah memasuki *Waktunya Servis* berkala pada hari ini (".$hari_ini.").\n\n";
        $pesan .= "Yuk, segera jadwalkan kunjungan Anda ke bengkel agar performa mesin tetap terjaga dan garansi tetap aman! ✨";

        // 4. Eksekusi kirim via Fonnte
        kirimWhatsApp($no_wa, $pesan);
    }
    echo "Sukses mengirim pengingat untuk hari ini.";
} else {
    echo "Tidak ada jadwal servis untuk hari ini.";
}
?>