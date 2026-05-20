<?php
// File: cron_behavior.php
include 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');
$waktu_sekarang = date('Y-m-d H:i:s');

// Batas toleransi download (100MB)
$BATAS_DOWNLOAD = 104857600; 

$sql = "SELECT id, mac_address, ip_address, bytes_download, url_akses 
        FROM logs 
        WHERE bytes_download >= $BATAS_DOWNLOAD 
        AND status_alert_processed = 0";

$query = mysqli_query($conn, $sql);

while($row = mysqli_fetch_assoc($query)) {
    $id_log = $row['id'];
    $mac = $row['mac_address'];
    $ip = $row['ip_address'];
    $url = $row['url_akses'];
    $ukuran_mb = round($row['bytes_download'] / (1024 * 1024), 2);

    // Ambil ekstensi file (misal: .exe)
    $path = parse_url($url, PHP_URL_PATH); 
    $ext = pathinfo($path, PATHINFO_EXTENSION); 
    $ext_display = empty($ext) ? "File/Stream" : "." . strtoupper($ext);

    // Susun pesan notifikasi
    $tipe_alert = "Download Besar ($ext_display)";
    $pesan = "Pengguna dengan IP <b>$ip</b> ($mac) terdeteksi mengunduh file berekstensi <b>$ext_display</b> " .
             "sebesar <b>$ukuran_mb MB</b>.<br><small>Sumber: $url</small>";
    
    // Masukkan ke tabel alert
    mysqli_query($conn, "INSERT INTO behavior_alerts (mac_address, tipe_alert, keterangan, waktu, status) 
                         VALUES ('$mac', '$tipe_alert', '$pesan', '$waktu_sekarang', 'Unread')");
                         
    // Tandai log agar tidak dideteksi ulang
    mysqli_query($conn, "UPDATE logs SET status_alert_processed = 1 WHERE id = $id_log");
}

echo "Proses pengecekan kelakuan jaringan selesai.";
?>