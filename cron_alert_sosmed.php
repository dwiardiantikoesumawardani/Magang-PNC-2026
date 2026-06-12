<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

file_put_contents(
    __DIR__ . "/cron_test.txt",
    date("Y-m-d H:i:s") . " - CRON BERJALAN" . PHP_EOL,
    FILE_APPEND
);

require_once __DIR__ . '/koneksi.php';

$sql = "
SELECT *
FROM sosmed_sessions
WHERE status='OPEN'
AND alert_triggered=0
AND TIMESTAMPDIFF(MINUTE,start_time,NOW()) >= 20
";

$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {

    $id       = (int)$row['id'];
    $mac      = mysqli_real_escape_string($conn, $row['mac_address']);
    $ip       = mysqli_real_escape_string($conn, $row['ip_address']);
    $hostname = mysqli_real_escape_string($conn, $row['hostname']);
    $platform = mysqli_real_escape_string($conn, $row['platform']);

    mysqli_query(
        $conn,
        "INSERT INTO logs (mac_address, ip_address, hostname, resolve_method, dst_address, bytes_download, kategori, url_akses, waktu) VALUES ('$mac', '$ip', '$hostname', 'SESSION', '$platform', 0, 'SOSMED', 'AKSES_20_MENIT', NOW())"
    );

    mysqli_query(
        $conn,
        "UPDATE sosmed_sessions
         SET alert_triggered=1
         WHERE id=$id"
    );
}

echo "OK";