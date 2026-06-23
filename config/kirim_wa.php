<?php
function kirimWhatsApp($nomor_tujuan, $pesan) {
    // PASTE APP TOKEN DARI DASHBOARD FONNTE ANDA DI SINI
    $token = "KkM4ycpSDakcGRiSCBNm"; 

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://api.fonnte.com/send',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => array(
        'target' => $nomor_tujuan,
        'message' => $pesan,
        'countryCode' => '62', // Otomatis konversi nomor Indonesia
      ),
      CURLOPT_HTTPHEADER => array(
        "Authorization: $token"
      ),
    ));

    $response = curl_stream_context_create_or_get($curl); // Mengeksekusi kirim pesan
    $response = curl_exec($curl);
    curl_close($curl);
    
    return $response;
}
?>