<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                <div class="sidebar-brand-icon"><i class="fas fa-shield-alt"></i></div>
                <div class="sidebar-brand-text mx-3">NetMonitor</div>
            </a>
            <hr class="sidebar-divider my-0">
            <li class="nav-item <?= $current_page == 'index.php' ? 'active' : '' ?>">
                <a class="nav-link" style="padding: 0.5rem 1rem;" href="index.php"><i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span></a>
            </li>
            <li class="nav-item <?= $current_page == 'behavior_user.php' ? 'active' : '' ?>">
                <a class="nav-link" style="padding: 0.5rem 1rem;" href="behavior_user.php"><i class="fas fa-fw fa-chart-pie"></i><span>User Behavior</span></a>
            </li>
            <li class="nav-item <?= $current_page == 'profil_user.php' ? 'active' : '' ?>">
                <a class="nav-link" style="padding: 0.5rem 1rem;" href="profil_user.php"><i class="fas fa-fw fa-user"></i><span>Profiling per User</span></a>
            </li>
            <li class="nav-item <?= $current_page == 'riwayat.php' ? 'active' : '' ?>">
                <a class="nav-link" style="padding: 0.5rem 1rem;" href="riwayat.php"><i class="fas fa-fw fa-table"></i><span>Riwayat Insiden</span></a>
            </li>
        </ul>