<?php
$page_title   = "Profiling User";
$page_heading = "Profiling User";

include 'koneksi.php';

// Query Profiling: Dikelompokkan berdasarkan MAC Address
$filter_kat = isset($_GET['kategori'])
    ? mysqli_real_escape_string($conn, $_GET['kategori'])
    : '';

$sql = "SELECT
            mac_address,
            MAX(hostname) as hostname,
            GROUP_CONCAT(DISTINCT ip_address SEPARATOR ', ') as used_ips,
            COUNT(*) as total_insiden,
            SUM(CASE WHEN kategori='VPN' THEN 1 ELSE 0 END) as vpn_count,
            SUM(CASE WHEN kategori='JUDOL' THEN 1 ELSE 0 END) as judol_count,
            SUM(CASE WHEN kategori='DOWNLOAD' THEN 1 ELSE 0 END) as download_count,
            SUM(CASE WHEN kategori='SOSMED' THEN 1 ELSE 0 END) as sosmed_count,
            MAX(waktu) as terakhir_terdeteksi
        FROM logs
        WHERE 1=1";

if ($filter_kat != '') {
    $sql .= " AND kategori='$filter_kat'";
}

$sql .= " GROUP BY mac_address
          ORDER BY total_insiden DESC";

$query = mysqli_query($conn, $sql);

// SET WAKTU KE WIB (Asia/Jakarta) untuk Last Update
date_default_timezone_set('Asia/Jakarta');
$last_update = date('d M Y | H:i:s');

include 'includes/header.php';
?>
<style>
    #dataTable_filter {
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    #dataTable_filter label {
        margin-bottom: 0;
    }

    #filter-dropdown {
        margin-left: 15px;
    }
</style>      
        <?php include 'includes/sidebar.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content"> 

                <?php include 'includes/topbar.php'; ?>
                
                <div class="container-fluid">
                    <div class="card shadow mb-4">
                        <div class="card-header py-2 bg-primary">
                            <h6 class="m-0 font-weight-bold text-white">Daftar Akumulasi Pelanggaran Perangkat</h6>
                        </div>
                        <div class="card-body">
                            <div id="filter-dropdown" style="display:none;">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle"
                                            type="button"
                                            data-toggle="dropdown">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>

                                   <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">

                                        <a class="dropdown-item" href="profil_user.php">
                                            <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                                            Semua Data
                                        </a>

                                        <div class="dropdown-divider"></div>

                                        <a class="dropdown-item" href="profil_user.php?kategori=VPN">
                                            <i class="fas fa-shield-alt fa-sm fa-fw mr-2 text-warning"></i>
                                            VPN
                                        </a>

                                        <a class="dropdown-item" href="profil_user.php?kategori=JUDOL">
                                            <i class="fas fa-dice fa-sm fa-fw mr-2 text-danger"></i>
                                            JUDOL
                                        </a>

                                        <a class="dropdown-item" href="profil_user.php?kategori=DOWNLOAD">
                                            <i class="fas fa-download fa-sm fa-fw mr-2 text-success"></i>
                                            DOWNLOAD
                                        </a>

                                        <a class="dropdown-item" href="profil_user.php?kategori=SOSMED">
                                            <i class="fas fa-hashtag fa-sm fa-fw mr-2 text-primary"></i>
                                            SOSMED
                                        </a>

                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm table-striped" id="dataTable" width="100%" cellspacing="0">
                                    <thead class="bg-gray-100 text-center">
                                        <tr>
                                            <th>No</th> 
                                            <th>MAC Address</th>
                                            <th>Hostname</th>
                                            <th>IP Digunakan</th>
                                            <th>Kategori Terdeteksi</th>
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
                                            <td class="text-center font-weight-bold"></td>
                                            <td class="text-center"><code><?= $row['mac_address']; ?></code></td>
                                            <td><?= $row['hostname'] ?: '<em class="text-muted">Unknown</em>'; ?></td>
                                            <td><small><?= $row['used_ips']; ?></small></td>
                                            <td>
                                                <?php if($row['vpn_count'] > 0): ?>
                                                    <span class="badge badge-warning">VPN</span>
                                                <?php endif; ?>

                                                <?php if($row['download_count'] > 0): ?>
                                                    <span class="badge badge-success">DOWNLOAD</span>
                                                <?php endif; ?>

                                                <?php if($row['judol_count'] > 0): ?>
                                                    <span class="badge badge-danger">JUDOL</span>
                                                <?php endif; ?>

                                                <?php if($row['sosmed_count'] > 0): ?>
                                                    <span class="badge badge-primary">SOSMED</span>
                                                <?php endif; ?>
                                            </td>
                                            

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

    <?php include 'includes/scripts.php'; ?>

    <script src="js/sb-admin-2.min.js"></script>

    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
    
    <script>
        $(document).ready(function () {

            var table = $('#dataTable').DataTable({
                order: [[5, "desc"]],
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                language: {
                    search: "",
                    searchPlaceholder: "Cari perangkat...",
                    lengthMenu: "Tampilkan _MENU_ data",
                    paginate: {
                        previous: "Previous",
                        next: "Next"
                    }
                }
            });

            table.on('draw.dt', function () {
                var PageInfo = table.page.info();

                table.column(0, {page:'current'}).nodes().each(function(cell, i){
                    cell.innerHTML = i + 1 + PageInfo.start;
                });
            });

            table.draw();

            $('#filter-dropdown')
                .show()
                .appendTo('#dataTable_filter');

        });
    </script>
</body>
</html>