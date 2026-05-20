<?php
// File: behavior_user.php
include 'koneksi.php';

// Ambil semua data riwayat dari database
$query_alerts = mysqli_query($conn, "SELECT * FROM behavior_alerts ORDER BY id DESC");

// Last Update
date_default_timezone_set('Asia/Jakarta');
$last_update = date('d M Y | H:i:s');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Behavior Analytics - NetMonitor</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

    <style>
        /* REVISI PADDING SETIPIS MUNGKIN (Sesuai Dashboard Index) */
        .container-fluid { padding-top: 10px !important; }
        .card-body { padding: 0.5rem !important; }
        .table td, .table th { 
            padding: 0.4rem !important; 
            vertical-align: middle !important; 
            font-size: 0.85rem; 
        }
        .topbar { height: 3.5rem !important; margin-bottom: 1rem !important; }
        
        /* Sidebar layout fix */
        #accordionSidebar { flex-shrink: 0 !important; }
        #content-wrapper { min-width: 0; background-color: #f8f9fc; }
        
        /* Badge Custom */
        .badge-download { background-color: #e74a3b; color: white; font-size: 0.75rem; }
        .badge-stream { background-color: #f6c23e; color: white; font-size: 0.75rem; }
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
                <a class="nav-link" style="padding: 0.5rem 1rem;" href="index.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" style="padding: 0.5rem 1rem;" href="riwayat.php">
                    <i class="fas fa-fw fa-table"></i><span>Riwayat Insiden</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" style="padding: 0.5rem 1rem;" href="profil_user.php">
                    <i class="fas fa-fw fa-user"></i><span>Profiling per User</span>
                </a>
            </li>

            <li class="nav-item active">
                <a class="nav-link" style="padding: 0.5rem 1rem;" href="behavior_user.php">
                    <i class="fas fa-fw fa-chart-pie"></i><span>User Behavior</span>
                </a>
            </li>

        </ul>
        <div id="content-wrapper" class="d-flex flex-column">

            <div id="content">

                <nav class="navbar navbar-expand navbar-light bg-white topbar static-top shadow d-flex align-items-center justify-content-between px-4">
                    <h1 class="h5 mb-0 text-gray-800">User Behavior Analytics</h1>
                    <div class="text-muted small font-weight-bold">
                        <i class="fas fa-sync-alt fa-sm mr-1"></i> Last Update: <?= $last_update; ?> WIB
                    </div>
                </nav>

                <div class="container-fluid">

                    <div class="card shadow mb-4">
                        <div class="card-header py-2 bg-primary">
                            <h6 class="m-0 font-weight-bold text-white">Log Aktivitas Unduhan & Bandwidth</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-striped" id="dataTableBehavior" width="100%" cellspacing="0">
                                    <thead class="bg-gray-100 text-center text-dark">
                                        <tr>
                                            <th>No</th>
                                            <th>Waktu</th>
                                            <th>MAC Address</th>
                                            <th>Kategori Alert</th>
                                            <th>Detail Aktivitas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $no = 1; 
                                        while($row = mysqli_fetch_assoc($query_alerts)): 
                                            // Tentukan warna badge berdasarkan tipe alert
                                            $badge_class = (strpos($row['tipe_alert'], 'Download') !== false) ? 'badge-download' : 'badge-stream';
                                        ?>
                                        <tr>
                                            <td class="text-center font-weight-bold"><?= $no++; ?></td>
                                            <td class="text-center small"><?= $row['waktu']; ?></td>
                                            <td class="text-center"><code><?= $row['mac_address']; ?></code></td>
                                            <td class="text-center">
                                                <span class="badge <?= $badge_class ?> px-2">
                                                    <?= $row['tipe_alert']; ?>
                                                </span>
                                            </td>
                                            <td class="small"><?= $row['keterangan']; ?></td>
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
    <script src="js/sb-admin-2.min.js"></script>

    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Inisialisasi DataTable agar mirip index
            $('#dataTableBehavior').DataTable({
                "order": [[ 0, "asc" ]],
                "pageLength": 10,
                "language": {
                    "search": "Cari Log:",
                    "lengthMenu": "Tampil _MENU_ data"
                }
            });

            // Polling Alert Behavior setiap 5 detik
            function cekAlertBehavior() {
                $.ajax({
                    url: 'cek_alert.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        if(data.ada_notifikasi) {
                            Swal.fire({
                                icon: 'warning',
                                title: data.tipe,
                                html: data.pesan,
                                confirmButtonText: 'Catat di Log',
                                confirmButtonColor: '#4e73df'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    location.reload();
                                }
                            });
                        }
                    }
                });
            }
            setInterval(cekAlertBehavior, 5000);
        });
    </script>

</body>
</html>