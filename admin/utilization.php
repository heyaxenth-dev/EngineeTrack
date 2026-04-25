<?php
include 'includes/authentication.php';
include '../database/conn.php';

$utilization_rows = [];
$schema_ready = true;

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function table_exists($conn, $table_name) {
    $safe_table = mysqli_real_escape_string($conn, $table_name);
    $result = mysqli_query($conn, "SHOW TABLES LIKE '{$safe_table}'");
    return $result && mysqli_num_rows($result) > 0;
}

$required_tables = ['reservation_requests', 'reservation_request_items', 'users'];
foreach ($required_tables as $table_name) {
    if (!table_exists($conn, $table_name)) {
        $schema_ready = false;
        break;
    }
}

if ($schema_ready) {
    $utilization_query = "
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
            ) AS request_resources
        FROM reservation_requests rr
        LEFT JOIN users u ON u.id = rr.requested_by_user_id
        LEFT JOIN reservation_request_items rri ON rri.reservation_request_id = rr.id
        GROUP BY rr.id
        ORDER BY rr.created_at DESC
    ";

    $utilization_result = mysqli_query($conn, $utilization_query);
    if ($utilization_result) {
        while ($row = mysqli_fetch_assoc($utilization_result)) {
            $utilization_rows[] = $row;
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

    <section class="section">
        <?php if (!$schema_ready): ?>
            <div class="alert alert-warning">
                Reservation tables are not ready yet. Run `database/migrations/2026_04_25_create_staff_reservation_tables.sql`
                and refresh this page.
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Utilization Requests</h5>
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>Requester</th>
                                    <th>Department</th>
                                    <th>Event</th>
                                    <th>Equipment & Facility</th>
                                    <th>Request Schedule</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($utilization_rows)): ?>
                                    <?php foreach ($utilization_rows as $row): ?>
                                        <?php $status = strtolower($row['status']); ?>
                                        <tr>
                                            <td><?= h($row['requester_name']) ?></td>
                                            <td><?= h($row['department']) ?></td>
                                            <td><?= h($row['event_title']) ?></td>
                                            <td><?= h($row['request_resources'] ?: 'N/A') ?></td>
                                            <td>
                                                <?= date('M d, Y h:i A', strtotime($row['start_datetime'])) ?><br>
                                                <small class="text-muted">to <?= date('h:i A', strtotime($row['end_datetime'])) ?></small>
                                            </td>
                                            <td>
                                                <?php
                                                $badge_class = $status === 'approved' ? 'bg-success' : ($status === 'rejected' ? 'bg-danger' : 'bg-warning');
                                                ?>
                                                <span class="badge <?= $badge_class ?>"><?= ucfirst(h($row['status'])) ?></span>
                                            </td>
                                            <td>
                                                <?php if ($status === 'pending'): ?>
                                                    <div class="d-flex gap-2">
                                                        <form method="POST" action="api/update-reservation-status.php">
                                                            <input type="hidden" name="request_id" value="<?= (int) $row['id'] ?>">
                                                            <input type="hidden" name="action" value="approve">
                                                            <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                                        </form>
                                                        <form method="POST" action="api/update-reservation-status.php">
                                                            <input type="hidden" name="request_id" value="<?= (int) $row['id'] ?>">
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
                                        <td colspan="7" class="text-center text-muted">No utilization requests found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
include 'includes/footer.php';
?>