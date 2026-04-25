<?php
include 'includes/authentication.php';
include '../database/conn.php';

$current_user_id = (int) ($_SESSION['user_id'] ?? 0);
$venues = [];
$equipment_items = [];
$recent_requests = [];
$calendar_events = [];
$pending_requests = 0;
$approved_today = 0;
$active_venues = 0;
$available_equipment = 0;
$schema_ready = true;

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function table_exists($conn, $table_name) {
    $safe_table = mysqli_real_escape_string($conn, $table_name);
    $result = mysqli_query($conn, "SHOW TABLES LIKE '{$safe_table}'");
    return $result && mysqli_num_rows($result) > 0;
}

$required_tables = ['venues', 'equipment_items', 'reservation_requests', 'reservation_request_items'];
foreach ($required_tables as $table_name) {
    if (!table_exists($conn, $table_name)) {
        $schema_ready = false;
        break;
    }
}

if ($schema_ready) {
    $venues_result = mysqli_query($conn, "SELECT id, name, location, capacity FROM venues WHERE is_active = 1 ORDER BY name ASC");
    if ($venues_result) {
        while ($row = mysqli_fetch_assoc($venues_result)) {
            $venues[] = $row;
        }
    }

    $equipment_result = mysqli_query($conn, "SELECT id, name, available_quantity FROM equipment_items WHERE is_active = 1 ORDER BY name ASC");
    if ($equipment_result) {
        while ($row = mysqli_fetch_assoc($equipment_result)) {
            $equipment_items[] = $row;
        }
    }

    $stats_query = "SELECT
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
        SUM(CASE WHEN status = 'approved' AND DATE(updated_at) = CURDATE() THEN 1 ELSE 0 END) AS approved_today_count
    FROM reservation_requests
    WHERE requested_by_user_id = {$current_user_id}";
    $stats_result = mysqli_query($conn, $stats_query);
    if ($stats_result && $stats_row = mysqli_fetch_assoc($stats_result)) {
        $pending_requests = (int) ($stats_row['pending_count'] ?? 0);
        $approved_today = (int) ($stats_row['approved_today_count'] ?? 0);
    }

    $active_venues_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM venues WHERE is_active = 1");
    if ($active_venues_result && $active_row = mysqli_fetch_assoc($active_venues_result)) {
        $active_venues = (int) $active_row['total'];
    }

    $available_equipment_result = mysqli_query($conn, "SELECT COALESCE(SUM(available_quantity), 0) AS total FROM equipment_items WHERE is_active = 1");
    if ($available_equipment_result && $available_row = mysqli_fetch_assoc($available_equipment_result)) {
        $available_equipment = (int) $available_row['total'];
    }

    $recent_requests_query = "SELECT
        rr.id,
        rr.request_code,
        rr.event_title,
        rr.department,
        rr.request_date,
        rr.start_datetime,
        rr.end_datetime,
        rr.status,
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
    LEFT JOIN reservation_request_items rri ON rri.reservation_request_id = rr.id
    WHERE rr.requested_by_user_id = {$current_user_id}
    GROUP BY rr.id
    ORDER BY rr.created_at DESC
    LIMIT 8";
    $recent_requests_result = mysqli_query($conn, $recent_requests_query);
    if ($recent_requests_result) {
        while ($row = mysqli_fetch_assoc($recent_requests_result)) {
            $recent_requests[] = $row;
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
                        <h5 class="card-title">My Pending Requests</h5>
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
                        <h5 class="card-title">Available Equipment Qty</h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-box-seam-fill"></i>
                            </div>
                            <div class="ps-3">
                                <h6><?= $available_equipment ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0">Create Reservation Request</h5>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRequestModal">
                        <i class="bi bi-plus-circle me-1"></i>New Request
                    </button>
                </div>
                <small class="text-muted">Create a request for venue, equipment, or both in one submission.</small>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Reservation Calendar</h5>
                <div id="calendar"></div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">My Recent Requests</h5>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Request Code</th>
                                <th>Event</th>
                                <th>Department</th>
                                <th>Requested Resources</th>
                                <th>Schedule</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recent_requests)): ?>
                                <?php foreach ($recent_requests as $request): ?>
                                    <tr>
                                        <td><?= h($request['request_code']) ?></td>
                                        <td><?= h($request['event_title']) ?></td>
                                        <td><?= h($request['department']) ?></td>
                                        <td><?= h($request['requested_resources'] ?: 'N/A') ?></td>
                                        <td>
                                            <?= date('M d, Y h:i A', strtotime($request['start_datetime'])) ?><br>
                                            <small class="text-muted">to <?= date('h:i A', strtotime($request['end_datetime'])) ?></small>
                                        </td>
                                        <td>
                                            <?php
                                            $status = strtolower($request['status']);
                                            $badge_class = $status === 'approved' ? 'bg-success' : ($status === 'rejected' ? 'bg-danger' : 'bg-warning');
                                            ?>
                                            <span class="badge <?= $badge_class ?>"><?= ucfirst(h($request['status'])) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No reservation requests yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</main>

<div class="modal fade" id="createRequestModal" tabindex="-1" aria-labelledby="createRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createRequestModalLabel">Create Reservation Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="api/create-reservation-request.php">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Event Title</label>
                            <input type="text" class="form-control" name="event_title" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department / Office</label>
                            <input type="text" class="form-control" name="department" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Purpose</label>
                            <textarea class="form-control" name="purpose" rows="2" required></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" name="request_date" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Start Time</label>
                            <input type="time" class="form-control" name="start_time" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">End Time</label>
                            <input type="time" class="form-control" name="end_time" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Venue (Optional)</label>
                            <select class="form-select" name="venue_id">
                                <option value="">Select Venue</option>
                                <?php foreach ($venues as $venue): ?>
                                    <option value="<?= (int) $venue['id'] ?>">
                                        <?= h($venue['name']) ?><?= !empty($venue['location']) ? ' - ' . h($venue['location']) : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Equipment (Optional)</label>
                            <select class="form-select" name="equipment_id">
                                <option value="">Select Equipment</option>
                                <?php foreach ($equipment_items as $equipment): ?>
                                    <option value="<?= (int) $equipment['id'] ?>">
                                        <?= h($equipment['name']) ?> (Avail: <?= (int) $equipment['available_quantity'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Qty</label>
                            <input type="number" class="form-control" name="equipment_quantity" min="1" value="1">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Additional Notes</label>
                            <textarea class="form-control" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-3">
                        You must select at least one resource (venue or equipment).
                    </small>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" <?= $schema_ready ? '' : 'disabled' ?>>Submit Request</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    window.scheduleReservationEvents = <?= json_encode($calendar_events) ?>;
</script>
<script src="api/fullcalendar.js"></script>

<?php
include 'includes/footer.php';
?>