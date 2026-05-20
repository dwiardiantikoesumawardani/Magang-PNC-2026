<?php
// File: cek_alert.php
include 'koneksi.php';

$query = mysqli_query($conn, "SELECT * FROM behavior_alerts WHERE status = 'Unread' ORDER BY id ASC LIMIT 1");
$response = ['ada_notifikasi' => false, 'pesan' => '', 'tipe' => ''];

if(mysqli_num_rows($query) > 0) {
    $row = mysqli_fetch_assoc($query);
    $response['ada_notifikasi'] = true;
    $response['pesan'] = $row['keterangan'];
    $response['tipe'] = $row['tipe_alert'];
    
    // Langsung ubah jadi Read agar tidak muncul berulang-ulang
    mysqli_query($conn, "UPDATE behavior_alerts SET status = 'Read' WHERE id = {$row['id']}");
}

header('Content-Type: application/json');
echo json_encode($response);
?>