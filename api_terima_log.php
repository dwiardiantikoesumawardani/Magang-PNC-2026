<?php
// File: api_terima_log.php
// Fungsi: Menerima request HTTP GET dari MikroTik dan memasukkannya ke tabel 'logs'

include 'koneksi.php';

// Pastikan zona waktu sesuai
date_default_timezone_set('Asia/Jakarta');
$waktu_sekarang = date('Y-m-d H:i:s');

// 1. Tangkap data yang dikirim oleh MikroTik (Script Winbox)
$mac_address = isset($_GET['mac']) ? mysqli_real_escape_string($conn, $_GET['mac']) : '';
$ip_address  = isset($_GET['ip']) ? mysqli_real_escape_string($conn, $_GET['ip']) : '';
$bytes       = isset($_GET['bytes']) ? (int)$_GET['bytes'] : 0;
$kategori    = isset($_GET['kategori']) ? mysqli_real_escape_string($conn, $_GET['kategori']) : 'UNKNOWN';
$url         = isset($_GET['url']) ? mysqli_real_escape_string($conn, $_GET['url']) : 'Tidak Diketahui';

// 2. Validasi sederhana agar tidak ada data kosong yang masuk
if (!empty($mac_address) && !empty($ip_address)) {
    
    // Cek apakah perangkat punya Hostname (Opsional, jika tabel Anda punya relasi ke tabel profil)
    $hostname = "Perangkat_".$ip_address; // Nilai default

    // 3. Masukkan data ke tabel 'logs' utama
    // CATATAN: Sesuaikan nama kolom dengan struktur tabel 'logs' di database Anda
    $sql = "INSERT INTO logs (waktu, mac_address, ip_address, hostname, kategori, bytes_download, url_akses, status_alert_processed) 
            VALUES ('$waktu_sekarang', '$mac_address', '$ip_address', '$hostname', '$kategori', '$bytes', '$url', 0)";
            
    if (mysqli_query($conn, $sql)) {
        echo "Sukses: Data log behavior berhasil dicatat.";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    echo "Ditolak: MAC Address atau IP kosong.";
}
?>