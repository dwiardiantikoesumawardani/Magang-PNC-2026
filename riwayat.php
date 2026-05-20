<?php
include 'koneksi.php';

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
    <title>Riwayat Insiden Jaringan</title>
    
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <style>
    /* ===== FIXING SIDEBAR MELEBAR (FLEBOX BUG) ===== */
    #accordionSidebar {
        flex-shrink: 0 !important; /* Mengunci lebar sidebar agar tidak melar akibat dorongan tabel */
    }
    
    #content-wrapper {
        min-width: 0; /* Memaksa area konten membuat scrollbar internal pada tabel alih-alih merusak ukuran sidebar */
    }

    /* Membuat area search dan filter sejajar ke kanan */
    #dataTable_filter {
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }
    /* Menghilangkan margin bawah default label pencarian */
    #dataTable_filter label {
        margin-bottom: 0; 
    }
    /* Memberi jarak yang pas antara kotak Search dan tombol Filter */
    #filter-dropdown {
        margin-left: 15px; 
    }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                <div class="sidebar-brand-icon"><i class="fas fa-shield-alt"></i></div>
                <div class="sidebar-brand-text mx-3">NetMonitor </div>
            </a>
            <hr class="sidebar-divider my-0">
            
            <li class="nav-item">
                <a class="nav-link" style="padding: 0.5rem 1rem;" href="index.php"><i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span></a>
            </li>
            
            <li class="nav-item active">
                <a class="nav-link" style="padding: 0.5rem 1rem;" href="riwayat.php"><i class="fas fa-fw fa-table"></i><span>Riwayat Insiden</span></a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" style="padding: 0.5rem 1rem;" href="profil_user.php"><i class="fas fa-fw fa-user"></i><span>Profiling per User</span></a>
            </li>

        </ul>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow d-flex justify-content-between align-items-center px-4">
                    
                    <div class="d-flex align-items-center">
                        <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                            <i class="fa fa-bars"></i>
                        </button>
                        <h1 class="h3 mb-0 text-gray-800">Riwayat Keseluruhan Data</h1>
                    </div>
                    
                    <div class="text-muted" style="font-size: 0.9rem;">
                        <i class="fas fa-sync-alt fa-sm mr-1"></i> Last Update: <strong><?= $last_update; ?> WIB</strong>
                    </div>

                </nav>
                <div class="container-fluid">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Riwayat Insiden</h6>
                        </div>
                        <div class="card-body">
                            
                            <div id="filter-dropdown" style="display: none;">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuButton">
                                        <a class="dropdown-item" href="riwayat.php">
                                            <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i> Semua Data
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="riwayat.php?kategori=VPN">
                                            <i class="fas fa-shield-alt fa-sm fa-fw mr-2 text-warning"></i> VPN
                                        </a>
                                        <a class="dropdown-item" href="riwayat.php?kategori=JUDOL">
                                            <i class="fas fa-dice fa-sm fa-fw mr-2 text-danger"></i> JUDOL
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
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
                                        // 1. Ambil parameter filter dari URL (jika ada)
                                        $filter_ip = isset($_GET['ip']) ? mysqli_real_escape_string($conn, $_GET['ip']) : '';
                                        $filter_kat = isset($_GET['kategori']) ? mysqli_real_escape_string($conn, $_GET['kategori']) : '';

                                        // 2. Susun Query Dinamis
                                        $sql = "SELECT * FROM logs WHERE 1=1";

                                        if ($filter_ip != '') {
                                            $sql .= " AND ip_address = '$filter_ip'";
                                        }
                                        if ($filter_kat != '') {
                                            $sql .= " AND kategori = '$filter_kat'";
                                        }

                                        $sql .= " ORDER BY id DESC";

                                        // 3. Eksekusi Query
                                        $query = mysqli_query($conn, $sql);
                                        
                                        // Variabel untuk nomor urut
                                        $no = 1;

                                        // Tampilkan pesan jika data filter tidak ditemukan
                                        if(mysqli_num_rows($query) == 0) {
                                            echo "<tr><td colspan='8'>Data tidak ditemukan untuk filter ini.</td></tr>";
                                        }

                                        while($row = mysqli_fetch_assoc($query)){
                                            $badge_color = ($row['kategori'] == 'VPN') ? 'badge-warning' : 'badge-danger';
                                            $status_badge = ($row['status'] == 'Resolved') ? 'badge-success' : 'badge-secondary';
                                            
                                            echo "<tr>
                                                    <td>{$no}</td>
                                                    <td>{$row['waktu']}</td>
                                                    <td><b>{$row['ip_address']}</b></td>
                                                    <td>{$row['hostname']}</td>
                                                    <td>{$row['mac_address']}</td>
                                                    <td><span class='badge {$badge_color} px-2 py-1'>{$row['kategori']}</span></td>
                                                    <td>{$row['jumlah_deteksi']} x</td>
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
    <script src="css/sb-admin-2.min.js"></script>

    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inisialisasi DataTables
            $('#dataTable').DataTable({
                "order": [[ 1, "desc" ]] // Sortir berdasarkan kolom Waktu (index 1) agar urutan terbaru di atas
            });

            // Munculkan dan pindahkan dropdown filter ke dalam area pencarian (di sebelah kanan input)
            $('#filter-dropdown').show().appendTo('#dataTable_filter');
        });
    </script>
        
</body>
</html>