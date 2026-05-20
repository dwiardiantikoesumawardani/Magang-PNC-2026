<?php
include 'koneksi.php';

// Query Profiling: Dikelompokkan berdasarkan MAC Address
$query = mysqli_query($conn, "SELECT 
    mac_address, 
    MAX(hostname) as hostname, 
    GROUP_CONCAT(DISTINCT ip_address SEPARATOR ', ') as used_ips, 
    COUNT(*) as total_insiden,
    SUM(CASE WHEN kategori = 'VPN' THEN 1 ELSE 0 END) as vpn_count,
    SUM(CASE WHEN kategori = 'JUDOL' THEN 1 ELSE 0 END) as judol_count,
    MAX(waktu) as terakhir_terdeteksi
    FROM logs 
    GROUP BY mac_address 
    ORDER BY total_insiden DESC");

// SET WAKTU KE WIB (Asia/Jakarta) untuk Last Update
date_default_timezone_set('Asia/Jakarta');
$last_update = date('d M Y | H:i:s');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Profiling User - Netmonitor</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    
    <style>
        /* ===== FIXING SIDEBAR MELEBAR (FLEXBOX BUG) ===== */
        #accordionSidebar {
            flex-shrink: 0 !important; /* Mengunci lebar sidebar agar tidak melar akibat dorongan tabel */
        }
        
        #content-wrapper {
            min-width: 0; /* Memaksa area konten membuat scrollbar internal pada tabel alih-alih merusak ukuran sidebar */
        }

        /* PENYESUAIAN PADDING */
        .container-fluid { padding-top: 10px !important; }
        .card-body { padding: 0.75rem !important; }
        .table td, .table th { padding: 0.5rem !important; vertical-align: middle !important; }
        .topbar { height: 3.5rem !important; margin-bottom: 1rem !important; }
        .page-heading { margin-bottom: 15px !important; }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                <div class="sidebar-brand-icon"><i class="fas fa-shield-alt"></i></div>
                <div class="sidebar-brand-text mx-3">NetMonitor</div>
            </a>
            <hr class="sidebar-divider my-0">
            
            <li class="nav-item">
                <a class="nav-link" style="padding: 0.5rem 1rem;" href="index.php"><i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span></a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" style="padding: 0.5rem 1rem;" href="riwayat.php"><i class="fas fa-fw fa-table"></i><span>Riwayat Insiden</span></a>
            </li>
            
            <li class="nav-item active">
                <a class="nav-link" style="padding: 0.5rem 1rem;" href="profil_user.php"><i class="fas fa-fw fa-user"></i><span>Profiling per User</span></a>
            </li>
            
        </ul>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content"> 

                <nav class="navbar navbar-expand navbar-light bg-white topbar static-top shadow d-flex align-items-center justify-content-between px-4">
                    
                    <div class="d-flex align-items-center">
                        <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                            <i class="fa fa-bars"></i>
                        </button>
                        <h1 class="h5 mb-0 text-gray-800">Profiling User & Device</h1>
                    </div>

                    <div class="text-muted small font-weight-bold">
                        <i class="fas fa-sync-alt fa-sm mr-1"></i> Last Update: <strong><?= $last_update; ?> WIB</strong>
                    </div>
                </nav>
                <div class="container-fluid">
                    <div class="card shadow mb-4">
                        <div class="card-header py-2 bg-primary">
                            <h6 class="m-0 font-weight-bold text-white">Daftar Akumulasi Pelanggaran Perangkat</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm table-striped" id="dataTable" width="100%" cellspacing="0">
                                    <thead class="bg-gray-100 text-center">
                                        <tr>
                                            <th>No</th> 
                                            <th>MAC Address</th>
                                            <th>Hostname</th>
                                            <th>IP Digunakan</th>
                                            <th>Total</th>
                                            <th>Kategori Detail</th>
                                            <th>Update Terakhir</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $no = 1; // Counter nomor
                                        while($row = mysqli_fetch_assoc($query)): 
                                        ?>
                                        <tr>
                                            <td class="text-center font-weight-bold"><?= $no++; ?></td>
                                            <td class="text-center"><code><?= $row['mac_address']; ?></code></td>
                                            <td><?= $row['hostname'] ?: '<em class="text-muted">Unknown</em>'; ?></td>
                                            <td><small><?= $row['used_ips']; ?></small></td>
                                            <td class="text-center">
                                                <span class="badge badge-danger" style="font-size: 0.9rem;"><?= $row['total_insiden']; ?></span>
                                            </td>
                                            <td class="small">
                                                <span class="text-warning font-weight-bold">VPN:</span> <?= $row['vpn_count']; ?>x<br>
                                                <span class="text-danger font-weight-bold">JUDOL:</span> <?= $row['judol_count']; ?>x
                                            </td>
                                            <td class="text-center small"><?= $row['terakhir_terdeteksi']; ?></td>
                                            <td class="text-center">
                                                <a href="detail_profil.php?mac=<?= $row['mac_address']; ?>" class="btn btn-primary btn-sm shadow-sm">
                                                    <i class="fas fa-search fa-sm text-white-50"></i> Telusuri
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
    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="css/sb-admin-2.min.js"></script> <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable({
                "order": [[ 4, "desc" ]], // Urutkan berdasarkan total insiden
                "pageLength": 10,
                "language": {
                    "search": "Cari Perangkat:",
                    "lengthMenu": "Tampil _MENU_ data"
                }
            });
        });
    </script>
</body>
</html>