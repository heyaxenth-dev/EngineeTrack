<?php
include 'includes/authentication.php';
include '../database/conn.php';

$current_user_id = (int) ($_SESSION['user_id'] ?? 0);
$schema_ready = true;
$my_pending_requests = 0;
$my_approved_today = 0;
$my_booked_venues = 0;
$my_equipment_requested = 0;
$most_requested_labels = [];
$most_requested_values = [];

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function table_exists($conn, $table_name) {
    $safe_table = mysqli_real_escape_string($conn, $table_name);
    $result = mysqli_query($conn, "SHOW TABLES LIKE '{$safe_table}'");
    return $result && mysqli_num_rows($result) > 0;
}

$required_tables = ['reservation_requests', 'reservation_request_items'];
foreach ($required_tables as $table_name) {
    if (!table_exists($conn, $table_name)) {
        $schema_ready = false;
        break;
    }
}

if ($schema_ready && $current_user_id > 0) {
    $counts_result = mysqli_query($conn, "
        SELECT
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
            SUM(CASE WHEN status = 'approved' AND DATE(updated_at) = CURDATE() THEN 1 ELSE 0 END) AS approved_today_count
        FROM reservation_requests
        WHERE requested_by_user_id = {$current_user_id}
    ");
    if ($counts_result && $counts_row = mysqli_fetch_assoc($counts_result)) {
        $my_pending_requests = (int) ($counts_row['pending_count'] ?? 0);
        $my_approved_today = (int) ($counts_row['approved_today_count'] ?? 0);
    }

    $venues_result = mysqli_query($conn, "
        SELECT COUNT(DISTINCT rri.resource_id) AS booked_venues
        FROM reservation_request_items rri
        INNER JOIN reservation_requests rr ON rr.id = rri.reservation_request_id
        WHERE rr.requested_by_user_id = {$current_user_id}
          AND rri.item_type = 'venue'
          AND rr.status = 'approved'
          AND rr.end_datetime >= NOW()
    ");
    if ($venues_result && $venues_row = mysqli_fetch_assoc($venues_result)) {
        $my_booked_venues = (int) $venues_row['booked_venues'];
    }

    $equipment_total_result = mysqli_query($conn, "
        SELECT COALESCE(SUM(rri.quantity), 0) AS total_requested
        FROM reservation_request_items rri
        INNER JOIN reservation_requests rr ON rr.id = rri.reservation_request_id
        WHERE rr.requested_by_user_id = {$current_user_id}
          AND rri.item_type = 'equipment'
          AND rr.status IN ('pending', 'approved')
    ");
    if ($equipment_total_result && $equipment_total_row = mysqli_fetch_assoc($equipment_total_result)) {
        $my_equipment_requested = (int) $equipment_total_row['total_requested'];
    }

    $most_requested_result = mysqli_query($conn, "
        SELECT
            rri.resource_name,
            SUM(rri.quantity) AS total_requested
        FROM reservation_request_items rri
        INNER JOIN reservation_requests rr ON rr.id = rri.reservation_request_id
        WHERE rr.requested_by_user_id = {$current_user_id}
          AND rri.item_type = 'equipment'
        GROUP BY rri.resource_id, rri.resource_name
        ORDER BY total_requested DESC
        LIMIT 5
    ");
    if ($most_requested_result) {
        while ($row = mysqli_fetch_assoc($most_requested_result)) {
            $most_requested_labels[] = $row['resource_name'];
            $most_requested_values[] = (int) $row['total_requested'];
        }
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
include 'alert.php';
?>
<main id="main" class="main">
    <div class="pagetitle">
        <h1><?= $renamed_pages[$current_page] ?></h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                <li class="breadcrumb-item active"><?= $renamed_pages[$current_page] ?></li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <?php if (!$schema_ready): ?>
            <div class="alert alert-warning">
                Dashboard data tables are not ready yet. Run `database/migrations/2026_04_25_create_staff_reservation_tables.sql`
                and refresh this page.
            </div>
        <?php endif; ?>

        <div class="row g-3 mb-3">
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm" style="background: #f59f00; color: #fff;">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <h4 class="mb-0"><?= $my_pending_requests ?></h4>
                            <small>My Pending Requests</small>
                        </div>
                        <i class="bi bi-hourglass-split fs-2"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm" style="background: #20c997; color: #fff;">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <h4 class="mb-0"><?= $my_approved_today ?></h4>
                            <small>My Approved Today</small>
                        </div>
                        <i class="bi bi-check-circle-fill fs-2"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-12">
                <div class="card border-0 shadow-sm" style="background: #1f7ed0; color: #fff;">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <h4 class="mb-0"><?= $my_booked_venues ?></h4>
                            <small>My Scheduled Venues</small>
                        </div>
                        <i class="bi bi-calendar2-week fs-2"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">My Request Activity</h5>
                        <?php
                        $activity_total = max(1, $my_pending_requests + $my_approved_today + $my_equipment_requested);
                        $pending_pct = min(100, round(($my_pending_requests / $activity_total) * 100));
                        $approved_pct = min(100, round(($my_approved_today / $activity_total) * 100));
                        $equipment_pct = min(100, round(($my_equipment_requested / $activity_total) * 100));
                        ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <small>Pending Requests: <?= $my_pending_requests ?></small>
                                <small><?= $pending_pct ?>%</small>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-warning" style="width: <?= $pending_pct ?>%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <small>Approved Today: <?= $my_approved_today ?></small>
                                <small><?= $approved_pct ?>%</small>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-success" style="width: <?= $approved_pct ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between">
                                <small>Equipment Requested: <?= $my_equipment_requested ?></small>
                                <small><?= $equipment_pct ?>%</small>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-primary" style="width: <?= $equipment_pct ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">My Most Requested Equipment</h5>
                        <div id="staffRequestedChart"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var chartEl = document.querySelector('#staffRequestedChart');
    if (!chartEl) return;

    var labels = <?= json_encode($most_requested_labels) ?>;
    var values = <?= json_encode($most_requested_values) ?>;

    new ApexCharts(chartEl, {
        series: [{
            data: values
        }],
        chart: {
            type: 'bar',
            height: 320,
            toolbar: {
                show: false
            }
        },
        plotOptions: {
            bar: {
                borderRadius: 4,
                horizontal: true,
                distributed: true
            }
        },
        dataLabels: {
            enabled: true
        },
        xaxis: {
            categories: labels
        },
        colors: ['#0d6efd', '#20c997', '#fd7e14', '#6f42c1', '#198754'],
        noData: {
            text: 'No equipment request data yet.'
        }
    }).render();
});
</script>

<?php
include 'includes/footer.php';
?>