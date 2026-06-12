<?php
require_once __DIR__ . '/koneksi.php';

$sql = "
UPDATE sosmed_sessions
SET status='CLOSED'
WHERE status='OPEN'
AND TIMESTAMPDIFF(MINUTE, last_hit, NOW()) >= 5
";

mysqli_query($conn, $sql);

echo "OK";