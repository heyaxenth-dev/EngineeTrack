<?php
include 'includes/authentication.php';
include '../database/conn.php';

$schema_ready = true;
$unreturned_equipment = 0;
$maintenance_items = 0;
$scheduled_venues = 0;
$borrowed_items_total = 0;
$returned_items_total = 0;
$most_borrowed_labels = [];
$most_borrowed_values = [];

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function table_exists($conn, $table_name) {
    $safe_table = mysqli_real_escape_string($conn, $table_name);
    $result = mysqli_query($conn, "SHOW TABLES LIKE '{$safe_table}'");
    return $result && mysqli_num_rows($result) > 0;
}

$required_tables = ['equipment_items', 'reservation_requests', 'reservation_request_items'];
foreach ($required_tables as $table_name) {
    if (!table_exists($conn, $table_name)) {
        $schema_ready = false;
        break;
    }
}

if ($schema_ready) {
    $equipment_totals_result = mysqli_query($conn, "
        SELECT
            COALESCE(SUM(total_quantity - available_quantity), 0) AS borrowed_total,
            COALESCE(SUM(available_quantity), 0) AS available_total,
            SUM(CASE WHEN is_active = 1 AND available_quantity <= 10 THEN 1 ELSE 0 END) AS low_stock_count
        FROM equipment_items
    ");
    if ($equipment_totals_result && $equipment_row = mysqli_fetch_assoc($equipment_totals_result)) {
        $borrowed_items_total = (int) $equipment_row['borrowed_total'];
        $returned_items_total = (int) $equipment_row['available_total'];
        $unreturned_equipment = $borrowed_items_total;
        $maintenance_items = (int) $equipment_row['low_stock_count'];
    }

    $venues_result = mysqli_query($conn, "
        SELECT COUNT(DISTINCT rri.resource_id) AS booked_venues
        FROM reservation_request_items rri
        INNER JOIN reservation_requests rr ON rr.id = rri.reservation_request_id
        WHERE rri.item_type = 'venue'
          AND rr.status = 'approved'
          AND rr.end_datetime >= NOW()
    ");
    if ($venues_result && $venues_row = mysqli_fetch_assoc($venues_result)) {
        $scheduled_venues = (int) $venues_row['booked_venues'];
    }

    $most_borrowed_result = mysqli_query($conn, "
        SELECT
            rri.resource_name,
            SUM(rri.quantity) AS total_borrowed
        FROM reservation_request_items rri
        INNER JOIN reservation_requests rr ON rr.id = rri.reservation_request_id
        WHERE rri.item_type = 'equipment'
          AND rr.status IN ('approved', 'pending')
        GROUP BY rri.resource_id, rri.resource_name
        ORDER BY total_borrowed DESC
        LIMIT 5
    ");
    if ($most_borrowed_result) {
        while ($row = mysqli_fetch_assoc($most_borrowed_result)) {
            $most_borrowed_labels[] = $row['resource_name'];
            $most_borrowed_values[] = (int) $row['total_borrowed'];
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
                <div class="card border-0 shadow-sm" style="background: #f23d3d; color: #fff;">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <h4 class="mb-0"><?= $unreturned_equipment ?></h4>
                            <small>Unreturned Equipment</small>
                        </div>
                        <i class="bi bi-exclamation-triangle-fill fs-2"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm" style="background: #ff8a00; color: #fff;">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <h4 class="mb-0"><?= $maintenance_items ?></h4>
                            <small>For Maintenance (Low Stock)</small>
                        </div>
                        <i class="bi bi-tools fs-2"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-12">
                <div class="card border-0 shadow-sm" style="background: #1f7ed0; color: #fff;">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <h4 class="mb-0"><?= $scheduled_venues ?></h4>
                            <small>Scheduled Utilized Venue</small>
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
                        <h5 class="card-title">Recent Activity</h5>
                        <?php
                        $activity_total = max(1, $borrowed_items_total + $returned_items_total + $maintenance_items);
                        $borrowed_pct = min(100, round(($borrowed_items_total / $activity_total) * 100));
                        $returned_pct = min(100, round(($returned_items_total / $activity_total) * 100));
                        $maintenance_pct = min(100, round(($maintenance_items / $activity_total) * 100));
                        ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <small>Borrowed: <?= $borrowed_items_total ?> items</small>
                                <small><?= $borrowed_pct ?>%</small>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-primary" style="width: <?= $borrowed_pct ?>%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <small>Returned/Available: <?= $returned_items_total ?> items</small>
                                <small><?= $returned_pct ?>%</small>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-success" style="width: <?= $returned_pct ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between">
                                <small>Maintenance: <?= $maintenance_items ?> items</small>
                                <small><?= $maintenance_pct ?>%</small>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-warning" style="width: <?= $maintenance_pct ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Most Borrowed Equipment</h5>
                        <div id="mostBorrowedChart"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var chartEl = document.querySelector('#mostBorrowedChart');
    if (!chartEl) return;

    var labels = <?= json_encode($most_borrowed_labels) ?>;
    var values = <?= json_encode($most_borrowed_values) ?>;

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
            text: 'No equipment usage data yet.'
        }
    }).render();
});
</script>

<?php
include 'includes/footer.php';
?>