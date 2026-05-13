<?php
include 'koneksi.php';
require('routeros_api.class.php');

$api = new RouterosAPI();
// $api->debug = true; // Dinonaktifkan karena koneksi sudah berhasil

// KONFIGURASI MIKROTIK
$ip_mikrotik = '192.168.88.1'; // Ganti dengan IP Routermu
$user_mikrotik = 'admin';      // Ganti dengan User Winbox
$pass_mikrotik = '';           // Ganti dengan Password Winbox

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $mac = $_GET['mac'];
    $ip_user = $_GET['ip'];
    $id_log = $_GET['id'];

    // 1. Coba Hubungkan ke MikroTik
    if ($api->connect($ip_mikrotik, $user_mikrotik, $pass_mikrotik)) {
        
        if ($action == 'block') {
            // --- EKSEKUSI BLOKIR ---
            $api->comm("/ip/firewall/filter/add", array(
                "chain" => "forward",
                "src-mac-address" => $mac,
                "action" => "drop",
                "comment" => "BLOKIR-OTOMATIS-NETMONITOR-$mac",
                "place-before" => "0" 
            ));

            // Update status di Database menjadi 'Resolved'
            mysqli_query($conn, "UPDATE logs SET status='Resolved' WHERE id='$id_log'");
            $msg = "Perangkat Berhasil Diblokir di MikroTik!";
            
        } else if ($action == 'unblock') {
            // --- EKSEKUSI BUKA BLOKIR ---
            // Cari aturan blokir di firewall berdasarkan komentar MAC
            $getRules = $api->comm("/ip/firewall/filter/print", array(
                "?comment" => "BLOKIR-OTOMATIS-NETMONITOR-$mac"
            ));

            // Jika aturan ditemukan, hapus
            if (!empty($getRules)) {
                foreach ($getRules as $rule) {
                    $api->comm("/ip/firewall/filter/remove", array(
                        ".id" => $rule['.id']
                    ));
                }
            }

            // Kembalikan status di Database menjadi 'Pending'
            mysqli_query($conn, "UPDATE logs SET status='Pending' WHERE id='$id_log'");
            $msg = "Blokir Perangkat Berhasil Dibuka!";
        }

        $api->disconnect();

        // 4. Redirect kembali ke detail dengan pesan sukses
        echo "<script>alert('$msg'); window.location='detail_insiden.php?id=$id_log';</script>";
        
    } else {
        // Jika koneksi ke MikroTik gagal
        echo "<script>alert('Gagal Terhubung ke MikroTik! Pastikan API (Port 8728) Aktif.'); window.history.back();</script>";
    }
} else {
    header("Location: index.php");
}
?>