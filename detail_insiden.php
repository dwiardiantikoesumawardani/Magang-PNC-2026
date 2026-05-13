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

if($data['kategori'] == 'VPN') {
    $penjelasan = "Pengguna terdeteksi menggunakan protokol enkripsi (VPN) untuk menyembunyikan identitas trafik atau mencoba melewati filter keamanan jaringan.";
    $mitigasi = "1. Blokir port standar VPN.<br>2. Gunakan Address List untuk isolasi IP.<br>3. Terapkan kebijakan Drop pada Raw Firewall.";
} else {
    $penjelasan = "Sistem mendeteksi upaya akses menuju domain atau alamat IP yang terdaftar dalam blacklist perjudian online (JUDOL).";
    $mitigasi = "1. Redirect DNS ke halaman isolasi.<br>2. Masukkan MAC Address ke daftar blokir permanen.<br>3. Lakukan pembersihan cache browser pada perangkat user.";
}

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
        /* Membuat container utama memenuhi layar */
        html, body, #wrapper, #content-wrapper, #content {
            height: 100%;
        }
        #content {
            display: flex;
            flex-direction: column;
        }
        /* Mengatur agar container fluid mengambil sisa ruang */
        .full-screen-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 15px !important;
            overflow-y: auto; /* Agar tetap bisa scroll jika konten overload */
        }
        .main-row {
            flex: 1;
        }
        .card {
            height: 100%; /* Membuat card memanjang ke bawah */
            display: flex;
            flex-direction: column;
        }
        .card-body {
            flex: 1;
        }
        .topbar { height: 3.5rem !important; }
        .table-detail td { padding: 12px 0; border-bottom: 1px solid #f8f9fc; }
        
        .animate-pulse { animation: pulse 2s infinite; }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.01); }
            100% { transform: scale(1); }
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <div id="content-wrapper" class="d-flex flex-column bg-light">
            <div id="content">
                
                <nav class="navbar navbar-expand navbar-light bg-white topbar static-top shadow d-flex align-items-center justify-content-between px-4">
                    <div>
                        <a href="index.php" class="btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-arrow-left fa-sm"></i> Dashboard
                        </a>
                        <span class="ml-3 h5 mb-0 text-gray-800 font-weight-bold">Analisis Insiden #<?= $id; ?></span>
                    </div>
                    <div class="text-muted small">
                        <i class="fas fa-clock"></i> <span class="font-weight-bold"><?= $last_update; ?> WIB</span>
                    </div>
                </nav>

                <div class="container-fluid full-screen-container">
                    <div class="row main-row">
                        
                        <div class="col-lg-4 mb-3 mb-lg-0">
                            <div class="card shadow border-0">
                                <div class="card-header py-3 bg-dark d-flex align-items-center">
                                    <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-laptop mr-2"></i> Detail Perangkat</h6>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <table class="table table-borderless table-detail mb-auto">
                                        <tr><td width="45%"><strong>Status Log</strong></td><td>: 
                                            <span class="badge badge-<?= ($data['status']=='Resolved')?'success':'secondary'; ?> px-3"><?= $data['status']; ?></span>
                                        </td></tr>
                                        <tr><td><strong>Kategori</strong></td><td>: 
                                            <span class="badge badge-<?= ($data['kategori']=='VPN')?'warning':'danger'; ?> px-3"><?= $data['kategori']; ?></span>
                                        </td></tr>
                                        <tr><td><strong>IP Address</strong></td><td>: <code class="text-primary h6"><?= $data['ip_address']; ?></code></td></tr>
                                        <tr><td><strong>MAC Address</strong></td><td>: <code><?= $data['mac_address']; ?></code></td></tr>
                                        <tr><td><strong>Hostname</strong></td><td>: <span class="text-dark font-weight-bold"><?= $data['hostname'] ?: '-'; ?></span></td></tr>
                                        <tr><td><strong>Waktu Terdeteksi</strong></td><td>: <span class="text-muted"><?= $data['waktu']; ?></span></td></tr>
                                    </table>
                                    
                                    <div class="mt-4">
                                        <?php if($data['status'] == 'Pending'): ?>
                                            <a href="mikrotik_action.php?action=block&mac=<?= $data['mac_address']; ?>&ip=<?= $data['ip_address']; ?>&id=<?= $data['id']; ?>" 
                                               class="btn btn-danger btn-block btn-lg shadow-lg py-3 animate-pulse" 
                                               onclick="return confirm('Eksekusi blokir otomatis via MikroTik?')">
                                                <i class="fas fa-shield-alt mr-2"></i> BLOKIR PERANGKAT
                                            </a>
                                        <?php else: ?>
                                            <div class="alert alert-success text-center border-0 shadow-sm">
                                                <i class="fas fa-check-circle"></i> Akses Terblokir
                                            </div>
                                            <a href="mikrotik_action.php?action=unblock&mac=<?= $data['mac_address']; ?>&ip=<?= $data['ip_address']; ?>&id=<?= $data['id']; ?>" 
                                               class="btn btn-success btn-block btn-lg shadow" 
                                               onclick="return confirm('Buka kembali akses internet?')">
                                                <i class="fas fa-unlock mr-2"></i> BUKA BLOKIR
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="d-flex flex-column h-100">
                                <div class="card shadow border-left-danger mb-3" style="height: auto !important;">
                                    <div class="card-body py-3">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Target Akses / Destination</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-900"><?= $data['dst_address']; ?></div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-skull-crossbones fa-2x text-gray-200"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card shadow border-0">
                                    <div class="card-header py-3 bg-success text-white">
                                        <h6 class="m-0 font-weight-bold"><i class="fas fa-shield-virus mr-2"></i> Analisis Sistem & Mitigasi</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-4">
                                            <label class="font-weight-bold text-primary small text-uppercase">Informasi Kejadian</label>
                                            <div class="p-3 bg-light rounded text-dark" style="font-size: 1.1rem; line-height: 1.6;">
                                                <?= $penjelasan; ?>
                                            </div>
                                        </div>
                                        
                                        <hr>
                                        
                                        <div>
                                            <label class="font-weight-bold text-success small text-uppercase">Rencana Tindakan Lanjutan</label>
                                            <div class="alert alert-light border shadow-sm p-4 mt-2">
                                                <div class="text-dark" style="font-size: 1rem; line-height: 1.8;">
                                                    <?= $mitigasi; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white py-3 text-center text-muted small">
                                        <i class="fas fa-info-circle"></i> Gunakan panel ini untuk meninjau detail insiden sebelum melakukan eksekusi pada perangkat.
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div> </div>
        </div>
    </div>
</body>
</html>