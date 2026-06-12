<?php
$page_title   = "Riwayat Insiden";
$page_heading = "Riwayat Insiden";

include 'koneksi.php';

date_default_timezone_set('Asia/Jakarta');
$last_update = date('d M Y | H:i:s');

include 'includes/header.php';
?>

<style>
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
        
        <?php include 'includes/sidebar.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                
                <?php include 'includes/topbar.php'; ?>
                
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
                                        <a class="dropdown-item" href="riwayat.php?kategori=DOWNLOAD">
                                            <i class="fas fa-download fa-sm fa-fw mr-2 text-success"></i>
                                            DOWNLOAD
                                        </a>

                                        <a class="dropdown-item" href="riwayat.php?kategori=SOSMED">
                                            <i class="fas fa-hashtag fa-sm fa-fw mr-2 text-primary"></i>
                                            SOSMED
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
                                       

                                        // Tampilkan pesan jika data filter tidak ditemukan
                                        if(mysqli_num_rows($query) == 0) {
                                            echo "<tr><td colspan='8'>Data tidak ditemukan untuk filter ini.</td></tr>";
                                        }

                                        while($row = mysqli_fetch_assoc($query)){
                                            switch($row['kategori']) {
                                                case 'VPN':
                                                    $badge_color = 'badge-warning';
                                                    break;

                                                case 'JUDOL':
                                                    $badge_color = 'badge-danger';
                                                    break;

                                                case 'DOWNLOAD':
                                                    $badge_color = 'badge-success';
                                                    break;

                                                case 'SOSMED':
                                                    $badge_color = 'badge-primary';
                                                    break;

                                                default:
                                                    $badge_color = 'badge-secondary';
                                            }
                                            $status_badge = ($row['status'] == 'Resolved') ? 'badge-success' : 'badge-secondary';
                                            
                                            echo "<tr>
                                                    <td></td>
                                                    <td>{$row['waktu']}</td>
                                                    <td><b>{$row['ip_address']}</b></td>
                                                    <td>{$row['hostname']}</td>
                                                    <td>{$row['mac_address']}</td>
                                                    <td><span class='badge {$badge_color} px-2 py-1'>{$row['kategori']}</span></td>
                                                    <td>{$row['jumlah_deteksi']} x</td>
                                                    <td><span class='badge {$status_badge}'>{$row['status']}</span></td>
                                                </tr>";
                                          
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

    
    <?php include 'includes/scripts.php'; ?>

    <script src="js/sb-admin-2.min.js"></script>

    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {

            var table = $('#dataTable').DataTable({
                "order": [[1, "desc"]],
                "columnDefs": [
                    {
                        "targets": 0,
                        "searchable": false,
                        "orderable": false
                    }
                ]
            });

            table.on('order.dt search.dt draw.dt', function () {
                table.column(0, {
                    search:'applied',
                    order:'applied'
                }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();

            $('#filter-dropdown').show().appendTo('#dataTable_filter');
        });
    </script>
        
</body>
</html>