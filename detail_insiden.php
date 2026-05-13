<?php
include 'koneksi.php';

// SET WAKTU KE WIB (Asia/Jakarta)
date_default_timezone_set('Asia/Jakarta');

// Ambil ID dari URL
if(!isset($_GET['id'])) { header("Location: index.php"); exit; }
$id = $_GET['id'];

// Query data spesifik
$query = mysqli_query($conn, "SELECT * FROM logs WHERE id='$id'");
$data = mysqli_fetch_assoc($query);

if(!$data) { echo "Data tidak ditemukan!"; exit; }

// Logika Penjelasan & Mitigasi
$penjelasan = "";
$mitigasi = "";
$mikrotik_script = ""; 

if($data['kategori'] == 'VPN') {
    $penjelasan = "Pengguna terdeteksi menggunakan protokol enkripsi (VPN) untuk menyembunyikan identitas trafik atau mencoba melewati filter keamanan jaringan.";
    $mitigasi = "1. Blokir port standar VPN.<br>2. Gunakan Address List untuk isolasi IP.<br>3. Terapkan kebijakan Drop pada Raw Firewall.";
    
    // Script Manual CLI
    $mikrotik_script = "/ip firewall address-list add address=" . $data['ip_address'] . " list=Isolasi-VPN comment=\"Blokir VPN: " . $data['hostname'] . "\"";
} else {
    $penjelasan = "Sistem mendeteksi upaya akses menuju domain atau alamat IP yang terdaftar dalam blacklist perjudian online (JUDOL).";
    $mitigasi = "1. Redirect DNS ke halaman isolasi.<br>2. Masukkan MAC Address ke daftar blokir permanen.<br>3. Lakukan pembersihan cache browser pada perangkat user.";
    
    // Script Manual CLI
    $mikrotik_script = "/ip firewall filter add chain=forward src-mac-address=" . $data['mac_address'] . " action=drop comment=\"Blokir Judol: " . $data['hostname'] . "\"";
}

// Waktu sekarang dalam format WIB
$last_update = date('d M Y | H:i:s');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Detail Analisis - NetMonitor</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .container-fluid { padding-top: 10px !important; }
        .card-body { padding: 1.25rem !important; }
        .topbar { height: 3.5rem !important; margin-bottom: 1rem !important; }
        .table-detail td { padding: 8px 0; border-bottom: 1px solid #f1f1f1; }
        code { font-size: 110%; }
        /* Animasi tambahan untuk tombol bahaya */
        .animate-pulse { animation: pulse 2s infinite; }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                
                <nav class="navbar navbar-expand navbar-light bg-white topbar static-top shadow d-flex align-items-center justify-content-between">
                    <div class="ml-3">
                        <a href="index.php" class="btn btn-sm btn-primary shadow-sm mr-2">
                            <i class="fas fa-arrow-left fa-sm"></i> Dashboard
                        </a>
                        <span class="h5 mb-0 text-gray-800 d-none d-md-inline-block">Detail Analisis Insiden</span>
                    </div>
                    <div class="mr-3 text-muted small font-weight-bold">
                        <i class="fas fa-clock"></i> Last Update: <?= $last_update; ?> (WIB)
                    </div>
                </nav>

                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-2 bg-dark text-white">
                                    <h6 class="m-0 font-weight-bold"><i class="fas fa-laptop"></i> Informasi Perangkat</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless table-detail">
                                        <tr><td width="40%"><strong>Status Log</strong></td><td>: 
                                            <span class="badge badge-<?= ($data['status']=='Resolved')?'success':'secondary'; ?>"><?= $data['status']; ?></span>
                                        </td></tr>
                                        <tr><td><strong>Kategori</strong></td><td>: 
                                            <span class="badge badge-<?= ($data['kategori']=='VPN')?'warning':'danger'; ?>"><?= $data['kategori']; ?></span>
                                        </td></tr>
                                        <tr><td><strong>Waktu Log</strong></td><td>: <?= $data['waktu']; ?></td></tr>
                                        <tr><td><strong>IP Address</strong></td><td>: <code class="text-primary"><?= $data['ip_address']; ?></code></td></tr>
                                        <tr><td><strong>MAC Address</strong></td><td>: <code><?= $data['mac_address']; ?></code></td></tr>
                                        <tr><td><strong>Hostname</strong></td><td>: <span class="text-dark font-weight-bold"><?= $data['hostname'] ?: '-'; ?></span></td></tr>
                                        <tr><td><strong>Tujuan/Domain</strong></td><td>: <span class="text-danger small"><?= $data['dst_address']; ?></span></td></tr>
                                    </table>
                                    
                                    <div class="mt-4">
                                        <?php if($data['status'] == 'Pending'): ?>
                                            <a href="mikrotik_action.php?action=block&mac=<?= $data['mac_address']; ?>&ip=<?= $data['ip_address']; ?>&id=<?= $data['id']; ?>" 
                                               class="btn btn-danger btn-block btn-lg shadow animate-pulse" 
                                               onclick="return confirm('Sistem akan mengirim perintah blokir ke MikroTik secara otomatis. Lanjutkan?')">
                                                <i class="fas fa-shield-alt"></i> EKSEKUSI BLOKIR SEKARANG
                                            </a>
                                            <p class="text-center small text-muted mt-2 font-italic">*Perangkat akan diputus koneksinya melalui API MikroTik</p>
                                        <?php else: ?>
                                            <div class="alert alert-success text-center mb-3 shadow-sm">
                                                <i class="fas fa-check-circle"></i> Perangkat sedang dalam kondisi terblokir.
                                            </div>
                                            <a href="mikrotik_action.php?action=unblock&mac=<?= $data['mac_address']; ?>&ip=<?= $data['ip_address']; ?>&id=<?= $data['id']; ?>" 
                                               class="btn btn-success btn-block btn-lg shadow" 
                                               onclick="return confirm('Apakah Anda yakin ingin membuka kembali akses internet untuk perangkat ini?')">
                                                <i class="fas fa-unlock"></i> BUKA BLOKIR (UNBLOCK)
                                            </a>
                                            <p class="text-center small text-muted mt-2 font-italic">*Aturan blokir pada perangkat ini akan dihapus dari MikroTik</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7">
                            <div class="card shadow mb-4 border-left-success">
                                <div class="card-header py-2 bg-success text-white">
                                    <h6 class="m-0 font-weight-bold"><i class="fas fa-microchip"></i> Analisis & Mitigasi Manual</h6>
                                </div>
                                <div class="card-body">
                                    <h6 class="font-weight-bold text-primary">Deskripsi Kejadian:</h6>
                                    <p class="text-gray-800"><?= $penjelasan; ?></p>
                                    
                                    <hr>
                                    
                                    <h6 class="font-weight-bold text-primary">Rencana Tindakan (Manual):</h6>
                                    <div class="alert alert-light border shadow-sm">
                                        <?= $mitigasi; ?>
                                    </div>

                                    <hr>
                                    
                                    <h6 class="font-weight-bold text-primary">MikroTik Command Line (CLI):</h6>
                                    <p class="small text-muted">Gunakan perintah ini jika ingin eksekusi manual via Terminal Winbox:</p>
                                    <div class="bg-dark p-3 rounded position-relative">
                                        <code id="mikrotikScript" class="text-white small"><?= $mikrotik_script; ?></code>
                                    </div>
                                    <button class="btn btn-sm btn-outline-dark mt-2" onclick="copyToClipboard()">
                                        <i class="fas fa-copy"></i> Salin Perintah CLI
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> 
            </div>
        </div>
    </div>

    <script>
    function copyToClipboard() {
        var copyText = document.getElementById("mikrotikScript").innerText;
        var textArea = document.createElement("textarea");
        textArea.value = copyText;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            alert("Script CLI berhasil disalin!");
        } catch (err) {
            alert("Gagal menyalin script.");
        }
        document.body.removeChild(textArea);
    }
    </script>
</body>
</html>