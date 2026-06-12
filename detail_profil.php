<?php
$page_title   = "Detail Profil";
$page_heading = "Analisis Profil";

include 'koneksi.php';

// Validasi jika MAC tidak ada di URL
if(!isset($_GET['mac'])) { header("Location: profil_user.php"); exit; }
$mac = $_GET['mac']; 

// 1. Ambil data profil (Hostname terbaru)
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT hostname FROM logs WHERE mac_address='$mac' ORDER BY waktu DESC LIMIT 1"));

// 2. Ambil statistik pelanggaran
$stats = mysqli_fetch_assoc(mysqli_query($conn, " SELECT
        COUNT(*) as total,
        SUM(CASE WHEN kategori='JUDOL' THEN 1 ELSE 0 END) as total_judol,
        SUM(CASE WHEN kategori='VPN' THEN 1 ELSE 0 END) as total_vpn,
        SUM(CASE WHEN kategori='DOWNLOAD' THEN 1 ELSE 0 END) as total_download,
        SUM(CASE WHEN kategori='SOSMED' THEN 1 ELSE 0 END) as total_sosmed
    FROM logs
    WHERE mac_address='$mac'
"));

// 3. Ambil riwayat lengkap insiden untuk MAC ini
$riwayat = mysqli_query($conn, "SELECT * FROM logs WHERE mac_address='$mac' ORDER BY waktu DESC");

include 'includes/header.php';
?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content" class="p-4">
                
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Analisis Profil: <?= $user['hostname'] ?: 'Unknown Device'; ?></h1>
                    <a href="profil_user.php" class="btn btn-sm btn-secondary shadow-sm"><i class="fas fa-arrow-left fa-sm"></i> Kembali</a>
                </div>

                <div class="row">
                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Temuan</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total']; ?> Kali</div>
                                    </div>
                                    <div class="col-auto"><i class="fas fa-shield-alt fa-2x text-gray-300"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card border-left-danger shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Pelanggaran JUDOL</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total_judol']; ?> Kejadian</div>
                                    </div>
                                    <div class="col-auto"><i class="fas fa-dice fa-2x text-gray-300"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Penggunaan VPN</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total_vpn']; ?> Kejadian</div>
                                    </div>
                                    <div class="col-auto"><i class="fas fa-user-secret fa-2x text-gray-300"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-dark">
                        <h6 class="m-0 font-weight-bold text-white">Log Aktivitas Perangkat (MAC: <?= $mac; ?>)</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="dataTableRiwayat" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Waktu</th>
                                        <th>IP Saat Itu</th>
                                        <th>Kategori</th>
                                        <th>Tujuan / Domain</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = mysqli_fetch_assoc($riwayat)): ?>
                                    <tr>
                                        <td><?= $row['waktu']; ?></td>
                                        <td><span class="badge badge-light border"><?= $row['ip_address']; ?></span></td>
                                        <td>
                                        <?php
                                        $badge_color = 'secondary';

                                        switch(strtoupper($row['kategori'])) {
                                            case 'VPN':
                                                $badge_color = 'warning';
                                                break;

                                            case 'JUDOL':
                                                $badge_color = 'danger';
                                                break;

                                            case 'DOWNLOAD':
                                                $badge_color = 'success';
                                                break;

                                            case 'SOSMED':
                                                $badge_color = 'primary';
                                                break;
                                        }
                                        ?>
                                            <span class="badge badge-<?= $badge_color; ?>">
                                                <?= strtoupper($row['kategori']); ?>
                                            </span>
                                        </td>
                                        <td class="text-primary"><?= $row['dst_address']; ?></td>
                                        <td>
                                            <a href="detail_insiden.php?id=<?= $row['id']; ?>" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i> Analisis
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php include 'includes/scripts.php'; ?>
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#dataTableRiwayat').DataTable({
                "order": [[ 0, "desc" ]] // Urutkan berdasarkan waktu terbaru
            });
        });
    </script>
</body>
</html>