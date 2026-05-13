<?php
include 'koneksi.php';

// Menangkap filter jika ada
$filter_ip = isset($_GET['ip']) ? $_GET['ip'] : '';
$filter_kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';

// Menyusun Query berdasarkan filter
$query_sql = "SELECT * FROM logs WHERE 1=1";
if($filter_ip != '') { $query_sql .= " AND ip_address = '$filter_ip'"; }
if($filter_kategori != '') { $query_sql .= " AND kategori = '$filter_kategori'"; }
$query_sql .= " ORDER BY id DESC";

$data_logs = mysqli_query($conn, $query_sql);

// Mengambil daftar IP unik untuk Dropdown
$list_ip = mysqli_query($conn, "SELECT DISTINCT ip_address, hostname FROM logs");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Profiling Insiden</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                <div class="sidebar-brand-icon"><i class="fas fa-shield-alt"></i></div>
                <div class="sidebar-brand-text mx-3">NetMonitor</div>
            </a>
            <hr class="sidebar-divider my-0">
            <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span></a></li>
            <li class="nav-item"><a class="nav-link" href="riwayat.php"><i class="fas fa-fw fa-table"></i><span>Riwayat Insiden</span></a></li>
            <li class="nav-item active"><a class="nav-link" href="profiling.php"><i class="fas fa-fw fa-search"></i><span>Profiling User</span></a></li>
        </ul>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content" class="p-4">
                <h1 class="h3 mb-4 text-gray-800">Analisis Profiling (By User & Jenis)</h1>

                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-info text-white">
                        <h6 class="m-0 font-weight-bold"><i class="fas fa-filter"></i> Filter Pencarian Data</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="profiling.php" class="form-inline">
                            <label class="mr-2">User (IP/Host) :</label>
                            <select name="ip" class="form-control mr-4">
                                <option value="">-- Semua User --</option>
                                <?php while($ip = mysqli_fetch_assoc($list_ip)){ ?>
                                    <option value="<?= $ip['ip_address']; ?>" <?= ($filter_ip == $ip['ip_address']) ? 'selected' : ''; ?>>
                                        <?= $ip['ip_address']; ?> (<?= $ip['hostname']; ?>)
                                    </option>
                                <?php } ?>
                            </select>

                            <label class="mr-2">Kategori :</label>
                            <select name="kategori" class="form-control mr-4">
                                <option value="">-- Semua Kategori --</option>
                                <option value="VPN" <?= ($filter_kategori == 'VPN') ? 'selected' : ''; ?>>VPN</option>
                                <option value="JUDOL" <?= ($filter_kategori == 'JUDOL') ? 'selected' : ''; ?>>Judi Online</option>
                            </select>

                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Terapkan Filter</button>
                            <a href="profiling.php" class="btn btn-secondary ml-2"><i class="fas fa-sync"></i> Reset</a>
                        </form>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                           <table class="table table-bordered" id="dataTable">
                                <thead>
                                    <tr>
                                        <th>Hostname</th>
                                        <th>MAC Address</th>
                                        <th>Total Insiden</th>
                                        <th>Terakhir Terdeteksi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = mysqli_fetch_assoc($query)): ?>
                                    <tr>
                                        <td><?= $row['hostname'] ?: 'Unknown Device'; ?></td>
                                        <td><code><?= $row['mac_address']; ?></code></td>
                                        <td><?= $row['total_insiden']; ?> Kali</td>
                                        <td><?= $row['terakhir_aktif']; ?></td>
                                        <td>
                                            <a href="detail_profil.php?mac=<?= $row['mac_address']; ?>" class="btn btn-primary btn-sm">
                                                Lihat Profil
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
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script>$(document).ready(function() { $('#dataTable').DataTable(); });</script>
</body>
</html>