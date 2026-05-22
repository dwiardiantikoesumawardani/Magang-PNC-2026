<?php
// File: cron_behavior.php
include 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');

// =========================================================================
// BAGIAN 1: ANALISIS DETEKSI SOSIAL MEDIA (BERDASARKAN DURASI KONTINYU)
// =========================================================================
$batas_menit_sosmed = 1; // Peringatan muncul jika sosmed aktif selama 20 menit tanpa putus

$query_sosmed = mysqli_query($conn, "SELECT mac_address, waktu FROM logs WHERE kategori = 'SOSMED' AND DATE(waktu) = CURDATE() ORDER BY id ASC");

while ($log = mysqli_fetch_assoc($query_sosmed)) {
    $mac = $log['mac_address'];
    $waktu_log = $log['waktu'];

    // Cek apakah user ini sudah punya sesi sosmed yang aktif dalam 5 menit terakhir
    $cek_sesi = mysqli_query($conn, "SELECT * FROM sosmed_sessions WHERE mac_address = '$mac' AND last_hit >= DATE_SUB('$waktu_log', INTERVAL 5 MINUTE) AND DATE(start_time) = CURDATE() LIMIT 1");
    
    if (mysqli_num_rows($cek_sesi) > 0) {
        // JIKA ADA: Perbarui waktu keaktifan terakhir
        $sesi = mysqli_fetch_assoc($cek_sesi);
        $id_sesi = $sesi['id'];
        mysqli_query($conn, "UPDATE sosmed_sessions SET last_hit = '$waktu_log' WHERE id = $id_sesi");

        // Hitung durasi online kontinyu
        $waktu_mulai = new DateTime($sesi['start_time']);
        $waktu_sekarang = new DateTime($waktu_log);
        $durasi = $waktu_mulai->diff($waktu_sekarang);
        $total_menit = ($durasi->days * 24 * 60) + ($durasi->h * 60) + $durasi->i;

        // Jika melewati batas durasi dan belum pernah memicu alert di sesi ini
        if ($total_menit >= $batas_menit_sosmed && $sesi['alert_triggered'] == 0) {
            $tipe_alert = "Sosmed Overtime";
            $keterangan = "User terdeteksi mengakses Sosial Media (TikTok/IG) secara kontinyu selama <b>" . $total_menit . " menit</b> pada jam kerja.";

            mysqli_query($conn, "INSERT INTO behavior_alerts (waktu, mac_address, tipe_alert, keterangan) VALUES ('$waktu_log', '$mac', '$tipe_alert', '$keterangan')");
            mysqli_query($conn, "UPDATE sosmed_sessions SET alert_triggered = 1 WHERE id = $id_sesi");
        }
    } else {
        // JIKA TIDAK ADA: Buat sesi sosmed baru untuk perangkat ini (Perbaikan: variabel menggunakan $waktu_log)
        mysqli_query($conn, "INSERT INTO sosmed_sessions (mac_address, start_time, last_hit, alert_triggered) VALUES ('$mac', '$waktu_log', '$waktu_log', 0)");
    }
}


// =========================================================================
// BAGIAN 2: ANALISIS DETEKSI DOWNLOAD BESAR (BERDASARKAN UKURAN BANDWIDTH)
// =========================================================================
// Batas dinaikkan menjadi 3 GB (3072 MB) agar tidak mudah False Positive di lingkungan anak IT
$batas_download_mb = 3072; 

// Perhatikan perubahan 'ukuran_file' menjadi 'bytes_download' di sini
$query_download = mysqli_query($conn, "SELECT mac_address, SUM(bytes_download) as total_bytes, MAX(waktu) as waktu_terakhir FROM logs WHERE kategori = 'DOWNLOAD' AND DATE(waktu) = CURDATE() GROUP BY mac_address");

while ($dl = mysqli_fetch_assoc($query_download)) {
    $total_mb = $dl['total_bytes'] / (1024 * 1024); // Konversi dari bytes ke Megabytes

    if ($total_mb >= $batas_download_mb) {
        $mac_dl = $dl['mac_address'];
        $waktu_dl = $dl['waktu_terakhir'];
        $ukuran_gb = round($total_mb / 1024, 2);
        
        // Cek agar sistem tidak mengirim alert "Download Besar" berkali-kali untuk orang yang sama di hari yang sama
        $cek_alert_dl = mysqli_query($conn, "SELECT * FROM behavior_alerts WHERE mac_address = '$mac_dl' AND tipe_alert = 'Download Besar' AND DATE(waktu) = CURDATE()");
        
        if (mysqli_num_rows($cek_alert_dl) == 0) {
            $tipe_alert_dl = "Download Besar";
            $keterangan_dl = "User terdeteksi melakukan aktivitas unduhan besar hari ini dengan akumulasi total sebesar <b>" . $ukuran_gb . " GB</b>.";
            
            mysqli_query($conn, "INSERT INTO behavior_alerts (waktu, mac_address, tipe_alert, keterangan) VALUES ('$waktu_dl', '$mac_dl', '$tipe_alert_dl', '$keterangan_dl')");
        }
    }
}

echo "Analisis sinkronisasi User Behavior (Sosmed & Download) sukses dijalankan.";
?>