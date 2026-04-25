<?php
include 'includes/authentication.php';
include '../database/conn.php';

$pending_requests = 0;
$approved_today = 0;
$active_venues = 0;
$low_stock_count = 0;
$pending_items = [];
$calendar_events = [];
$schema_ready = true;

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function table_exists($conn, $table_name) {
    $safe_table = mysqli_real_escape_string($conn, $table_name);
    $result = mysqli_query($conn, "SHOW TABLES LIKE '{$safe_table}'");
    return $result && mysqli_num_rows($result) > 0;
}

$required_tables = ['venues', 'equipment_items', 'reservation_requests', 'reservation_request_items', 'users'];
foreach ($required_tables as $table_name) {
    if (!table_exists($conn, $table_name)) {
        $schema_ready = false;
        break;
    }
}

if ($schema_ready) {
    $stats_result = mysqli_query($conn, "
        SELECT
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
            SUM(CASE WHEN status = 'approved' AND DATE(updated_at) = CURDATE() THEN 1 ELSE 0 END) AS approved_today_count
        FROM reservation_requests
    ");
    if ($stats_result && $stats_row = mysqli_fetch_assoc($stats_result)) {
        $pending_requests = (int) ($stats_row['pending_count'] ?? 0);
        $approved_today = (int) ($stats_row['approved_today_count'] ?? 0);
    }

    $venues_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM venues WHERE is_active = 1");
    if ($venues_result && $venues_row = mysqli_fetch_assoc($venues_result)) {
        $active_venues = (int) $venues_row['total'];
    }

    $low_stock_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM equipment_items WHERE is_active = 1 AND available_quantity <= 10");
    if ($low_stock_result && $stock_row = mysqli_fetch_assoc($low_stock_result)) {
        $low_stock_count = (int) $stock_row['total'];
    }

    $pending_query = "
        SELECT
            rr.id,
            rr.request_code,
            rr.event_title,
            rr.department,
            rr.request_date,
            rr.start_datetime,
            rr.end_datetime,
            rr.status,
            COALESCE(u.name, u.username, CONCAT('User #', rr.requested_by_user_id)) AS requester_name,
            GROUP_CONCAT(
                CASE
                    WHEN rri.item_type = 'venue' THEN CONCAT('Venue: ', rri.resource_name)
                    WHEN rri.item_type = 'equipment' THEN CONCAT('Equipment: ', rri.resource_name, ' (x', rri.quantity, ')')
                    ELSE rri.resource_name
                END
                ORDER BY rri.item_type, rri.resource_name
                SEPARATOR ' | '
            ) AS requested_resources
        FROM reservation_requests rr
        LEFT JOIN users u ON u.id = rr.requested_by_user_id
        LEFT JOIN reservation_request_items rri ON rri.reservation_request_id = rr.id
        GROUP BY rr.id
        ORDER BY
            CASE WHEN rr.status = 'pending' THEN 0 ELSE 1 END,
            rr.created_at DESC
        LIMIT 20
    ";
    $pending_result = mysqli_query($conn, $pending_query);
    if ($pending_result) {
        while ($row = mysqli_fetch_assoc($pending_result)) {
            $pending_items[] = $row;
            $calendar_events[] = [
                'title' => $row['event_title'] . ' (' . ucfirst($row['status']) . ')',
                'start' => $row['start_datetime'],
                'end' => $row['end_datetime'],
                'color' => $row['status'] === 'approved' ? '#198754' : ($row['status'] === 'rejected' ? '#dc3545' : '#f59f00'),
            ];
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
                Reservation tables are not ready yet. Run `database/migrations/2026_04_25_create_staff_reservation_tables.sql`
                and refresh this page.
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-xxl-3 col-md-6">
                <div class="card info-card pending-requests-card">
                    <div class="card-body">
                        <h5 class="card-title">Pending Requests</h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-clock-fill"></i>
                            </div>
                            <div class="ps-3">
                                <h6><?= $pending_requests ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-3 col-md-6">
                <div class="card info-card approved-today-card">
                    <div class="card-body">
                        <h5 class="card-title">Approved Today</h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="ps-3">
                                <h6><?= $approved_today ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-3 col-md-6">
                <div class="card info-card active-venues-card">
                    <div class="card-body">
                        <h5 class="card-title">Active Venues</h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bx bxs-building"></i>
                            </div>
                            <div class="ps-3">
                                <h6><?= $active_venues ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-3 col-md-6">
                <div class="card info-card repairs-ongoing-card">
                    <div class="card-body">
                        <h5 class="card-title">Low Stock Items</h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            </div>
                            <div class="ps-3">
                                <h6><?= $low_stock_count ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Request Queue</h5>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Request Code</th>
                                <th>Requester</th>
                                <th>Event</th>
                                <th>Department</th>
                                <th>Resources</th>
                                <th>Schedule</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($pending_items)): ?>
                                <?php foreach ($pending_items as $item): ?>
                                    <?php $status = strtolower($item['status']); ?>
                                    <tr>
                                        <td><?= h($item['request_code']) ?></td>
                                        <td><?= h($item['requester_name']) ?></td>
                                        <td><?= h($item['event_title']) ?></td>
                                        <td><?= h($item['department']) ?></td>
                                        <td><?= h($item['requested_resources'] ?: 'N/A') ?></td>
                                        <td>
                                            <?= date('M d, Y h:i A', strtotime($item['start_datetime'])) ?><br>
                                            <small class="text-muted">to <?= date('h:i A', strtotime($item['end_datetime'])) ?></small>
                                        </td>
                                        <td>
                                            <?php
                                            $badge_class = $status === 'approved' ? 'bg-success' : ($status === 'rejected' ? 'bg-danger' : 'bg-warning');
                                            ?>
                                            <span class="badge <?= $badge_class ?>"><?= ucfirst(h($item['status'])) ?></span>
                                        </td>
                                        <td>
                                            <?php if ($status === 'pending'): ?>
                                                <div class="d-flex gap-2">
                                                    <form method="POST" action="api/update-reservation-status.php">
                                                        <input type="hidden" name="request_id" value="<?= (int) $item['id'] ?>">
                                                        <input type="hidden" name="action" value="approve">
                                                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                                    </form>
                                                    <form method="POST" action="api/update-reservation-status.php">
                                                        <input type="hidden" name="request_id" value="<?= (int) $item['id'] ?>">
                                                        <input type="hidden" name="action" value="reject">
                                                        <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                                    </form>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted small">Reviewed</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No reservation requests found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0">Reservation Calendar</h5>
                    <div class="d-flex align-items-center gap-2">
                        <label for="calendarStatusFilter" class="small text-muted mb-0">View:</label>
                        <select id="calendarStatusFilter" class="form-select form-select-sm">
                            <option value="all">All Requests</option>
                            <option value="pending">Pending Only</option>
                            <option value="approved">Approved Only</option>
                            <option value="rejected">Rejected Only</option>
                        </select>
                    </div>
                </div>
                <div id="calendar"></div>
            </div>
        </div>
    </section>
</main>

<script>
    window.scheduleReservationEvents = <?= json_encode($calendar_events) ?>;
    window.scheduleReservationCalendarConfig = {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        }
    };
</script>
<script src="api/fullcalendar.js"></script>

<?php
include 'includes/footer.php';
?>