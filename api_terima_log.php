<?php
include 'koneksi.php';

$mac      = trim($_REQUEST['mac'] ?? '');
$ip       = trim($_REQUEST['ip'] ?? '');
$hostname = trim($_REQUEST['hostname'] ?? ($_REQUEST['host'] ?? ''));
$bytes    = (int)($_REQUEST['bytes'] ?? 0);
$kategori = trim($_REQUEST['kategori'] ?? '');
$url      = trim($_REQUEST['url'] ?? '');
$resolve_method = trim($_REQUEST['resolve_method'] ?? '');
$dst_address = trim($_REQUEST['dst'] ?? '');

if ($mac === '' || $ip === '' || $kategori === '') {
    http_response_code(400);
    exit("ERROR: Data tidak lengkap");
}

$mac      = mysqli_real_escape_string($conn, $mac);
$ip       = mysqli_real_escape_string($conn, $ip);
$hostname = mysqli_real_escape_string($conn, $hostname);
$kategori = mysqli_real_escape_string($conn, $kategori);
$url      = mysqli_real_escape_string($conn, $url);
$resolve_method = mysqli_real_escape_string($conn, $resolve_method);
$dst_address = mysqli_real_escape_string($conn, $dst_address);

$sql = "INSERT INTO logs (mac_address, ip_address, hostname, resolve_method, dst_address, bytes_download, kategori, url_akses, waktu) VALUES ('$mac', '$ip', '$hostname', '$resolve_method', '$dst_address', '$bytes', '$kategori', '$url', NOW())";

if (!mysqli_query($conn, $sql)) {
    http_response_code(500);
    exit("ERROR DATABASE: " . mysqli_error($conn));
}

if (strtoupper($kategori) === 'SOSMED') {

    file_put_contents(
        'debug_sosmed.txt',
        date('Y-m-d H:i:s') .
        " | MASUK BLOK SOSMED | MAC=$mac | DST=$dst_address\n",
        FILE_APPEND
    );

    $platform = 'UNKNOWN';

    $dstLower = strtolower($dst_address);

    if (
    strpos($dstLower,'instagram') !== false ||
    strpos($dstLower,'cdninstagram') !== false
    ) {
        $platform='INSTAGRAM';
    }
    elseif (
        strpos($dstLower,'facebook') !== false ||
        strpos($dstLower,'fbcdn') !== false
    ) {
        $platform='FACEBOOK';
    }
    elseif (
        strpos($dstLower,'tiktok') !== false ||
        strpos($dstLower,'tiktokv') !== false
    ) {
        $platform='TIKTOK';
    }

    $cekSession = mysqli_query(
        $conn,
        "SELECT *
         FROM sosmed_sessions
         WHERE mac_address='$mac'
         AND platform='$platform'
         AND status='OPEN'
         LIMIT 1"
    );

    if ($rowSession = mysqli_fetch_assoc($cekSession)) {

        $idSession = (int)$rowSession['id'];

        mysqli_query(
            $conn,
            "UPDATE sosmed_sessions
             SET
                last_hit = NOW(),
                hit_count = hit_count + 1
             WHERE id = $idSession"
        );

    } else {

        $resultInsert = mysqli_query(
    $conn,
    "INSERT INTO sosmed_sessions ( mac_address, ip_address, hostname, platform, start_time, last_hit, hit_count, alert_triggered, status) VALUES ('$mac', '$ip', '$hostname', '$platform', NOW(), NOW(), 1, 0, 'OPEN')"
);

if (!$resultInsert) {

    file_put_contents(
        'debug_sosmed.txt',
        date('Y-m-d H:i:s') .
        " | INSERT ERROR | " .
        mysqli_error($conn) . "\n",
        FILE_APPEND
    );

}
    }
}

echo "OK";