<?php
$page_title   = "Detail Insiden";
$page_heading = "Analisis Insiden";

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
$mitigasi   = "";

switch(strtoupper($data['kategori'])) {

    case 'VPN':
        $penjelasan =
            "Perangkat terdeteksi menggunakan layanan Virtual Private Network (VPN) yang mengenkripsi lalu lintas jaringan dan dapat digunakan untuk menyembunyikan aktivitas pengguna atau melewati kebijakan keamanan jaringan.";

        $mitigasi =
            "1. Blokir endpoint atau port VPN yang terdeteksi.<br>
             2. Tambahkan IP ke Address List monitoring.<br>
             3. Terapkan firewall filtering terhadap trafik VPN yang tidak diizinkan.<br>
             4. Lakukan verifikasi kebutuhan penggunaan VPN oleh pengguna.";
    break;

    case 'JUDOL':
        $penjelasan =
            "Sistem mendeteksi akses menuju domain, IP, atau layanan yang terindikasi terkait aktivitas perjudian online dan termasuk dalam daftar pemantauan keamanan.";

        $mitigasi =
            "1. Blokir domain/IP tujuan pada firewall atau DNS.<br>
             2. Tambahkan perangkat ke daftar pemantauan khusus.<br>
             3. Edukasi pengguna terkait kebijakan penggunaan internet.<br>
             4. Lakukan audit aktivitas lanjutan apabila diperlukan.";
    break;

    case 'DOWNLOAD':
        $penjelasan =
            "Perangkat terdeteksi melakukan aktivitas pengunduhan file dari internet. Aktivitas ini dapat berupa pengunduhan aplikasi, arsip, dokumen, media, maupun executable yang berpotensi membawa malware atau konten tidak sesuai kebijakan perusahaan.";

        $mitigasi =
            "1. Verifikasi sumber file yang diunduh.<br>
             2. Lakukan pemindaian antivirus pada file hasil unduhan.<br>
             3. Batasi pengunduhan file executable dari sumber tidak terpercaya.<br>
             4. Monitor aktivitas download berulang dengan ukuran besar atau mencurigakan.";
    break;

    case 'SOSMED':
        $penjelasan =
            "Perangkat terdeteksi mengakses layanan media sosial. Aktivitas ini dapat mempengaruhi produktivitas kerja dan berpotensi menjadi jalur penyebaran phishing, social engineering, maupun kebocoran informasi organisasi.";

        $mitigasi =
            "1. Terapkan kebijakan akses media sosial sesuai kebutuhan organisasi.<br>
             2. Lakukan edukasi keamanan informasi kepada pengguna.<br>
             3. Pantau aktivitas akses media sosial yang berlebihan.<br>
             4. Blokir platform tertentu apabila tidak mendukung kebutuhan operasional.";
    break;

    default:
        $penjelasan =
            "Aktivitas jaringan terdeteksi dan memerlukan analisis lebih lanjut untuk menentukan tingkat risiko keamanan.";

        $mitigasi =
            "1. Lakukan investigasi terhadap tujuan akses.<br>
             2. Verifikasi aktivitas pengguna.<br>
             3. Pantau perangkat untuk mendeteksi aktivitas lanjutan.";
}

$last_update = date('d M Y | H:i:s');

include 'includes/header.php';
?>

    <style>
        /* Membuat container utama memenuhi layar */
        html, body, #wrapper, #content-wrapper, #content {
            height: 100%;
        }
        #content {
            display: flex;
            flex-direction: column;
        }

        /* ===== PADDING UTAMA ===== */
        .full-screen-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 8px !important;       
            overflow-y: auto;
        }
        .card-body        { padding: 0.4rem !important; }   
        .card-header      { padding: 0.25rem 0.6rem !important; } 
        .card-footer      { padding: 0.25rem 0.6rem !important; }

        /* ===== PERBAIKAN STRUKTUR SEJAJAR KIRI & KANAN ===== */
        .main-row { 
            display: flex;
            align-items: stretch; /* Memaksa kolom kiri & kanan memiliki total tinggi sama */
        }
        
        /* Mengubah sifat dasar card menjadi flex, tanpa memaksa height 100% secara global */
        .card {
            display: flex;
            flex-direction: column;
        }
        .card-body { flex: 1; }

        /* Khusus Card di Kolom Kiri wajib memenuhi tinggi container */
        .kolom-kiri .card {
            height: 100% !important;
        }

        /* Khusus Card Analisis di Kolom Kanan mengambil sisa ruang ruang agar sejajar kebawah */
        .card-analisis {
            flex: 1 !important;
        }

        .topbar { height: 3.5rem !important; }

        .table-detail td {
            padding: 8px 0;               
            border-bottom: 1px solid #f8f9fc;
        }

        /* Styling Khusus Tombol Blokir / Unblock */
        .btn-aksi-tengah {
            width: fit-content !important;  
            margin: 0 auto;                 
            display: block;                 
            padding: 8px 24px;              
            font-weight: bold;
        }

        .animate-pulse { animation: pulse 2s infinite; }
        @keyframes pulse {
            0%   { transform: scale(1); }
            50%  { transform: scale(1.03); }
            100% { transform: scale(1); }
        }
    </style>

        <div id="content-wrapper" class="d-flex flex-column bg-light">
            <div id="content">
                
                <nav class="navbar navbar-expand navbar-light bg-white topbar static-top shadow d-flex align-items-center justify-content-between px-3">
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
                    <div class="row main-row m-0">
                        
                        <div class="col-lg-4 p-1 kolom-kiri">
                            <div class="card shadow border-0">
                                <div class="card-header bg-dark d-flex align-items-center">
                                    <?php
                                    $badgeKategori = 'secondary';

                                    switch(strtoupper($data['kategori'])) {
                                        case 'VPN':
                                            $badgeKategori = 'warning';
                                            break;

                                        case 'JUDOL':
                                            $badgeKategori = 'danger';
                                            break;

                                        case 'DOWNLOAD':
                                            $badgeKategori = 'success';
                                            break;

                                        case 'SOSMED':
                                            $badgeKategori = 'primary';
                                            break;
                                    }
                                    ?>
                                    <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-laptop mr-2"></i> Detail Perangkat</h6>
                                </div>
                                <div class="card-body d-flex flex-column justify-content-between">
                                    <table class="table table-borderless table-detail mb-auto">
                                        <tr><td width="45%"><strong>Status Log</strong></td><td>: 
                                            <span class="badge badge-<?= ($data['status']=='Resolved')?'success':'secondary'; ?> px-3"><?= $data['status']; ?></span>
                                        </td></tr>
                                        <tr><td><strong>Kategori</strong></td><td>: 
                                        <span class="badge badge-<?= $badgeKategori; ?> px-3">
                                            <?= strtoupper($data['kategori']); ?>
                                        </span>                                        </td></tr>
                                        <tr><td><strong>IP Address</strong></td><td>: <code class="text-primary h6"><?= $data['ip_address']; ?></code></td></tr>
                                        <tr><td><strong>MAC Address</strong></td><td>: <code><?= $data['mac_address']; ?></code></td></tr>
                                        <tr><td><strong>Hostname</strong></td><td>: <span class="text-dark font-weight-bold"><?= $data['hostname'] ?: '-'; ?></span></td></tr>
                                        <tr><td><strong>Waktu Terdeteksi</strong></td><td>: <span class="text-muted"><?= $data['waktu']; ?></span></td></tr>
                                    </table>
                                    
                                    <div class="mt-4 mb-2 text-center">
                                        <?php if($data['status'] == 'Pending'): ?>
                                            <a href="mikrotik_action.php?action=block&mac=<?= $data['mac_address']; ?>&ip=<?= $data['ip_address']; ?>&id=<?= $data['id']; ?>" 
                                               class="btn btn-danger shadow-lg btn-aksi-tengah animate-pulse" 
                                               onclick="return confirm('Eksekusi blokir otomatis via MikroTik?')">
                                                <i class="fas fa-shield-alt mr-2"></i> BLOKIR PERANGKAT
                                            </a>
                                        <?php else: ?>
                                            <div class="alert alert-success border-0 shadow-sm py-2 mx-4 mb-3 small font-weight-bold">
                                                <i class="fas fa-check-circle"></i> Akses Terblokir
                                            </div>
                                            <a href="mikrotik_action.php?action=unblock&mac=<?= $data['mac_address']; ?>&ip=<?= $data['ip_address']; ?>&id=<?= $data['id']; ?>" 
                                               class="btn btn-success shadow btn-aksi-tengah" 
                                               onclick="return confirm('Buka kembali akses internet?')">
                                                <i class="fas fa-unlock mr-2"></i> BUKA BLOKIR
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8 p-1 d-flex flex-column kolom-kanan">
                            
                            <div class="card shadow border-left-danger mb-2">
                                <div class="card-body py-2">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Target Akses / Destination</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-900"><?= $data['dst_address']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                        <?php
                                        $icon = "fa-globe";

                                        switch(strtoupper($data['kategori'])) {
                                            case 'VPN':
                                                $icon = "fa-user-secret";
                                                break;
                                            case 'JUDOL':
                                                $icon = "fa-dice";
                                                break;
                                            case 'DOWNLOAD':
                                                $icon = "fa-download";
                                                break;
                                            case 'SOSMED':
                                                $icon = "fa-comments";
                                                break;
                                        }
                                        ?>
                                        <i class="fas <?= $icon ?> fa-2x text-gray-200"></i>                                        </div>
                                    </div>
                                </div>
                            </div>

                           <?php
                            $headerColor = 'bg-secondary';

                            switch(strtoupper($data['kategori'])) {

                                case 'VPN':
                                    $headerColor = 'bg-warning';
                                    break;

                                case 'JUDOL':
                                    $headerColor = 'bg-danger';
                                    break;

                                case 'DOWNLOAD':
                                    $headerColor = 'bg-success';
                                    break;

                                case 'SOSMED':
                                    $headerColor = 'bg-primary';
                                    break;
                            }
                            ?>
                                <div class="card-header <?= $headerColor; ?> text-white">
                                    <h6 class="m-0 font-weight-bold"><i class="fas fa-shield-virus mr-2"></i> Analisis Sistem & Mitigasi</h6>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <div class="mb-2">
                                        <label class="font-weight-bold text-primary small text-uppercase mb-1">Informasi Kejadian</label>
                                        <div class="p-2 bg-light rounded text-dark" style="font-size: 1.05rem; line-height: 1.5;">
                                            <?= $penjelasan; ?>
                                        </div>
                                    </div>
                                    
                                    <hr class="my-2">
                                    
                                    <div class="flex-grow-1 d-flex flex-column">
                                        <label class="font-weight-bold text-success small text-uppercase mb-1">Rencana Tindakan Lanjutan</label>
                                        <div class="alert alert-light border shadow-sm p-3 m-0 flex-grow-1">
                                            <div class="text-dark" style="font-size: 1rem; line-height: 1.8;">
                                                <?= $mitigasi; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-white text-center text-muted small mt-auto">
                                    <i class="fas fa-info-circle"></i> Gunakan panel ini untuk meninjau detail insiden sebelum melakukan eksekusi pada perangkat.
                                </div>
                            </div>

                        </div> </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>