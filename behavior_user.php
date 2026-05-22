<?php
// File: behavior_user.php
include 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');
$last_update = date('d M Y | H:i:s');

// 1. Ambil data HARI INI untuk Tabel
$query_alerts = mysqli_query($conn, "SELECT * FROM behavior_alerts WHERE DATE(waktu) = CURDATE() ORDER BY id DESC");

// 2. Siapkan Data untuk Grafik Garis (Tren per Jam Hari Ini)
$grafik_garis = mysqli_query($conn, "SELECT HOUR(waktu) as jam, COUNT(*) as total FROM behavior_alerts WHERE DATE(waktu) = CURDATE() GROUP BY HOUR(waktu)");
$data_jam = array_fill(0, 24, 0); 
while($row = mysqli_fetch_assoc($grafik_garis)) {
    $data_jam[$row['jam']] = $row['total'];
}
$label_jam = json_encode(array_map(function($jam) { return sprintf("%02d:00", $jam); }, range(0, 23)));
$nilai_jam = json_encode(array_values($data_jam));

// 3. Siapkan Data untuk Grafik Pie (Persentase Tipe Alert Hari Ini)
$grafik_pie = mysqli_query($conn, "SELECT tipe_alert, COUNT(*) as total FROM behavior_alerts WHERE DATE(waktu) = CURDATE() GROUP BY tipe_alert");
$label_pie = [];
$nilai_pie = [];
$total_insiden = 0;
while($row = mysqli_fetch_assoc($grafik_pie)) {
    $label_pie[] = $row['tipe_alert'];
    $nilai_pie[] = $row['total'];
    $total_insiden += $row['total'];
}
$json_label_pie = json_encode($label_pie);
$json_nilai_pie = json_encode($nilai_pie);
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
        .container-fluid { padding-top: 10px !important; }
        .card-body { padding: 0.75rem !important; }
        .table td, .table th { padding: 0.5rem !important; vertical-align: middle !important; font-size: 0.85rem; }
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
                <a class="nav-link" style="padding: 0.5rem 1rem;" href="index.php"><i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" style="padding: 0.5rem 1rem;" href="riwayat.php"><i class="fas fa-fw fa-table"></i><span>Riwayat Insiden</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" style="padding: 0.5rem 1rem;" href="profil_user.php"><i class="fas fa-fw fa-user"></i><span>Profiling per User</span></a>
            </li>
            <li class="nav-item active">
                <a class="nav-link" style="padding: 0.5rem 1rem;" href="behavior_user.php"><i class="fas fa-fw fa-chart-pie"></i><span>User Behavior</span></a>
            </li>
            <hr class="sidebar-divider d-none d-md-block">
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar static-top shadow d-flex align-items-center justify-content-between px-4 mb-4" style="height: 3.5rem;">
                    <h1 class="h5 mb-0 text-gray-800">User Behavior Analytics</h1>
                    <div class="text-muted small font-weight-bold">
                        <i class="fas fa-sync-alt fa-sm mr-1"></i> Last Update: <?= $last_update; ?> WIB
                    </div>
                </nav>

                <div class="container-fluid">
                    <div class="card shadow mb-4">
                        <div class="card-header py-2 bg-primary">
                            <h6 class="m-0 font-weight-bold text-white">Log Aktivitas Unduhan & Bandwidth (Hari Ini)</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-striped" id="dataTableBehavior" width="100%" cellspacing="0">
                                    <thead class="bg-gray-100 text-center text-dark">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="15%">Waktu</th>
                                            <th width="15%">MAC Address</th>
                                            <th width="15%">Kategori Alert</th>
                                            <th>Detail Singkat</th>
                                            <th width="10%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $no = 1; 
                                        while($row = mysqli_fetch_assoc($query_alerts)): 
                                            $badge_class = (strpos($row['tipe_alert'], 'Download') !== false) ? 'badge-download' : 'badge-stream';
                                            $keterangan_singkat = strlen($row['keterangan']) > 60 ? substr(strip_tags($row['keterangan']), 0, 60) . "..." : strip_tags($row['keterangan']);
                                        ?>
                                        <tr>
                                            <td class="text-center font-weight-bold"><?= $no++; ?></td>
                                            <td class="text-center small"><?= $row['waktu']; ?></td>
                                            <td class="text-center"><code><?= $row['mac_address']; ?></code></td>
                                            <td class="text-center"><span class="badge <?= $badge_class ?> px-2"><?= $row['tipe_alert']; ?></span></td>
                                            <td class="small"><?= $keterangan_singkat; ?></td>
                                            <td class="text-center">
                                                <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#detailModal<?= $row['id']; ?>" title="Lihat Detail">
                                                    <i class="fas fa-eye"></i> Detail
                                                </button>

                                                <div class="modal fade" id="detailModal<?= $row['id']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered text-left" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-info text-white py-2">
                                                                <h5 class="modal-title font-weight-bold" style="font-size: 1rem;"><i class="fas fa-info-circle"></i> Detail Insiden Behavior</h5>
                                                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body text-dark">
                                                                <table class="table table-borderless table-sm mb-2">
                                                                    <tr><th width="40%">Waktu Kejadian</th><td>: <?= $row['waktu']; ?></td></tr>
                                                                    <tr><th>MAC Address</th><td>: <code><?= $row['mac_address']; ?></code></td></tr>
                                                                    <tr><th>Tipe Peringatan</th><td>: <span class="badge <?= $badge_class ?>"><?= $row['tipe_alert']; ?></span></td></tr>
                                                                    <tr><th colspan="2">Keterangan Aktivitas:</th></tr>
                                                                </table>
                                                                <div class="alert alert-secondary small mb-0">
                                                                    <?= $row['keterangan']; ?>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer py-1">
                                                                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-8 col-lg-7 mb-4">
                            <div class="card shadow h-100">
                                <div class="card-header"><h6 class="m-0 font-weight-bold text-primary" style="font-size: 1rem;">Trafik Behavior Hari Ini</h6></div>
                                <div class="card-body">
                                    <div class="chart-area" style="height: 200px;"><canvas id="myAreaChart"></canvas></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-2 bg-white">
                                    <h6 class="m-0 font-weight-bold text-primary">Persentase (Hari Ini)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-pie pt-2 pb-2" style="height: 215px;">
                                        <canvas id="myPieChart"></canvas>
                                    </div>
                                    <div class="mt-2 text-center small font-weight-bold">
                                        Total: <?= $total_insiden; ?> Insiden
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
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="vendor/chart.js/Chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            $('#dataTableBehavior').DataTable({
                "order": [[ 0, "desc" ]], // Diurutkan dari yang terbaru
                "pageLength": 5, 
                "language": { "search": "Cari Log:", "lengthMenu": "Tampil _MENU_ data" }
            });

            // Sistem Polling Alert Pop-up SweetAlert2
            function cekAlertBehavior() {
                $.ajax({
                    url: 'cek_alert.php', type: 'GET', dataType: 'json',
                    success: function(data) {
                        if(data.ada_notifikasi) {
                            Swal.fire({
                                icon: 'warning', title: data.tipe, html: data.pesan, confirmButtonText: 'Catat di Log', confirmButtonColor: '#4e73df'
                            }).then((result) => {
                                if (result.isConfirmed) { location.reload(); }
                            });
                        }
                    }
                });
            }
            setInterval(cekAlertBehavior, 5000);
        });

        // ================= GRAFIK GARIS (AREA CHART) =================
        var ctx = document.getElementById("myAreaChart");
        if(ctx) {
            var myLineChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?= $label_jam; ?>,
                    datasets: [{
                        label: "Insiden",
                        lineTension: 0.3, backgroundColor: "rgba(78, 115, 223, 0.05)", borderColor: "rgba(78, 115, 223, 1)",
                        pointRadius: 3, pointBackgroundColor: "rgba(78, 115, 223, 1)", pointBorderColor: "rgba(78, 115, 223, 1)",
                        pointHoverRadius: 3, pointHoverBackgroundColor: "rgba(78, 115, 223, 1)", pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                        pointHitRadius: 10, pointBorderWidth: 2,
                        data: <?= $nilai_jam; ?>,
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    layout: { padding: { left: 10, right: 25, top: 25, bottom: 0 } },
                    scales: {
                        xAxes: [{ gridLines: { display: false, drawBorder: false }, ticks: { maxTicksLimit: 12 } }],
                        yAxes: [{
                            ticks: { maxTicksLimit: 5, padding: 10, beginAtZero: true, stepSize: 1 },
                            gridLines: { color: "rgb(234, 236, 244)", zeroLineColor: "rgb(234, 236, 244)", drawBorder: false, borderDash: [2], zeroLineBorderDash: [2] }
                        }],
                    },
                    legend: { display: false },
                    tooltips: {
                        backgroundColor: "rgb(255,255,255)", bodyFontColor: "#858796", titleFontColor: '#6e707e', titleFontSize: 14,
                        borderColor: '#dddfeb', borderWidth: 1, xPadding: 15, yPadding: 15, displayColors: false, intersect: false, mode: 'index', caretPadding: 10
                    }
                }
            });
        }

        // ================= GRAFIK LINGKARAN (PIE CHART) =================
        var ctxPie = document.getElementById("myPieChart");
        if(ctxPie && <?= $total_insiden; ?> > 0) {
            var myPieChart = new Chart(ctxPie, {
                type: 'doughnut',
                data: {
                    labels: <?= $json_label_pie; ?>,
                    datasets: [{
                        data: <?= $json_nilai_pie; ?>,
                        backgroundColor: ['#e74a3b', '#f6c23e', '#4e73df'],
                        hoverBackgroundColor: ['#be2617', '#dda20a', '#2e59d9'],
                        hoverBorderColor: "rgba(234, 236, 244, 1)",
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    tooltips: { backgroundColor: "rgb(255,255,255)", bodyFontColor: "#858796", borderColor: '#dddfeb', borderWidth: 1, xPadding: 15, yPadding: 15, displayColors: false, caretPadding: 10 },
                    legend: { display: true, position: 'bottom', labels: { boxWidth: 12 } },
                    cutoutPercentage: 70,
                },
            });
        }
    </script>
</body>
</html>