<?php
function hitungDetailPrediksi($id_kendaraan, $conn) {
    // 1. Ambil data riwayat servis
    $sql = "SELECT tgl_servis, km_sekarang FROM riwayat_servis 
            WHERE id_kendaraan = '$id_kendaraan' ORDER BY tgl_servis ASC";
    $result = mysqli_query($conn, $sql);
    
    $data_x = []; 
    $data_y = []; 
    $tgl_awal = null;

    if (mysqli_num_rows($result) < 2) {
        return "Data belum cukup (Min. 2 Riwayat)";
    }

    while ($row = mysqli_fetch_assoc($result)) {
        if ($tgl_awal === null) {
            $tgl_awal = strtotime($row['tgl_servis']);
            $data_x[] = 0; 
        } else {
            $selisih_hari = (strtotime($row['tgl_servis']) - $tgl_awal) / (60 * 60 * 24);
            $data_x[] = $selisih_hari;
        }
        $data_y[] = (float)$row['km_sekarang']; 
    }

    $n = count($data_x);
    $sum_x = array_sum($data_x);
    $sum_y = array_sum($data_y);
    $sum_xy = 0; $sum_x2 = 0;

    for ($i = 0; $i < $n; $i++) {
        $sum_xy += ($data_x[$i] * $data_y[$i]);
        $sum_x2 += ($data_x[$i] ** 2);
    }

    // Hitung Denominator (Penyebut)
    $denominator = ($n * $sum_x2 - ($sum_x ** 2));
    if ($denominator == 0) return "Data tidak valid (KM/Tanggal Sama)";
    
    // b = Nilai kemiringan (KM per hari)
    $b = ($n * $sum_xy - ($sum_x * $sum_y)) / $denominator;
    // a = Konstanta (KM awal)
    $a = ($sum_y - ($b * $sum_x)) / $n;

    $km_terakhir = end($data_y);
    $target_km = $km_terakhir + 2200; 

    // --- LOGIC SAFETY GUARD (Mencegah Tahun Raksasa) ---
    
    // Jika mobilitas negatif atau nol (aneh), set minimal 1 KM per hari
    if ($b <= 0) {
        $b = 1; 
    }

    $hari_prediksi = ($target_km - $a) / $b;
    $tgl_sekarang = time();
    $selisih_hari_dari_awal = ($tgl_sekarang - $tgl_awal) / (60 * 60 * 24);

    // Hitung berapa hari lagi dari HARI INI
    $sisa_hari = $hari_prediksi - $selisih_hari_dari_awal;

    // JIKA SISA HARI TERLALU JAUH (Lebih dari 1 tahun / 365 hari)
    // Atau mobilitas terlalu rendah, kita batasi agar tidak error tahunnya.
    if ($sisa_hari > 365 || $b < 0.5) {
        $tgl_hasil = date('d F Y', strtotime("+180 days")); // Default 6 bulan ke depan
        $pesan_tambahan = " (Estimasi Berkala)";
    } 
    // JIKA SISA HARI SUDAH LEWAT (Motor harusnya sudah servis)
    elseif ($sisa_hari < 0) {
        $tgl_hasil = date('d F Y'); // Set hari ini
        $pesan_tambahan = " (Segera Servis!)";
    }
    else {
        $tgl_hasil = date('d F Y', strtotime("+" . round($hari_prediksi) . " days", $tgl_awal));
        $pesan_tambahan = "";
    }
    // --------------------------------------------------

    return [
        'tgl_prediksi' => $tgl_hasil . $pesan_tambahan,
        'a'            => round($a, 2),
        'b'            => round($b, 2), // Ini yang muncul di box "Mobilitas"
        'n'            => $n,
        'target_km'    => $target_km,
        'km_terakhir'  => $km_terakhir
    ];
}