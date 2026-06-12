<?php
$page_title   = "User Behavior Detection";
$page_heading = "User Behavior Detection";

include 'koneksi.php';

date_default_timezone_set('Asia/Jakarta');
$last_update = date('d M Y | H:i:s');

function formatBytes($bytes)
{
    if ($bytes >= 1073741824) {
        return round($bytes / 1073741824, 2) . ' GB';
    }

    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 2) . ' MB';
    }

    if ($bytes >= 1024) {
        return round($bytes / 1024, 2) . ' KB';
    }

    return $bytes . ' B';
}

// 1. Ambil data HARI INI untuk Tabel
$batas = 5;
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$halaman_awal = ($halaman > 1) ? (($halaman * $batas) - $batas) : 0;

$query_jumlah = mysqli_query($conn,"SELECT COUNT(*) as total FROM logs WHERE (kategori='DOWNLOAD' OR (kategori='SOSMED' AND url_akses='AKSES_20_MENIT')) AND DATE(waktu)=CURDATE()");

$jumlah_data = mysqli_fetch_assoc($query_jumlah)['total'];
$total_halaman = ceil($jumlah_data / $batas);

$query_alerts = mysqli_query($conn,"SELECT * FROM logs WHERE (kategori='DOWNLOAD' OR (kategori='SOSMED' AND url_akses='AKSES_20_MENIT')) AND DATE(waktu)=CURDATE() ORDER BY id DESC LIMIT $halaman_awal,$batas");

// 2. Siapkan Data untuk Grafik Garis (Tren per Jam Hari Ini)
$grafik_garis = mysqli_query($conn, "SELECT HOUR(waktu) as jam, COUNT(*) as total FROM logs WHERE (kategori='DOWNLOAD' OR (kategori='SOSMED' AND url_akses='AKSES_20_MENIT')) AND DATE(waktu)=CURDATE() GROUP BY HOUR(waktu)");

$data_jam = array_fill(0, 24, 0); 
while($row = mysqli_fetch_assoc($grafik_garis)) {
    $data_jam[$row['jam']] = $row['total'];
}
$label_jam = json_encode(array_map(function($jam) { return sprintf("%02d:00", $jam); }, range(0, 23)));
$nilai_jam = json_encode(array_values($data_jam));

// 3. Siapkan Data untuk Grafik Pie (Persentase Tipe Alert Hari Ini)
$grafik_pie = mysqli_query($conn, "SELECT kategori, COUNT(*) as total FROM logs WHERE (kategori='DOWNLOAD' OR (kategori='SOSMED' AND url_akses='AKSES_20_MENIT')) AND DATE(waktu)=CURDATE() GROUP BY kategori");

$label_pie = [];
$nilai_pie = [];
$total_insiden = 0;
while($row = mysqli_fetch_assoc($grafik_pie)) {
    $label_pie[] = $row['kategori'];
    $nilai_pie[] = $row['total'];
    $total_insiden += $row['total'];
}
$json_label_pie = json_encode($label_pie);
$json_nilai_pie = json_encode($nilai_pie);

include 'includes/header.php';
?>

    <style>
        .badge-download { background-color: #28a745; color: white; font-size: 0.75rem; }
        .badge-stream   { background-color: #007bff; color: white; font-size: 0.75rem; }
    </style>

        <?php include 'includes/sidebar.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                
                <?php include 'includes/topbar.php'; ?>

                <div class="container-fluid">
                    <div class="card shadow mb-3">
                        <div class="card-header bg-primary">
                            <h6 class="m-0 font-weight-bold text-white" style="font-size: 0.9rem;">Log Aktivitas Transfer Data & Media Sosial (Hari Ini)</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                        <?php
                            $total_device = mysqli_num_rows(
                                mysqli_query(
                                    $conn,
                                    "SELECT DISTINCT ip_address
                                    FROM logs
                                    WHERE kategori IN ('DOWNLOAD','SOSMED')
                                    AND DATE(waktu)=CURDATE()"
                                )
                            );
                            ?>

                            <div class="alert alert-light border-left-primary mb-3">
                                <strong><?= $total_device; ?></strong>
                                perangkat terdeteksi melakukan aktivitas
                                Download atau Sosial Media hari ini.
                            </div>
                                <table class="table table-bordered table-sm table-striped table-hover text-center" id="dataTableBehavior" width="100%" cellspacing="0">
                                    <thead class="bg-gray-100 text-dark">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="15%">Waktu</th>
                                            <th width="18%">Host / Device</th>
                                            <th width="15%">Kategori</th>
                                            <th>Detail Singkat</th>
                                            <th width="10%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $no = 1; 
                                        while($row = mysqli_fetch_assoc($query_alerts)): 
                                            $badge_class = ($row['kategori'] == 'DOWNLOAD') ? 'badge-download' : 'badge-stream';
                                            $keterangan_singkat = strlen($row['url_akses']) > 60 ? substr(strip_tags($row['url_akses']), 0, 60) . "..." : strip_tags($row['url_akses']);
                                            if($row['kategori'] == 'SOSMED'){
                                            $penjelasan = "Terdeteksi akses ke platform media sosial yang berpotensi mengurangi produktivitas kerja.";
                                            }
                                            else{
                                                $penjelasan = "Terdeteksi aktivitas transfer data berukuran besar yang berpotensi menghabiskan bandwidth jaringan.";
                                            }
                                                                                ?>
                                        <tr>
                                            <td class="font-weight-bold"><?= $row['id']; ?></td>
                                            <td class="small"><?= $row['waktu']; ?></td>
                                            <td class="text-left align-middle">

                                                <strong>
                                                    <?= !empty($row['hostname'])
                                                        ? htmlspecialchars($row['hostname'])
                                                        : 'Tidak Terdeteksi'; ?>
                                                </strong>

                                                <br>

                                                <small class="text-primary font-weight-bold">
                                                    <?= htmlspecialchars($row['ip_address']); ?>
                                                </small>

                                                <br>

                                                <small class="text-danger">
                                                    <?= htmlspecialchars($row['mac_address']); ?>
                                                </small>

                                            </td>
                                            <td><span class="badge <?= $badge_class ?> px-2"><?= $row['kategori']; ?></span></td>
                                            <td class="text-left small">
                                                <?= htmlspecialchars($keterangan_singkat); ?>
                                        </td>
                                            <td>
                                                <button class="btn btn-info btn-sm p-1" data-toggle="modal" data-target="#detailModal<?= $row['id']; ?>" title="Lihat Detail">
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

                                                                <?php
                                                                $methodClass =
                                                                    ($row['resolve_method'] == 'DHCP')
                                                                    ? 'badge-success'
                                                                    : 'badge-warning';
                                                                ?>

                                                                <div class="row align-items-stretch">

                                                                    <div class="col-md-6">

                                                                        <table class="table table-borderless table-sm">

                                                                            <tr>
                                                                                <th width="40%">Hostname</th>
                                                                                <td><?= !empty($row['hostname']) ? $row['hostname'] : 'Tidak Terdeteksi'; ?></td>
                                                                            </tr>

                                                                            <tr>
                                                                                <th>IP Address</th>
                                                                                <td><?= $row['ip_address']; ?></td>
                                                                            </tr>

                                                                            <tr>
                                                                                <th>MAC Address</th>
                                                                                <td><code><?= $row['mac_address']; ?></code></td>
                                                                            </tr>

                                                                            <tr>
                                                                                <th>Waktu</th>
                                                                                <td><?= $row['waktu']; ?></td>
                                                                            </tr>

                                                                        </table>

                                                                    </div>

                                                                    <div class="col-md-6">

                                                                        <table class="table table-borderless table-sm">

                                                                            <tr>
                                                                                <th width="40%">Kategori</th>
                                                                                <td>
                                                                                    <span class="badge <?= $badge_class ?>">
                                                                                        <?= $row['kategori']; ?>
                                                                                    </span>
                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <th>Resolve</th>
                                                                                <td>
                                                                                    <span class="badge <?= $methodClass ?>">
                                                                                        <?= $row['resolve_method']; ?>
                                                                                    </span>
                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <?php if($row['kategori']=='DOWNLOAD'): ?>
                                                                                <tr>
                                                                                    <th>Threshold</th>
                                                                                    <td><?= formatBytes($row['bytes_download']); ?></td>
                                                                                </tr>
                                                                                <?php endif; ?>
                                                                            </tr>

                                                                            <tr>
                                                                                <th>Status</th>
                                                                                <td>
                                                                                    <span class="badge badge-success">
                                                                                        Terdeteksi
                                                                                    </span>
                                                                                </td>
                                                                            </tr>

                                                                        </table>

                                                                    </div>

                                                                </div>

                                                                <hr>

                                                                <h6 class="font-weight-bold text-primary">
                                                                    Analisis Aktivitas
                                                                </h6>

                                                                <div class="alert alert-light border">

                                                                    <?= $penjelasan; ?>

                                                                </div>

                                                                <?php if($row['kategori'] == 'DOWNLOAD'): ?>
                                                                <div class="alert alert-warning">
                                                                    Threshold transfer data terlampaui (>30 MB per koneksi TCP aktif).
                                                                </div>
                                                                <?php endif; ?>

                                                                <h6 class="font-weight-bold text-primary">
                                                                    Aktivitas Terdeteksi
                                                                </h6>

                                                                <div class="alert alert-secondary">

                                                                    <?= htmlspecialchars($row['url_akses']); ?>

                                                                </div>

                                                                <?php
                                                                    $dst = trim($row['dst_address']);
                                                                    ?>

                                                                    <?php if(
                                                                        !empty($dst)
                                                                        && !in_array(
                                                                            strtoupper($dst),
                                                                            ['N/A', '-', 'TIDAK-TERDETEKSI', 'TIDAK-DIGUNAKAN']
                                                                        )
                                                                    ): ?>

                                                                    <h6 class="font-weight-bold text-primary">
                                                                        Destination Address
                                                                    </h6>

                                                                    <div class="alert alert-info mb-0">
                                                                        <?= htmlspecialchars($dst); ?>
                                                                    </div>

                                                                    <?php endif; ?>

                                                            </div>
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
                            <?php if($total_halaman > 1): ?>
                                                                    <nav class="mt-2">
                                                                        <ul class="pagination pagination-sm justify-content-end mb-0">

                                                                            <li class="page-item <?= ($halaman <= 1) ? 'disabled' : ''; ?>">
                                                                                <a class="page-link"
                                                                                href="?halaman=<?= $halaman-1; ?>">
                                                                                Previous
                                                                                </a>
                                                                            </li>

                                                                            <?php for($x=1;$x<=$total_halaman;$x++): ?>
                                                                            <li class="page-item <?= ($halaman==$x)?'active':''; ?>">
                                                                                <a class="page-link"
                                                                                href="?halaman=<?= $x; ?>">
                                                                                <?= $x; ?>
                                                                                </a>
                                                                            </li>
                                                                            <?php endfor; ?>

                                                                            <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : ''; ?>">
                                                                                <a class="page-link"
                                                                                href="?halaman=<?= $halaman+1; ?>">
                                                                                Next
                                                                                </a>
                                                                            </li>

                                                                        </ul>
                                                                    </nav>
                                                                    <?php endif; ?>
                        </div>
                    </div>

                    <div class="row align-items-stretch">
                        <div class="col-xl-8 col-lg-7 d-flex">
                            <div class="card shadow mb-3 w-100 h-100">
                                <div class="card-header"><h6 class="m-0 font-weight-bold text-primary" style="font-size: 0.9rem;">Trafik Behavior Hari Ini</h6></div>
                                <div class="card-body"><div class="chart-area" style="height: 200px;"><canvas id="myAreaChart"></canvas></div></div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-5 d-flex">
                            <div class="card shadow mb-3 w-100 h-100">
                                <div class="card-header bg-white">
                                    <h6 class="m-0 font-weight-bold text-primary" style="font-size: 0.9rem;">Persentase (Hari Ini)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-pie pt-2 pb-2" style="height: 160px;">
                                        <canvas id="myPieChart"></canvas>
                                    </div>
                                    <?php if($total_insiden > 0): ?>

                                    <div class="small mt-2 px-3">

                                    <?php
                                    foreach($label_pie as $i => $label):

                                    $persen = round(
                                        ($nilai_pie[$i] / $total_insiden) * 100,
                                        1
                                    );
                                    ?>

                                    <div class="d-flex justify-content-between mb-1">
                                        <span><?= $label; ?></span>
                                        <strong><?= $persen; ?>%</strong>
                                    </div>

                                    <?php endforeach; ?>

                                    </div>

                                    <?php endif; ?>

                                    <hr class="my-2">

                                    <div class="text-center small font-weight-bold">
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

    <script src="vendor/chart.js/Chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
           
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
                    layout: { padding: 0 },
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

        var ctxPie = document.getElementById("myPieChart");
        if(ctxPie && <?= $total_insiden; ?> > 0) {
            var myPieChart = new Chart(ctxPie, {
                type: 'doughnut',
                data: {
                    labels: <?= $json_label_pie; ?>,
                    datasets: [{
                        data: <?= $json_nilai_pie; ?>,
                        backgroundColor: ['#2675dd', '#00ff22', '#4e73df'],
                        hoverBackgroundColor: ['#2675dd', '#00ff22', '#2e59d9'],
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