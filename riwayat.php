<?php
include 'koneksi.php';

// SET WAKTU KE WIB (Asia/Jakarta)
date_default_timezone_set('Asia/Jakarta');
$last_update = date('d M Y | H:i:s');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Riwayat Insiden Jaringan</title>
    
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    
    <style>
        /* Base Fullscreen Layout */
        html, body { height: 100%; overflow: hidden; }
        #wrapper { height: 100vh; display: flex; }
        #content-wrapper { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        #content { flex: 1; display: flex; flex-direction: column; background-color: #f8f9fc; }
        
        /* Container Scrollable */
        .container-fluid { 
            flex: 1; 
            display: flex; 
            flex-direction: column; 
            padding: 1.5rem !important; 
            overflow-y: auto; 
        }

        .full-height-card { flex: 1; display: flex; flex-direction: column; border: none; }
        .card-body { flex: 1; display: flex; flex-direction: column; }

        /* MERAPIKAN PENCARIAN & FILTER */
        /* Menghilangkan margin default label pencarian DataTables */
        .dataTables_filter label {
            margin-bottom: 0 !important;
            display: flex !important;
            align-items: center;
        }

        /* Container bungkus untuk filter dan search */
        .custom-filter-wrapper {
            display: flex;
            align-items: center;
            gap: 10px; /* Memberi jarak konsisten antar elemen */
        }

        .dataTables_filter input {
            margin-left: 10px !important;
            border-radius: 5px;
        }

        /* Topbar styling */
        .topbar-title { font-weight: 800; color: #4e73df; }
        .last-update-box { font-size: 0.85rem; color: #858796; }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                <div class="sidebar-brand-icon"><i class="fas fa-shield-alt"></i></div>
                <div class="sidebar-brand-text mx-3">NetMonitor <sup>App</sup></div>
            </a>
            <hr class="sidebar-divider my-0">
            <li class="nav-item">
                <a class="nav-link" href="index.php"><i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span></a>
            </li>
            <li class="nav-item active">
                <a class="nav-link" href="riwayat.php"><i class="fas fa-fw fa-table"></i><span>Riwayat Insiden</span></a>
            </li>
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Analisis Lanjutan</div>
            <li class="nav-item">
                <a class="nav-link" href="profil_user.php"><i class="fas fa-fw fa-user"></i><span>Profiling per User</span></a>
            </li>
        </ul>

        <div id="content-wrapper">
            <div id="content">
                
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow d-flex justify-content-between px-4">
                    <h1 class="h4 mb-0 text-gray-800 font-weight-bold">Riwayat Keseluruhan Data</h1>
                    <div class="last-update-box">
                        <i class="fas fa-sync-alt fa-sm mr-1"></i> Last Update: <strong><?= $last_update; ?> WIB</strong>
                    </div>
                </nav>

                <div class="container-fluid">
                    <div class="card shadow mb-4 full-height-card">
                        <div class="card-header py-3 bg-white border-bottom">
                            <h6 class="m-0 font-weight-bold text-primary">Log Aktivitas Terdeteksi</h6>
                        </div>

                        <div class="card-body">
                            <div id="external-filter" style="display:none;">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle font-weight-bold" type="button" id="dropFilter" data-toggle="dropdown">
                                        <i class="fas fa-filter mr-1"></i> Kategori
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right shadow">
                                        <a class="dropdown-item" href="riwayat.php">Semua Data</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="riwayat.php?kategori=VPN text-warning font-weight-bold">Kategori: VPN</a>
                                        <a class="dropdown-item" href="riwayat.php?kategori=JUDOL text-danger font-weight-bold">Kategori: JUDOL</a>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover border-bottom" id="dataTable" width="100%" cellspacing="0">
                                    <thead class="bg-primary text-white text-center">
                                        <tr>
                                            <th>No.</th>
                                            <th>Waktu</th>
                                            <th>IP Address</th>
                                            <th>Hostname</th>
                                            <th>MAC Address</th>
                                            <th>Kategori</th>
                                            <th>Deteksi</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-center align-middle">
                                        <?php
                                        $filter_kat = isset($_GET['kategori']) ? mysqli_real_escape_string($conn, $_GET['kategori']) : '';
                                        $sql = "SELECT * FROM logs WHERE 1=1";
                                        if ($filter_kat != '') $sql .= " AND kategori = '$filter_kat'";
                                        $sql .= " ORDER BY id DESC";

                                        $query = mysqli_query($conn, $sql);
                                        $no = 1;

                                        while($row = mysqli_fetch_assoc($query)){
                                            $badge_color = ($row['kategori'] == 'VPN') ? 'badge-warning' : 'badge-danger';
                                            $status_badge = ($row['status'] == 'Resolved') ? 'badge-success' : 'badge-secondary';
                                            
                                            echo "<tr>
                                                    <td class='text-muted'>{$no}</td>
                                                    <td class='small'>{$row['waktu']}</td>
                                                    <td class='font-weight-bold text-dark'>{$row['ip_address']}</td>
                                                    <td>".($row['hostname'] ?: '-')."</td>
                                                    <td><code class='text-xs'>{$row['mac_address']}</code></td>
                                                    <td><span class='badge {$badge_color} px-2 py-1'>{$row['kategori']}</span></td>
                                                    <td><span class='badge badge-light border'>{$row['jumlah_deteksi']} x</span></td>
                                                    <td><span class='badge {$status_badge}'>{$row['status']}</span></td>
                                                </tr>";
                                            $no++;
                                        }
                                        ?>
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

    <script>
        $(document).ready(function() {
            var table = $('#dataTable').DataTable({
                "order": [[ 1, "desc" ]],
                "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 custom-header-controls"f>>' +
                       '<"row"<"col-sm-12"tr>>' +
                       '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                "language": {
                    "search": "Cari Data:",
                    "lengthMenu": "Tampilkan _MENU_"
                }
            });

            // LOGIKA PENYELARASAN:
            // 1. Buat wrapper flexbox di area pencarian
            $(".custom-header-controls").addClass("d-flex align-items-center justify-content-end");
            
            // 2. Masukkan Filter ke dalam wrapper sebelum kotak pencarian
            $("#external-filter").detach().appendTo(".custom-header-controls").show();
            
            // 3. Tambahkan jarak (gap) via CSS atau Margin agar tidak menempel
            $("#external-filter").addClass("mr-2");
        });
    </script>
</body>
</html>