<?php 
// Function to check if page exists, fallback to 404 if not
function get_page_link($page_name) {
    $file_path = $page_name . '.php';
    if (file_exists($file_path)) {
        return $file_path;
    } else {
       return 'page-error-404.php';
    }
}
?>
<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">
        <li class="nav-item">
            <a class="nav-link <?= ($current_page == 'dashboard') ? '' : 'collapsed' ?>"
                href="<?= get_page_link('dashboard')?>">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <!-- End Dashboard Nav -->

        <li class="nav-item">
            <a class="nav-link <?= ($current_page == 'schedule-reservation') ? '' : 'collapsed' ?>"
                href="<?= get_page_link('schedule-reservation') ?>">
                <i class="bi bi-calendar-week"></i>
                <span>Schedule and Reservation</span>
            </a>
        </li>
        <!-- End Schedule and Reservation Page Nav -->

        <li class="nav-item">
            <a class="nav-link <?= ($current_page == 'utilization') ? '' : 'collapsed' ?>"
                href="<?= get_page_link('utilization') ?>">
                <i class="bi bi-gear-wide-connected"></i>
                <span>Utilization</span>
            </a>
        </li>
        <!-- End Utilization Page Nav -->

        <li class="nav-item">
            <a class="nav-link <?= ($current_page == 'equipment-inventory') ? '' : 'collapsed' ?>"
                href="<?= get_page_link('equipment-inventory') ?>">
                <i class="bi bi-box-seam"></i>
                <span>Equipment and Inventory</span>
            </a>
        </li>
        <!-- End Equipment and Inventory Page Nav -->

        <li class="nav-item">
            <a class="nav-link <?= ($current_page == 'reports') ? '' : 'collapsed' ?>"
                href="<?= get_page_link('reports') ?>">
                <i class="bi bi-clipboard-data"></i>
                <span>Reports</span>
            </a>
        </li>
        <!-- End Reports Page Nav -->

        <li class="nav-heading">Personal Pages</li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="users-profile.html">
                <i class="bi bi-person"></i>
                <span>Profile</span>
            </a>
        </li>
        <!-- End Profile Page Nav -->

        <li class="nav-item">
            <a class="nav-link collapsed" href="logout.php">
                <i class="bi bi-box-arrow-in-left"></i>
                <span>Log out</span>
            </a>
        </li>
        <!-- End Login Page Nav -->
    </ul>
</aside>
<!-- End Sidebar-->