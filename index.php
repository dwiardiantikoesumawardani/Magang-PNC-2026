<?php
include 'koneksi.php';

// Persentase berdasarkan HARI INI saja
$query_vpn_today = mysqli_query($conn, "SELECT COUNT(*) as total FROM logs WHERE kategori='VPN' AND DATE(waktu) = CURDATE()");
$total_vpn_today = mysqli_fetch_assoc($query_vpn_today)['total'];

$query_judol_today = mysqli_query($conn, "SELECT COUNT(*) as total FROM logs WHERE kategori='JUDOL' AND DATE(waktu) = CURDATE()");
$total_judol_today = mysqli_fetch_assoc($query_judol_today)['total'];

$total_kategori_today = $total_vpn_today + $total_judol_today;
$persen_vpn = ($total_kategori_today > 0) ? round(($total_vpn_today / $total_kategori_today) * 100) : 0;
$persen_judol = ($total_kategori_today > 0) ? round(($total_judol_today / $total_kategori_today) * 100) : 0;

// Query Total Keseluruhan
$total_vpn_all = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM logs WHERE kategori='VPN'"))['total'];
$total_judol_all = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM logs WHERE kategori='JUDOL'"))['total'];
$total_today = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM logs WHERE DATE(waktu) = CURDATE()"))['total'];

// Data Grafik Insiden Per Jam
$grafik_jam = array_fill(0, 24, 0);
$query_grafik = mysqli_query($conn, "SELECT HOUR(waktu) as jam, COUNT(*) as total FROM logs WHERE DATE(waktu) = CURDATE() GROUP BY HOUR(waktu)");
while ($row = mysqli_fetch_assoc($query_grafik)) {
    $grafik_jam[$row['jam']] = $row['total'];
}
$label_jam = '["00:00", "01:00", "02:00", "03:00", "04:00", "05:00", "06:00", "07:00", "08:00", "09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00", "18:00", "19:00", "20:00", "21:00", "22:00", "23:00"]';
$data_jam = '[' . implode(',', $grafik_jam) . ']';

$last_update = date('d M Y | H:i:s');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Monitoring Keamanan Jaringan</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,800,900" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">

    <style>
        html, body {
            overflow-x: hidden;
        }

        .container-fluid {
            padding-top: 0.75rem !important;
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }

        .topbar {
            min-height: 3rem !important;
            height: 3rem !important;
            margin-bottom: 0.75rem !important;
        }

        .topbar h1 {
            font-size: 1.15rem;
        }

        .card {
            margin-bottom: 0.75rem !important;
        }

        .card-body {
            padding: 0.75rem !important;
        }

        .card-header {
            padding: 0.45rem 0.75rem !important;
        }

        .table td,
        .table th {
            padding: 0.35rem 0.5rem !important;
            vertical-align: middle !important;
        }

        .table-responsive {
            margin-bottom: 0 !important;
        }

        .row-tight {
            margin-left: -6px;
            margin-right: -6px;
        }

        .row-tight > [class*="col-"] {
            padding-left: 6px;
            padding-right: 6px;
        }

        .badge {
            font-size: 0.72rem;
            padding: 0.35em 0.55em;
        }

        .pagination {
            margin-bottom: 0 !important;
        }

        .sidebar .nav-item .nav-link {
            padding-top: 0.65rem;
            padding-bottom: 0.65rem;
        }

        .chart-area {
            height: 250px !important;
        }

        .chart-pie {
            height: 250px !important;
            position: relative;
        }

        .chart-card {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .chart-card .card-body {
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .pie-legend {
            margin-top: 0.5rem;
            text-align: center;
            font-size: 0.85rem;
        }
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

        <li class="nav-item active">
            <a class="nav-link" href="index.php">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="riwayat.php">
                <i class="fas fa-fw fa-table"></i>
                <span>Riwayat Insiden</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="profil_user.php">
                <i class="fas fa-fw fa-user"></i>
                <span>Profiling per User</span>
            </a>
        </li>
    </ul>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <nav class="navbar navbar-expand navbar-light bg-white topbar shadow d-flex align-items-center justify-content-between px-3">
                <h1 class="h4 mb-0 text-gray-800">Live Monitoring System</h1>
                <div class="text-muted small font-weight-bold">
                    <i class="fas fa-sync-alt fa-spin mr-1"></i> Last Update: <?= $last_update; ?>
                </div>
            </nav>

            <div class="container-fluid">
                <div class="row row-tight">
                    <div class="col-xl-4 col-md-6 mb-2">
                        <div class="card border-left-warning shadow h-100 py-1">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Deteksi VPN</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_vpn_all; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user-secret fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6 mb-2">
                        <div class="card border-left-danger shadow h-100 py-1">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Deteksi Judol</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_judol_all; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-dice fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6 mb-2">
                        <div class="card border-left-info shadow h-100 py-1">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Insiden Hari Ini</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_today; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row row-tight">
                    <div class="col-lg-12">
                        <div class="card shadow mb-3">
                            <div class="card-header py-2 bg-primary">
                                <h6 class="m-0 font-weight-bold text-white">Log Insiden Terkini (Hari Ini)</h6>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm table-striped text-center mb-0">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th>No</th>
                                                <th>Waktu</th>
                                                <th>IP Address</th>
                                                <th>Host / MAC</th>
                                                <th>Kategori</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $batas = 5;
                                            $halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
                                            $halaman_awal = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;

                                            $query_jumlah_data = mysqli_query($conn, "SELECT COUNT(*) as total FROM logs WHERE DATE(waktu) = CURDATE()");
                                            $jumlah_data = mysqli_fetch_assoc($query_jumlah_data)['total'];
                                            $total_halaman = ceil($jumlah_data / $batas);

                                            $query = mysqli_query($conn, "SELECT * FROM logs WHERE DATE(waktu) = CURDATE() ORDER BY id DESC LIMIT $halaman_awal, $batas");
                                            $no = $halaman_awal + 1;

                                            if (mysqli_num_rows($query) > 0) {
                                                while ($row = mysqli_fetch_assoc($query)):
                                                    $badge = ($row['kategori'] == 'VPN') ? 'badge-warning' : 'badge-danger';
                                                    $status_class = ($row['status'] == 'Resolved') ? 'badge-success' : 'badge-secondary';
                                            ?>
                                            <tr>
                                                <td><?= $no++; ?></td>
                                                <td><small><?= $row['waktu']; ?></small></td>
                                                <td><strong><?= $row['ip_address']; ?></strong></td>
                                                <td class="text-left">
                                                    <small><?= $row['hostname']; ?><br><code><?= $row['mac_address']; ?></code></small>
                                                </td>
                                                <td><span class="badge <?= $badge; ?>"><?= $row['kategori']; ?></span></td>
                                                <td><span class="badge <?= $status_class; ?>"><?= $row['status']; ?></span></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="detail_insiden.php?id=<?= $row['id']; ?>" class="btn btn-info btn-sm">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <?php if ($row['status'] == 'Pending'): ?>
                                                            <a href="mikrotik_action.php?action=block&mac=<?= $row['mac_address']; ?>&id=<?= $row['id']; ?>"
                                                               class="btn btn-primary btn-sm" title="Blokir via MikroTik">
                                                                <i class="fas fa-shield-alt"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                                endwhile;
                                            } else {
                                                echo '<tr><td colspan="7" class="text-center">Tidak ada insiden baru hari ini.</td></tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>

                                <?php if ($total_halaman > 1): ?>
                                <nav aria-label="Page navigation" class="mt-2">
                                    <ul class="pagination pagination-sm justify-content-end">
                                        <li class="page-item <?= ($halaman <= 1) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?halaman=<?= $halaman - 1; ?>">Previous</a>
                                        </li>

                                        <?php for ($x = 1; $x <= $total_halaman; $x++): ?>
                                            <li class="page-item <?= ($halaman == $x) ? 'active' : ''; ?>">
                                                <a class="page-link" href="?halaman=<?= $x; ?>"><?= $x; ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?halaman=<?= $halaman + 1; ?>">Next</a>
                                        </li>
                                    </ul>
                                </nav>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row row-tight align-items-stretch">
                    <div class="col-xl-8 col-lg-7 d-flex">
                        <div class="card shadow mb-2 chart-card w-100">
                            <div class="card-header py-2">
                                <h6 class="m-0 font-weight-bold text-primary">Trafik Hari Ini</h6>
                            </div>
                            <div class="card-body p-2">
                                <div class="chart-area">
                                    <canvas id="myAreaChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-5 d-flex">
                        <div class="card shadow mb-2 chart-card w-100">
                            <div class="card-header py-2">
                                <h6 class="m-0 font-weight-bold text-primary">Persentase (Hari Ini)</h6>
                            </div>
                            <div class="card-body p-2">
                                <div class="chart-pie">
                                    <canvas id="myPieChart"></canvas>
                                    <div style="position:absolute; top:50%; left:0; width:100%; text-align:center; transform:translateY(-40%); pointer-events:none;">
                                        <div style="font-size: 1.1rem; font-weight: 800; color: #5a5c69;"><?= $total_kategori_today; ?></div>
                                        <div style="font-size: 0.72rem; color: #858796;">Total Hari Ini</div>
                                    </div>
                                </div>

                                <div class="pie-legend">
                                    <span class="mr-2">
                                        <i class="fas fa-circle text-warning"></i> VPN (<?= $persen_vpn; ?>%)
                                    </span>
                                    <span>
                                        <i class="fas fa-circle text-danger"></i> Judol (<?= $persen_judol; ?>%)
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/chart.js/Chart.min.js"></script>

<script>
var myPieChart = new Chart(document.getElementById("myPieChart"), {
    type: 'doughnut',
    data: {
        labels: ["VPN", "Judol"],
        datasets: [{
            data: [<?= $total_vpn_today; ?>, <?= $total_judol_today; ?>],
            backgroundColor: ['#f6c23e', '#e74a3b'],
        }],
    },
    options: {
        maintainAspectRatio: false,
        cutoutPercentage: 75,
        legend: { display: false }
    }
});

var myLineChart = new Chart(document.getElementById("myAreaChart"), {
    type: 'line',
    data: {
        labels: <?= $label_jam; ?>,
        datasets: [{
            label: "Insiden",
            data: <?= $data_jam; ?>,
            borderColor: "#4e73df",
            backgroundColor: "rgba(78, 115, 223, 0.05)",
            lineTension: 0.3
        }],
    },
    options: {
        maintainAspectRatio: false,
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true,
                    stepSize: 1
                }
            }]
        }
    }
});
</script>
</body>
</html>