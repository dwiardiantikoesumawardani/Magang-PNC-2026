<?php
include 'koneksi.php';

$ip         = $_POST['ip'];
$mac        = $_POST['mac'];
$host       = $_POST['host'];
$kategori   = $_POST['kategori'];
$dst        = isset($_POST['dst']) ? $_POST['dst'] : '-'; // Tangkap data domain/IP tujuan
$keterangan = isset($_POST['keterangan']) ? $_POST['keterangan'] : '';

if(!empty($ip)){
    $cek = mysqli_query($conn, "SELECT id, jumlah_deteksi, dst_address FROM logs 
                                WHERE ip_address = '$ip' 
                                AND kategori = '$kategori' 
                                AND waktu > DATE_SUB(NOW(), INTERVAL 15 MINUTE) 
                                ORDER BY id DESC LIMIT 1");

    if(mysqli_num_rows($cek) > 0){
        $data = mysqli_fetch_assoc($cek);
        $id_lama = $data['id'];
        $dst_lama = $data['dst_address'];
        
        // Cek agar domain yang sama tidak ditulis berulang kali
        if (strpos($dst_lama, $dst) === false && $dst != '-') {
            $dst_baru = $dst_lama . ", " . $dst;
        } else {
            $dst_baru = $dst_lama;
        }

        $sql = "UPDATE logs SET 
                waktu = NOW(), 
                jumlah_deteksi = jumlah_deteksi + 1,
                dst_address = '$dst_baru'
                WHERE id = $id_lama";
    } else {
        $sql = "INSERT INTO logs (ip_address, mac_address, hostname, kategori, keterangan, dst_address, waktu, jumlah_deteksi) 
                VALUES ('$ip', '$mac', '$host', '$kategori', '$keterangan', '$dst', NOW(), 1)";
    }
    mysqli_query($conn, $sql);
}
?>